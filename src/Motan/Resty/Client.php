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

namespace Motan\Resty;

use \Motan\Interfaces\RestyConfs as RestyConfsInterface;
use \Motan\Constants as Consts;

/**
 * Motan Resty Client for PHP 5.4+
 * 
 * <pre>
 * Resty Client
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-11-12]
 */
class Client
{
    private $_motan_client;
    private $_service_str;
    private $_group;
    private $_serialization = 1;
    private $_protocol = TRUE;
    private $_client_handler;
    private $_req_params = [];
    private $_req_headers = [];

    private $_resty_conf = NULL;

    public function addHeader($key, $value)
    {
        $this->_req_headers[$key] = $value;
    }

    public function addCookie($key, $value)
    {
        $this->_req_headers[Consts::$RESTY_COOKIE_PREFIX . $key] = $value;
    }

    public function addP($key, $value)
    {
        $this->_req_params[$key] = $value;
    }

    public function __construct($url,  RestyConfsInterface $resty_conf = NULL)
    {
        if (!empty($resty_conf)) {
            $this->_resty_conf = $resty_conf;
        } else {
            $this->_resty_conf = new Confs($url);
        }
        $this->_service_str = $this->_resty_conf->getService();
        $this->_group = $this->_resty_conf->getGroup();
        $this->_serialization = $this->_resty_conf->getSerialization();
        $this->_protocol = $this->_resty_conf->getProtocol();
        $this->_motan_client = new \Motan\Client($this->_service_str, $this->_group, $this->_protocol, $this->_serialization);

        $this->_req_params = $this->_resty_conf->getReqParams();
        $this->addHeader('M_m', $this->_resty_conf->getPath());
    }

    public function restyCall()
    {
        return $this->_motan_client->restyCall($this->_req_params, $this->_req_headers);
    }

    public function getMotanClient()
    {
        return $this->_motan_client;
    }
}
