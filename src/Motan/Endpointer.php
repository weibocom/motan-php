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
 * Motan Endpointer for PHP 5.4+
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
    protected $_connection_obj;

    protected $_response;
    protected $_response_header;
    protected $_response_metadata;
    protected $_response_exception;

    public $request_id;

    public function __construct(URL $url_obj)
    {
        $this->_url_obj = $url_obj;
        $this->_serializer = Utils::getSerializer($this->_url_obj->getSerialization());
        $this->_connection_obj = new Connection($this->_url_obj);
    }

    public function setLoadBalance($loadbalance)
    {
        if ($loadbalance instanceof Cluster\LoadBalance) {
            $this->_loadbalance = $loadbalance;
        }
        return $this;
    }

    protected function _buildConnection()
    {
        $this->_connection_obj->buildConnection($this->_loadbalance->getNode());
        return $this->_connection = $this->_connection_obj->getConnection();
    }

    public function call(...$arguments)
    {
        $request_obj = $resp_obj = $resp_taged = NULL;
        if (empty($arguments)) {
            $req_params = $this->_url_obj->getParams();
            if (!empty($req_params)) {
                $req_obj_data = $req_params;
            } else {
                $req_obj_data = $this->_url_obj->getRawReqObj();
            }
            $request_obj = $this->_serializer->serialize($req_obj_data);
        } else {
            $request_obj = $this->_serializer->serializeMulti(...$arguments);
        }
        if (Constants::PROTOCOL_GRPC === $this->_url_obj->getProtocol()) {
            $resp_obj = $req_params['resp_msg'];
            $req_obj_data = $req_params['req_msg'];
            $request_obj = $this->_serializer->serialize($req_obj);
            $resp_taged = true;
        }
        $this->_buildConnection();
        if (!$this->_connection) {
            return false;
        }
        $this->_response = $this->_motanCall($request_obj);
        $this->_response_header = $this->_response->getHeader();
        $this->_response_metadata = $this->_response->getMetadata();
        if ($this->_response_header->isGzip()) {
            $resp_body = zlib_decode($this->_response->getBody());
        } else {
            $resp_body = $this->_response->getBody();
        }
        $rs = $this->_serializer->deserialize($resp_obj, $resp_body);
        // if ($resp_taged) {
            null === $rs && $this->_response_exception = $this->_response->getMetadata()['M_e'];
        // }
        return $rs;
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

    protected function _motanCall($request_obj)
    {
        $request_id = $this->_url_obj->getRequestId();
        $metadata = $this->_url_obj->getHeaders();
        defined("APP_NAME") && $metadata['M_s'] = APP_NAME;
        $metadata['M_p'] = $this->_url_obj->getService();
        $metadata['M_m'] = $this->_url_obj->getMethod();
        $metadata['M_g'] = $this->_url_obj->getGroup();
        $metadata['M_pp'] = $this->_url_obj->getProtocol();
        $metadata['requestIdFromClient'] = $request_id;
        $metadata['SERIALIZATION'] = $this->_url_obj->getSerialization();
        $metadata['M_pp'] === 'cedrus' && $metadata['HTTP_Method'] = $this->_url_obj->getHttpMethod();
        $buf = Protocol\Motan::encode($request_id, $request_obj, $metadata);
        $this->_connection_obj->write($buf);
        return $this->_connection_obj->read();
    }
}
