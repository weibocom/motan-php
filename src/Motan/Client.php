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
 * Motan Client for PHP 5.6+
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
    protected $_url_obj;
    protected $_endpoint;

    public function __construct(URL $url_obj = NULL)
    {
        $this->_url_obj = $url_obj != NULL ? $url_obj : new URL();
        $connection = new Connection($this->_url_obj);
        $agent_addr = defined('D_AGENT_ADDR') ? D_AGENT_ADDR : NULL;
        if (defined('MESH_SOCK_FILE')) {
            $agent_addr = "unix://" . MESH_SOCK_FILE;
        }
        $mesh_isalive = FALSE;
        try {
            $mesh_isalive = $connection->buildConnection($agent_addr);
        } catch (\Exception $e) {
            error_log("weibo-mesh isn't alive " . $e->getMessage() );
        }
        if ($mesh_isalive){
            $this->_url_obj->setEndpoint(Constants::ENDPOINT_AGENT);
            $this->_endpoint = new Agent($this->_url_obj);
            $this->_endpoint->setConnectionObj($connection);
        } else {
            $_protocol = $this->_url_obj->getProtocol();
            if ($_protocol === 'memcache') {
                throw new \Exception('did not support to connect memcache directly.');
            }
            if ($_protocol == 'grpc') {
                $this->_url_obj->setEndpoint(Constants::ENDPOINT_GRPC);
            } else {
                $this->_url_obj->setEndpoint(Constants::ENDPOINT_MOTAN);
            }
            $this->_endpoint = new Cluster($this->_url_obj);
        }
    }

    public function setRequestTimeOut($time_out)
    {
        $this->_endpoint->setRequestTimeOut($time_out);
    }

    // this just for Mesh down, connection direct to server.
    public function setConnectionTimeOut($time_out)
    {
        $this->_endpoint->setConnectionTimeOut($time_out);
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
        $request = new \Motan\Request(
            $this->_url_obj->getService(),
            $name, ...$arguments);
        $request->addHeaders($this->_url_obj->getHeaders());
        $request->setGroup($this->_url_obj->getGroup());
        $request->setProtocol($this->_url_obj->getProtocol());
        return $this->_endpoint->call($request)->getRs();
    }

    public function __call($name, $arguments)
    {
        $request_id = $request_args = $request_header = NULL;
        isset($arguments[0]) && !empty($arguments[0]) && $request_args = $arguments[0];
        $request = new \Motan\Request($this->_url_obj->getService(),
        $name, ...[$request_args]);
        $request->addHeaders($this->_url_obj->getHeaders());
        isset($arguments[1]) && !empty($arguments[1]) && $request->addHeaders($arguments[1]);
        isset($arguments[2]) && !empty($arguments[2]) && $request->setRequestId($arguments[2]);
        $request->setGroup($this->_url_obj->getGroup());
        $http_params = $this->_url_obj->getParams();
        !empty($http_params) && $request->addHTTPQueryParams($http_params);
        switch ($name) {
            case 'get':
                $this->_url_obj->setHttpMethod(Constants::HTTP_METHOD_GET);
                $request->setMethod($this->_url_obj->getMethod());
                break;
            case 'post':
                $this->_url_obj->setHttpMethod(Constants::HTTP_METHOD_POST);
                $request->setMethod($this->_url_obj->getMethod());
                break;
            case 'grpc':
                $this->_url_obj->setEndpoint(Constants::ENDPOINT_GRPC);
                $this->_url_obj->setProtocol(Constants::PROTOCOL_GRPC);
                $request->setProtocol(Constants::ENDPOINT_GRPC);
                break;
            default:
                $this->_url_obj->setMethod($name);
                break;
        }
        return $this->_endpoint->call($request)->getRs();
    }

    /**
     * multiCall calls a method on multiple backend.
     * @param URL[] $url_objs array of backend URL object.
     * @param string $method  RPC method name.
     * @param mixed ...$args  arguments will pass to method.
     * @return array arary of called result, index 0 is the first response of $url_objs.
     * @throws \Exception, you should try to catch it.
     */
    public function multiCall(array $url_objs,string $method, ...$args) {
        if (empty($url_objs)) {
            return [];
        }

        $request_objs = [];
        foreach ($url_objs as $url_obj) {
            $request = new \Motan\Request($url_obj->getService(),
            $method, ...$args);
            $request->addHeaders($url_obj->getHeaders());
            $request->setGroup($url_obj->getGroup());
            $request_objs[] = $request;
        }
        return $this->_endpoint->multiCall($request_objs);
    }
}
