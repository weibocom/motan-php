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
 * Motan  Response for PHP 5.6+
 * 
 * <pre>
 * Motan Response 
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2019-01-06]
 */
class MultiResponse {
    private $_rs_map;
   
    public function __construct($rs_map)
    {
        $this->_rs_map = $rs_map;
    }

    public function getException(\Motan\Request $request)
    {
        return $this->_rs_map[$request->getRequestId()]->getException();
    }

    public function getRs(\Motan\Request $request)
    {
        return $this->_rs_map[$request->getRequestId()]->getRs();
    }
    
    public function getRawResp(\Motan\Request $request)
    {
        return $this->_rs_map[$request->getRequestId()]->getRawResp();
    }

    public function getResponseHeader(\Motan\Request $request)
    {
        return $this->_rs_map[$request->getRequestId()]->getResponseHeader();
    }

    public function getResponseMetadata(\Motan\Request $request)
    {
        return $this->_rs_map[$request->getRequestId()]->getResponseMetadata();
    }
}
