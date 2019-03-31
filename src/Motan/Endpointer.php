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

namespace Motan;

use Motan\Transport\Connection;

/**
 * Motan Endpointer for PHP 5.6+
 * 
 * <pre>
 * Motan Endpointer
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-08-15]
 */
abstract class  Endpointer
{
    protected $_url_obj;
    protected $_loadbalance;
    protected $_serializer;
    protected $_connection;
    protected $_connection_addr;
    protected $_connection_obj = NULL;

    protected $_response;
    protected $_response_header;
    protected $_response_metadata;
    protected $_response_exception;

    protected $_resp_taged = NULL;

    public $request_id;

    public function __construct(URL $url_obj)
    {
        $this->_url_obj = $url_obj;
        $this->_serializer = Utils::getSerializer($this->_url_obj->getSerialization());
    }

    public function setLoadBalance($loadbalance)
    {
        if ($loadbalance instanceof Cluster\LoadBalance) {
            $this->_loadbalance = $loadbalance;
        }
        return $this;
    }

    public function addHeaders($headers = [])
    {
        return $this->_url_obj->addHeaders($headers);
    }

    public function setRequestTimeOut($time_out)
    {
        // setting for agent request time out.
        if (isset($this->_connection) && is_resource($this->_connection)) {
            @stream_set_timeout($this->_connection, 0, $time_out * 1000000);
        }
        // setting for cluster request time out when mesh down.
        $this->_url_obj->setReadTimeOut($time_out);
    }

    public function setConnectionTimeOut($time_out)
    {
        // setting just for cluster connection time out when mesh down.
        $this->_url_obj->setConnectionTimeOut($time_out);
    }

    protected function _buildConnection()
    {
        if (empty($this->_connection_obj)) {
            $this->_connection_obj = new Connection($this->_url_obj);
        }
        $this->_connection_obj->buildConnection($this->_loadbalance->getNode());
        return $this->_connection = $this->_connection_obj->getConnection();
    }

    public function setConnectionObj(Connection $conn_obj) {
        $this->_connection_obj = $conn_obj;
        $this->_connection = $this->_connection_obj->getConnection();
    }

    public function call(\Motan\Request $request)
    {
        $this->_doSend($request);

        // @TODO checke GRPC using \Motan\Request
        // if (Constants::PROTOCOL_GRPC === $this->_url_obj->getProtocol()) {
        //     $resp_obj = $req_params['resp_msg'];
        //     $req_obj_data = $req_params['req_msg'];
        //     $request_obj = $this->_serializer->serialize($req_obj);
        //     $this->_resp_taged = true;
        // }

        $res = $this->_doRecv();

        // @Deprecated start
        $this->_response = $res->getRawResp();
        $this->_response_header = $res->getResponseHeader();
        $this->_response_metadata = $res->getResponseMetadata();
        $exception = $res->getResponseException();
        if (!empty($exception)) {
            $this->_response_exception = $exception;
        }
        // @Deprecated end

        return $res;
    }

    protected function _doSend(\Motan\Request $request)
    {
        if ($this->_url_obj->getUrlType() == Constants::REQ_URL_TYPE_RESTY
            || strpos($request->getMethod(), '/')) {
            $request = $request->buildHTTPParams();
        }
        $this->_buildConnection();
        if( !$this->_connection) {
            throw new \Exception("Connection has gone away!");
        }
        $req_body = $this->_serializer->serializeMulti(...$request->getRequestArgs());

        // @TODO check GRPC using \Motan\Request
        // if (Constants::PROTOCOL_GRPC === $url_obj->getProtocol()) {
        //     $this->_resp_obj = $req_params['resp_msg'];
        //     $req_obj = $req_params['req_msg'];
        //     $this->_resp_taged = true;
        // }

        $request_id = $request->getRequestId();
        $metadata = $request->getRequestHeaders();
        $app_name = $this->_url_obj->getAppName();
        !empty($app_name) && $metadata['M_s'] = $app_name;
        $metadata['M_p'] = $request->getService();
        $metadata['M_m'] = $request->getMethod();
        $metadata['M_g'] = $request->getGroup();
        $metadata['M_pp'] = $request->getProtocol();
        $metadata['requestIdFromClient'] = $request_id;
        $metadata['SERIALIZATION'] = $this->_url_obj->getSerialization();
        $http_method = $this->_url_obj->getHttpMethod();
        !empty($http_method) && $metadata['HTTP_Method'] = $http_method;
        $buf = Protocol\Motan::encode($request_id, $req_body, $metadata);
        
        $this->_connection_obj->write($buf);
    }

    protected function _doRecv($resp_obj = NULL)
    {
        $resp_msg = $this->_connection_obj->read();
        $resp_body = $resp_msg->getBody();
        if ($resp_msg->getHeader()->isGzip()) {
            $resp_body = zlib_decode($resp_body);
        }
        $res = $exception = NULL;
        $res = $this->_serializer->deserialize($resp_obj, $resp_body);
        // @TODO Check resp_taged for grpc
        $resp_meta = $resp_msg->getMetadata();
        if (isset($resp_meta['M_e'])) {
            $exception = $resp_meta['M_e'];
        }
        return new \Motan\Response($res, $exception, $resp_msg);
    }
    
    public function multiCall(array $request_objs)
    {
        $result = [];
        $ret_order = [];
        $i = 0;
        foreach ($request_objs as $request) {
            $seqId = $request->getRequestId();
            $this->_doSend($request);
            $ret_order[$seqId] = $i;
            $i++;
        }
        foreach ($ret_order as $index) {
            $ret = $this->_doRecv();
            $ret_id = $ret->getResponseHeader()->getRequestId();
            // @Deprecated
            $result[$ret_order[$ret_id]] = $ret->getRs();
        }
        ksort($result);
        return $result;
    }

    public function doMultiCall($request_objs)
    {
        $results = $requests = [];
        foreach ($request_objs as $request) {
            $request_id = $request->getRequestId();
            try {
                $this->_doSend($request);
            } catch (\Exception $e) {
                $results[$request_id] = new \Motan\Response(NULL, $e->getMessage(), NULL);
                continue;
            }
            $results[$request_id] = NULL;
            $requests[$request_id] = $request_id;
        }

        $multi_exceptions = [];
        foreach ($results as $req_id => $prepared_resp) {
            if ($prepared_resp !== NULL) {
                continue;
            }
            try {
                $resp= $this->_doRecv();
            } catch (\Exception $e) {
                array_push($multi_exceptions, $e);
                continue;
            }

            $request_id = $resp->getResponseHeader()->getRequestId();
            $results[$request_id] = $resp;
            unset($requests[$request_id]);
        }

        foreach (array_keys($requests) as $req_id) {
            $results[$req_id] = new \Motan\Response(NULL, array_pop($multi_exceptions)->getMessage(), NULL);
        }
        return new \Motan\MultiResponse($results);
    }

    public function getResponseHeader()
    {
        return $this->_response_header;
    }

    public function getResponseException()
    {
        return $this->_response_exception;
    }

    public function getResponseMetadata()
    {
        return $this->_response_metadata;
    }

    public function getResponse()
    {
        return $this->_response;
    }
}
