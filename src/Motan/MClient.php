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
 * Motan MClient for PHP 5.6+
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
    public function __construct($app_name = NULL)
    {
        $url_obj = NULL;
        if (!empty($app_name)) {
            $url_obj = new \Motan\URL();
            $url_obj->setAppName($app_name);
        }
        parent::__construct($url_obj);
    }

    public function doMultiCall($request_arr)
    {
        if (empty($request_arr)) {
            return [];
        }
        return $this->_endpoint->doMultiCall($request_arr);
    }

    public function __call($name, $args)
    {
        throw new \Exception("MClient didn't support Magic Calling, using doCall(\Motan\Request) insteded.", 1);
    }

    public function doCall($request, ...$arguments)
    {
        if ($request instanceof \Motan\Request) {
            return $this->_endpoint->call($request)->getRs(); 
        } else {
            throw new \Exception("MClient doCall must using \Motan\Request as a param", 1);
        }
    }
}
