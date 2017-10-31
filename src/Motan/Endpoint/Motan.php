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

namespace Motan\Endpoint;

use Motan\URL;

/**
 * Motan Endpoint for PHP 5.4+
 * 
 * <pre>
 * Motan Endpoint
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-12-21]
 */
class Motan extends \Motan\Endpointer
{
    public function __construct(URL $url)
    {
        parent::__construct($url);
    }

    //@TODO multiCall
    //@TODO stream_select
    //@TODO socket_select
    public function multiCall(array $call_arr)
    {
    }
}
