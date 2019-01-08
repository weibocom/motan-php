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

use DrSlump\Protobuf\Exception;

/**
 * Motan URL for PHP 5.4+
 * 
 * <pre>
 * 请求URL 封装
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-12-12]
 */
class URL {
    private $_raw_url;
    private $_raw_req_obj;
    private $_url_type;
    private $_params;
    private $_headers;
    private $_service;
    private $_method;
    private $_http_method = Constants::HTTP_METHOD_GET;
    private $_endpoint = Constants::ENDPOINT_AGENT;
    private $_version = Constants::DEFAULT_VERSION;
    /** serialize **/
    private $_serialization = Constants::SERIALIZATION_SIMPLE;
    private $_protocol = Constants::PROTOCOL_MOTAN_NEW;

    private $_group = NULL;
    private $_request_id = NULL;

    private $_loadbalance = "random";
    private $_haStrategy = "failfast";
    private $_path = "";
    private $_host = Constants::DEFAULT_AGENT_HOST;
    private $_port = Constants::DEFAULT_AGENT_PORT;

    private $_connection_time_out = Constants::MOTAN_CONNECTION_TIME_OUT;
    private $_read_time_out = Constants::MOTAN_READ_TIME_OUT;
    private $_read_buffer_size = Constants::DEFAULT_SOCKET_BUFFER_SIZE;
    private $_write_buffer_size = Constants::DEFAULT_SOCKET_BUFFER_SIZE;
    private $_retry_times = Constants::DEFAULT_SOCKET_CONNECT_RETRY_TIMES;

    private function _initMetaInfo($key, $url_key)
    {
        if (isset($this->_params[$key])) {
            $this->$url_key = $this->_params[$key];
            unset($this->_params[$key]);
        }
    }
    
