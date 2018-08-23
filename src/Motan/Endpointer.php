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
    }

    public function setLoadBalance($loadbalance)
    {
        if ($loadbalance instanceof Cluster\LoadBalance) {
            $this->_loadbalance = $loadbalance;
        }
        return $this;
    }
    
    public function setConnection(Connection $conn_obj) {
        $this->_connection_obj = $conn_obj;
    }

    protected function _buildConnection()
    {
        $this->_connection_obj->buildConnection($this->_loadbalance->getNode());
        return $this->_connection = $this->_connection_obj->getConnection();
    }
    
    abstract function call();
    abstract function multiCall(array $call_arr);

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
