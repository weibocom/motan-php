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

use Motan\Utils;

/**
 * Motan  Request for PHP 5.4+
 * 
 * <pre>
 * Motan Request 
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2019-01-06]
 */
class Request{
    private $_service;
    private $_method;
    private $_request_args;
    private $_request_id;
    private $_group;
   
    public function __construct($service, $method, $request_args = NULL, $group = NULL, $request_id = NULL)
    {
        $this->_service = $service;
        $this->_method = $method;
        $request_args != NULL ? $this->_request_args = $request_args :$this->_request_args = [];
        $group != NULL? $this->_group= $group: $this->_group= Utils::getGroup(NULL); 
        $request_id != NULL? $this->_request_id = $request_id : $this->_request_id = Utils::genRequestId(NULL); 
    }

    public function setGroup($group = NULL)
    {
        $group != NULL && $this->_group = $group;
        return $this;
    }

    public function setRequestId($request_id = NULL)
    {
        $request_id != NULL && $this->_request_id = $request_id;
        return $this;
    }

    public function getService()
    {
        return $this->_service;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getRequestArgs()
    {
        return $this->_request_args;
    }

    public function getRequestId()
    {
        return $this->_request_id;
    }

    public function getGroup()
    {
        return $this->_group;
    }
}
