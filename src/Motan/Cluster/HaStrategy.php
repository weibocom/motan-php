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

namespace Motan\Cluster;

use Motan\Utils;
use Motan\URL;

/**
 * HaStrategy for PHP 5.4+
 * 
 * <pre>
 * HaStrategy
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-11-12]
 */
abstract class  HaStrategy
{
    protected $_endpoint;
    protected $_url_obj;
    protected $_group;
    protected $_service_str;

    public function __construct(URL $url_obj)
    {
        $this->_url_obj = $url_obj;
        $this->_group = $url_obj->getGroup();
        $this->_service_str = $url_obj->getService();
    }

    public function getEndpoint()
    {
        if (!isset($this->_endpoint)) {
            $this->_endpoint = Utils::getEndPoint($this->_url_obj);
        }
        return $this->_endpoint;
    }

    abstract public function call(LoadBalance $load_balance);
}
