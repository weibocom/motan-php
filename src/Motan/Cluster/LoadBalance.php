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

use Motan\URL;

/**
 * LoadBalance for PHP 5.6+
 * 
 * <pre>
 * LoadBalance
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-11-12]
 */
abstract class  LoadBalance
{
    protected $_url_obj;
    protected $_group;
    protected $_service_str;

    public function __construct(URL $url_obj)
    {
        $this->_url_obj = $url_obj;
        $this->_group = $url_obj->getGroup();
        $this->_service_str = $url_obj->getService();
    }

    abstract public function onRefresh();
    abstract public function select($nodes, $requestid);
    abstract public function selectToHolder();

    public function getNode()
    {
        if (defined('D_CONN_DEBUG')) {
            return D_CONN_DEBUG;
        }
        if (!defined('AGENT_RUN_PATH')) {
            throw new \Exception('need a AGENT_RUN_PATH defined for reading direct connection nodes');
        }
        $filepath = AGENT_RUN_PATH . "/snapshot/" . $this->_group . '_' . $this->_service_str;
        $snap_str = @file_get_contents($filepath);
        if (!$snap_str) {
            throw new \Exception('open snapshot file err : ' . $filepath);
        }
        $nodes = array();
        $get_nodes = json_decode($snap_str, true)['nodes'];
        if (!$get_nodes) {
            throw new \Exception('fetch backup nodes err : ' . json_last_error());
        }
        foreach ($get_nodes as $info) {
            $nodes[] = $info['address'];
        }
        return static::select($nodes, $this->_url_obj->getRequestId());
    }
}
