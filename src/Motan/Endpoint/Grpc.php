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
 * Motan Grpc Endpoint for PHP 5.4+
 * 
 * <pre>
 * Motan Grpc Endpoint
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-09-11]
 */
class Grpc extends \Motan\Endpointer
{
    const CLIENT_TYPE = 'grpc';

    private $_service_cls;

    public $request_id;

    public function __construct(URL $url_obj)
    {
        parent::__construct($url_obj);
        $service_str = $this->_url_obj->getService();

        if (!defined('PB_NAME_SPACE')) {
            $this->_service_cls = str_replace('.', '\\', $service_str);
        } else {
            $this->_service_cls = PB_NAME_SPACE . substr($service_str, strripos($service_str, '.') + 1);
        }
    }

    private function _getGrpcClient()
    {
        return new $this->_service_cls($this->_loadbalance->getNode(), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
    }

    public function call()
    {
//      @TODO add GRPC Request Options support
        $args[0] = $this->_url_obj->getParams()['req_msg'];
        $args[1] = $this->_url_obj->getHeaders();
        !isset($args[1]) && $args[1] = [];
        if (isset($args[1])) {
            $metadata = [];
            foreach ($args[1] as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $metadata[$k] = [$v];
            }
            $args[1] = $metadata;
        }
        $grpc_client = $this->_getGrpcClient();
        $func_name = $this->_url_obj->getMethod();
        if (is_callable([$grpc_client, $func_name])) {
            $func_call = call_user_func_array([$grpc_client, $func_name], $args);
            list($rs, $status) = $func_call->wait();
            if ($status->code === 0) {
                $this->_response_header = $status->metadata;
                return $rs;
            } else {
                throw new \Exception($status->details . PHP_EOL . print_r($status, true));
            }
        }
    }
    
    public function multiCall(array $call_arr)
    {
        $rs = [];
        foreach ($call_arr as $func_name => $args) {
            $rs[$func_name] = $this->call($func_name, [$args]);
        }
        return $rs;
    }
}
