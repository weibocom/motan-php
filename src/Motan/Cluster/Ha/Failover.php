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

namespace Motan\Cluster\Ha;

/**
 * HA Failover for PHP 5.4+
 * 
 * <pre>
 * 故障转移策略
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-11-12]
 */
class Failover extends \Motan\Cluster\HaStrategy
{
    public function call(\Motan\Cluster\LoadBalance $load_balance, $func_name, $args)
    {
        $request_id = $args[3];
        $grpc_client = $load_balance->getGrpcClient($request_id);
        unset($args[3]);
        if (is_callable([$grpc_client, $func_name])) {
            $func_call = call_user_func_array([$grpc_client, $func_name], $args);
            list($rs, $status) = $func_call->wait();
            if ($status->code === 0) {
                return $rs;
            } else {
                throw new \Exception($status->details . PHP_EOL . print_r($status->metadata, true));
            }
        }
    }
}
