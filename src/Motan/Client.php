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

use Motan\Endpoint\Agent;
use Motan\Transport\Connection;

/**
 * Motan Client for PHP 5.4+
 * 
 * <pre>
 * Motan Client
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-08-02]
 */
class Client
{
    private $_url_obj;
    private $_endpoint;

    public function __construct(URL $url_obj)
    {
        $this->_url_obj = $url_obj;
        $connection = new Connection($this->_url_obj);
        $agent_addr = NULL;
        if (defined('D_AGENT_ADDR')) {
            $agent_addr = D_AGENT_ADDR;
        }
        if ($connection->buildConnection($agent_addr)){
            $this->_url_obj->setEndpoint(Constants::ENDPOINT_AGENT);
            $this->_endpoint = new Agent($this->_url_obj);
        } else {
            $this->_endpoint = new Cluster($this->_url_obj);
        }
    }

    public function getEndPoint() {
        return $this->_endpoint;
    }

    public function getResponseHeader()
    {
        return $this->_endpoint->getResponseHeader();
    }

    public function getResponseMetadata()
    {
        return $this->_endpoint->getResponseMetadata();
    }

    public function getResponseException()
    {
        return $this->_endpoint->getResponseException();
    }

    public function getResponse()
    {
        return $this->_endpoint->getResponse();
    }

    public function doCall($name, ...$arguments)
    {
        if ($this->_url_obj->getRequestId() == NULL) {
            $this->_url_obj->setRequestId(Utils::genRequestId($this->_url_obj));
        }
        
        $this->_url_obj->setMethod($name);
        return $this->_endpoint->call(...$arguments);
    }

    public function __call($name, $arguments)
    {
        $request_id =  (!isset($arguments[2]) || empty($arguments[2])) ? Utils::genRequestId($this->_url_obj) : $arguments[2];
        isset($arguments[0]) && $this->_url_obj->addParams($arguments[0]);
        isset($arguments[1]) && $this->_url_obj->addHeaders($arguments[1]);
        $this->_url_obj->setRequestId($request_id);
        switch ($name) {
            case 'get':
                $this->_url_obj->setProtocol(Constants::PROTOCOL_CEDRUS);
                $this->_url_obj->setHttpMethod(Constants::HTTP_METHOD_GET);
                break;
            case 'post':
                $this->_url_obj->setProtocol(Constants::PROTOCOL_CEDRUS);
                $this->_url_obj->setHeaders(['Content-Type'=>'application/x-www-form-urlencoded']);
                $this->_url_obj->setHttpMethod(Constants::HTTP_METHOD_POST);
                break;
            case 'grpc':
                $this->_url_obj->setEndpoint(Constants::ENDPOINT_GRPC);
                $this->_url_obj->setProtocol(Constants::PROTOCOL_GRPC);
                break;
            default:
                $this->_url_obj->setMethod($name);
                break;
        }
        return $this->_endpoint->call();
    } 

    public function multiCall(array $url_objs) {
        if (empty($url_objs)) {
            return [];
        }
        foreach ($url_objs as $url_obj) {
            // usleep(1);  
            $url_obj->setRequestId(Utils::genRequestId($url_obj));
        }
        
        return $this->_endpoint->multiCall($url_objs);
    }
    
}
