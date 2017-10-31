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

abstract class Router {
    
    protected $_defaultIdc    = 'yf';
    protected $_defaultGroup  = 'yf';
    
    abstract public function getIdcByIp($ip);
    abstract public function getAgentGroupByIp($ip);
    abstract public function getAgentGroupByIdc($idc);

    public function setDefaultIdc($idc)
    {
        !$idc && $this->_defaultIdc = $idc;
    }
    
    public function setDefaultGroup($group)
    {
        !$group && $this->_defaultGroup = $group;
    }
    
    public function getDefaultIdc()
    {
        return $this->_defaultIdc;
    }
    
    public function getDefaultGroup()
    {
        return $this->_defaultGroup;
    }
    
}