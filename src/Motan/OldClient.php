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
 * OldClient for PHP 5.4+
 * 
 * <pre>
 * Motan OldClient
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-12-12]
 */
class OldClient
{
    private $_client_handler;
    private $_url_obj;
    private $_use_motan;

    public function __construct($service_str, $group, $use_motan = false, $serialization = Constants::SERIALIZATION_SIMPLE)
    {
        $this->_use_motan = $use_motan;
        $agent_addr = DEFAULT_AGENT_HOST . ':' . DEFAULT_AGENT_PORT;
        defined('D_AGENT_ADDR') && $agent_addr = D_AGENT_ADDR;
        $this->_url_obj = new URL('motan2://' . $agent_addr . '/' . $service_str . '?group=' . $group);
        $this->_url_obj->setSerialization($serialization);

        if ($this->_use_motan === true) {
            $this->_url_obj->setEndpoint(Constants::ENDPOINT_MOTAN);
            $this->_url_obj->setProtocol(Constants::PROTOCOL_MOTAN_NEW);
        } else {
            $this->_url_obj->setEndpoint(Constants::ENDPOINT_GRPC);
            $this->_url_obj->setProtocol(Constants::PROTOCOL_GRPC);
        }
        $this->_client_handler = new Client($this->_url_obj);
    }

    public function getResponseHeader()
    {
        return $this->_client_handler->getResponseHeader();
    }

    public function getResponseException()
    {
        return $this->_client_handler->getResponseException();
    }

    public function getResponse()
    {
        return $this->_client_handler->getResponse();
    }

    public function getClientHandler()
    {
        return $this->_client_handler;
    }
    
    public function __call($func_name, $args)
    {
        $rs = null;
        $this->_url_obj->setMethod($func_name);
        if (!$this->_use_motan) {
            $func_name = 'grpc';
            $this->_url_obj->addParams(['req_msg' => $args[0],'resp_msg'=>$args[1]]);
            $this->_url_obj->addHeaders($args[2]);
        }
        $get_rs = $this->_client_handler->__call($func_name, $args);
        if (defined('USE_WEIBOJSON_CODECER') && USE_WEIBOJSON_CODECER === true) {
            $rs = $get_rs->getMotanJsonResult();
        } else {
            $rs = $get_rs;
        }
        return $rs;
    }
}
