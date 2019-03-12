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

/**
 * Motan MClient for PHP 5.4+
 * 
 * <pre>
 * Motan MClient
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2019-01-08]
 */
class MClient extends Client
{
    const URL_FORMAT="%s://127.0.0.1:9981/%s?group=%s";
    
    public function __construct($app_name = 'default-appname', $service = NULL, $group=NULL, $protocol = 'motan2')
    {
        $url_obj = new \Motan\URL(sprintf(self::URL_FORMAT, $protocol, $service, $group));
        $url_obj->setAppName($app_name);
        parent::__construct($url_obj);
    }

    public function doMultiCall($request_arr)
    {
        if (empty($request_arr)) {
            return [];
        }
        return $this->_endpoint->doMultiCall($request_arr);
    }

    public function getMRs(\Motan\Request $request)
    {
        return $this->_endpoint->getMRs($request);
    }

    public function getMException(\Motan\Request $request)
    {
        return $this->_endpoint->getMException($request);
    }
}
