<?php
/**
 * Copyright (c) 2009-2017. Weibo, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *             http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Motan\Endpoint;

use Motan\URL;
use Motan\Constants;
use Motan\Protocol\Motan;
use Motan\Transport\Connection;
use Motan\Utils;
const AGENT_ADDR = "tcp://127.0.0.1:9981";

/**
 * Motan Agent Endpoint for PHP 5.4+
 * 
 * <pre>
 * Motan Agent Endpoint
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-12-12]
 */
class Agent extends \Motan\Endpointer
{
    protected $_resp_taged = NULL;
    protected $_resp_obj = NULL;
    
    public function __construct(URL $url_obj)
    {
        parent::__construct($url_obj);
    }

    protected function _buildConnection()
    {
        if (isset($this->_connection) && is_resource($this->_connection)) {
            return true;
        }
        if ($this->_connection_obj) {
            $this->_connection = $this->_connection_obj->getConnection();
            return true;
        }
        $this->_connection_obj = new Connection($this->_url_obj);
        $this->_connection_obj->buildConnection();
        return $this->_connection = $this->_connection_obj->getConnection();
    }
    
    public function call(...$arguments) {
        $reqArg = $this->_buildReqArg($this->_url_obj, ...$arguments);
        $this->_send($reqArg);
        
        return $this->_recv();
    }

    private function _buildReqArg(URL $url_obj, ...$arguments) 
    {
        $this->_resp_obj = $resp_taged = null;
        if (empty($arguments)) {
            $req_params = $url_obj->getParams();
            if (!empty($req_params)) {
                $req_obj = $req_params;
            } else {
                $req_obj = $url_obj->getRawReqObj();
            }
            if (Constants::PROTOCOL_GRPC === $url_obj->getProtocol()) {
                $this->_resp_obj = $req_params['resp_msg'];
                $req_obj = $req_params['req_msg'];
                $this->_resp_taged = true;
            }
            return $this->_serializer->serialize($req_obj);
        } else {
            // count($arguments) == 1 && $arguments = [$arguments];
            return $this->_serializer->serializeMulti(...$arguments);
        }
    }
    
    protected function _send($req_body)
    {
        $this->_buildConnection();
        if (!$this->_connection) {
            throw new \Exception('agent connection has gone away!');
        }
        $request_id = $this->_url_obj->getRequestId();
        $metadata = $this->_url_obj->getHeaders();
        $metadata['M_s'] = $this->_url_obj->getAppName();
        $metadata['M_p'] = $this->_url_obj->getService();
        $metadata['M_m'] = $this->_url_obj->getMethod();
        $metadata['M_g'] = $this->_url_obj->getGroup();
        $metadata['M_pp'] = $this->_url_obj->getProtocol();
        $metadata['requestIdFromClient'] = $request_id;
        $metadata['SERIALIZATION'] = $this->_url_obj->getSerialization();
        $metadata['M_pp'] === Constants::PROTOCOL_CEDRUS && $metadata['HTTP_Method'] = $this->_url_obj->getHttpMethod();
        $buf = Motan::encode($request_id, $req_body, $metadata);
        
        $this->_connection_obj->write($buf);
    }

    protected function _do_recv()
    {
        $this->_response = $this->_connection_obj->read();
        $this->_response_header = $this->_response->getHeader();
        $this->_response_metadata = $this->_response->getMetadata();
        if ($this->_response_header->isGzip()) {
            $resp_body = zlib_decode($this->_response->getBody());
        } else {
            $resp_body = $this->_response->getBody();
        }
        return $resp_body;
    }
    
    protected function _recv() 
    {
        $resp_body = $this->_do_recv();
        $rs = $this->_serializer->deserialize($this->_resp_obj, $resp_body);
        if (NULL === $rs || $this->_resp_taged) {
            $metadata = $this->_response->getMetadata(); 
            isset($metadata['M_e']) && $this->_response_exception = $metadata['M_e'];
        }
        return $rs;
    }

    protected function _do4Multi()
    {
        $rs = $exception = NULL;
        $resp_body = $this->_do_recv();
        $rs = $this->_serializer->deserialize($this->_resp_obj, $resp_body);
    
        if (NULL === $rs || $this->_resp_taged) {
            NULL === $rs && $get_exception= $this->_response->getMetadata()['M_e'];
            !empty($get_exception) && $exception = $get_exception;
        }
        return new \Motan\Response(
            $rs,
            $exception,
            $this->_response
        );
    }
    
    public function multiCall(array $url_objs)
    {
        $result = [];
        $ret_order = [];
        $i = 0;
        foreach ($url_objs as $url_obj) {
            $this->_url_obj = $url_obj;
            $seqId = $this->_url_obj->getRequestId();
            $this->_send($this->_buildReqArg($url_obj));
            $ret_order[$seqId] = $i;
            $i++;
        }
        foreach ($ret_order as $index) {
            $ret = $this->_recv();
            $ret_id = $this->_response->getHeader()->getRequestId();
            $result[$ret_order[$ret_id]] = $ret;
        }
        ksort($result);
        
        return $result;
    }

    public function doMultiCall($request_arr)
    {
        $result = $requests = [];
        foreach ($request_arr as $request) {
            $method = $request->getMethod();
            $args = $request->getRequestArgs(); 
            count($args) == 1 && $args = [$args];
            $request_id = $request->getRequestId();
            $url_obj = $this->_url_obj;
            $url_obj->setRequestId($request_id);
            $url_obj->setMethod($method);
            $url_obj->setService($request->getService());
            $this->_url_obj = $url_obj;
            $this->_send($this->_serializer->serializeMulti(...$args));
            $requests[] = $request_id;
        }

        foreach ($requests as $index) {
            $resp= $this->_do4Multi();
            $request_id = $resp->getRawResp()->getRequestId();
            $this->_multi_resp[$request_id] = $resp;
            $result[$request_id] = $resp->getRs();
        }
        
        return $result;
    }

}