    public function __construct($url)
    {
        if (!empty($url)) {
            $this->_raw_url = $url;

            $url_info = parse_url($url);
            $this->_path = ltrim($url_info['path'], '/');
            $this->_host = $url_info['host'];
            $this->_port = $url_info['port'];
            parse_str($url_info['query'], $this->_params);

            $this->_initMetaInfo(Constants::URL_SERVICE_KEY, '_service');
            $this->_initMetaInfo(Constants::URL_GROUP_KEY, '_group');
            $this->_initMetaInfo(Constants::URL_SERIALIZE_KEY, '_serialize');
            $this->_initMetaInfo(Constants::URL_METHOD_KEY, '_method');

            switch ($url_info['scheme']) {
                case 'motan':
                case 'motan2':
                    $this->_url_type = Constants::REQ_URL_TYPE_MOTAN;
                    $this->_service = $this->_path;
                    $this->_endpoint = Constants::ENDPOINT_MOTAN;
                break;
                case 'http':
                case 'cedrus':
                    $this->_url_type = Constants::REQ_URL_TYPE_RESTY;
                    $this->_method = '/' . $this->_path;
                    $this->_endpoint = Constants::ENDPOINT_MOTAN;
                    if (!isset($this->_headers['Content-Type'])) {
                        $this->_headers['Content-Type'] = Constants::DEFAULT_POST_CONTENTTYPE;
                    }
                break;
                case 'grpc':
                    $this->_endpoint = Constants::ENDPOINT_GRPC;
                    $this->_protocol = Constants::PROTOCOL_GRPC;
                    $this->_serialization = Constants::SERIALIZATION_PB;
                    $this->_service = $this->_path;
                    break;
                case 'cgi':
                    $this->_protocol = Constants::PROTOCOL_CGI;
                    $this->_url_type = Constants::REQ_URL_TYPE_MOTAN;
                    $this->_service = $this->_path;
                    $this->_endpoint = Constants::ENDPOINT_MOTAN;
                    break;
                case 'memcache':
                    $this->_protocol = Constants::PROTOCOL_MEMCACHE;
                    $this->_url_type = Constants::REQ_URL_TYPE_MOTAN;
                    $this->_service = $this->_path;
                    $this->_endpoint = Constants::ENDPOINT_MOTAN;
                    break;
                default:
                    throw new Exception("Didn't support the scheme:" . $url_info['scheme']);
                    break;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getRawUrl()
    {
        return $this->_raw_url;
    }

    /**
     * @param mixed $raw_url
     */
    public function setRawUrl($raw_url)
    {
        $this->_raw_url = $raw_url;
    }

    /**
     * @return string
     */
    public function getUrlType()
    {
        return $this->_url_type;
    }

    /**
     * @param string $url_type
     */
    public function setUrlType($url_type)
    {
        $this->_url_type = $url_type;
    }

    /**
     * @return void
     */
    public function getParams()
    {
        if(empty($this->_params))
            return NULL;
        return $this->_params;
    }

    /**
     * @param void $params
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * @return mixed
     */
    public function getHttpMethod()
    {
        return $this->_http_method;
    }

    /**
     * @param mixed $http_method
     */
    public function setHttpMethod($http_method)
    {
        $this->_http_method = $http_method;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->_endpoint = $endpoint;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->_version = $version;
    }

    /**
     * @return mixed
     */
    public function getSerialization()
    {
        return $this->_serialization;
    }

    /**
     * @param mixed $serialization
     */
    public function setSerialization($serialization)
    {
        $this->_serialization = $serialization;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->_group = $group;
    }

    /**
     * @return null
     */
    public function getRequestId()
    {
        return $this->_request_id;
    }

    /**
     * @param null $request_id
     */
    public function setRequestId($request_id)
    {
        $this->_request_id = $request_id;
    }

    /**
     * @return mixed
     */
    public function getCluster()
    {
        return $this->_cluster;
    }

    /**
     * @param mixed $cluster
     */
    public function setCluster($cluster)
    {
        $this->_cluster = $cluster;
    }

    /**
     * @return string
     */
    public function getLoadbalance()
    {
        return $this->_loadbalance;
    }

    /**
     * @param string $loadbalance
     */
    public function setLoadbalance($loadbalance)
    {
        $this->_loadbalance = $loadbalance;
    }

    /**
     * @return string
     */
    public function getHaStrategy()
    {
        return $this->_haStrategy;
    }

    /**
     * @param string $haStrategy
     */
    public function setHaStrategy($haStrategy)
    {
        $this->_haStrategy = $haStrategy;
    }

    /**
     * @return mixed
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }

    /**
     * @param mixed $protocol
     */
    public function setProtocol($protocol)
    {
        $this->_protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->_port = $port;
    }

    /**
     * @return float
     */
    public function getConnectionTimeOut()
    {
        return $this->_connection_time_out;
    }

    /**
     * @param float $connection_time_out
     */
    public function setConnectionTimeOut($connection_time_out)
    {
        $this->_connection_time_out = $connection_time_out;
    }

    /**
     * @return int
     */
    public function getReadTimeOut()
    {
        return $this->_read_time_out;
    }

    /**
     * @param int $read_time_out
     */
    public function setReadTimeOut($read_time_out)
    {
        $this->_read_time_out = $read_time_out;
    }

    /**
     * @return int
     */
    public function getReadBufferSize()
    {
        return $this->_read_buffer_size;
    }

    /**
     * @param int $read_buffer
     */
    public function setReadBufferSize($read_buffer_size)
    {
        $this->_read_buffer_size = $read_buffer_size;
    }

    /**
     * @return int
     */
    public function getWriteBufferSize()
    {
        return $this->_write_buffer_size;
    }

    /**
     * @param int $write_buffer
     */
    public function setWriteBufferSize($write_buffer_size)
    {
        $this->_write_buffer_size = $write_buffer_size;
    }

    /**
     * @return int
     */
    public function getRetryTimes()
    {
        return $this->_retry_times;
    }

    /**
     * @param int $retry_times
     */
    public function setRetryTimes($retry_times)
    {
        $this->_retry_times = $retry_times;
    }

    public function addParams($params)
    {
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $this->_params[$k] = $v;
            }
        } elseif(!empty($params)) {
            $this->_raw_req_obj = $params;
        }
    }

    public function getRawReqObj()
    {
        return $this->_raw_req_obj;
    }

    public function addHeaders($headers)
    {
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $this->_headers[$k] = $v;
            }
        }
    }
}
