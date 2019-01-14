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
 * Motan TestHelper for PHP 5.4+
 * 
 * <pre>
 * Motan testing helpers
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2019-1-08]
 */
class TestHelper
{
    public static function TestDefines()
    {
        if (isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME'] == 'php') {
            define('CONN_HOST_IP', '10.211.55.2');
            // define('CONN_HOST_IP', '10.211.55.100');
        }else {
            define('CONN_HOST_IP', '127.0.0.1');
            
        }

        if (isset($_SERVER['MESH_UP']) && $_SERVER['MESH_UP'] == 'yes'){
            define('MESH_CALL', TRUE);
            isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME'] == 'php' && define('D_AGENT_ADDR', CONN_HOST_IP . ':9981');
        }else {
            define('D_CONN_DEBUG', CONN_HOST_IP . ':9100');
        }
    }
}
