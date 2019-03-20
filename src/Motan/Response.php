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
 * Motan  Response for PHP 5.4+
 * 
 * <pre>
 * Motan Response 
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2019-01-06]
 */
class Response{
    private $_rs;
    private $_exception = NULL;
    private $_raw_response;
   
    public function __construct($rs, $exception, $raw_resp)
    {
        $this->_rs = $rs;
        $this->_exception = $exception;
        $this->_raw_response = $raw_resp;
    }

    public function getException()
    {
        return $this->_exception;
    }

    public function getRs()
    {
        return $this->_rs;
    }
    
    public function getRawResp()
    {
        return $this->_raw_response;
    }

    public function getResponseHeader()
    {
        return $this->_raw_response->getHeader();
    }

    public function getResponseMetadata()
    {
        return $this->_raw_response->getMetadata();
    }

    public function getResponseException()
    {
        return $this->_exception;
    }
}
