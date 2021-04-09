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

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->_group = $group;
    }

    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * @param mixed $service_str
     */
    public function setService($service_str)
    {
        $this->_service_str = $service_str;
    }

    private function _getSnapshotFile()
    {
        $snap_dir = AGENT_RUN_PATH . "/snapshot/";
        $origin_snapshot_file = $snap_dir . $this->_group . '_' . $this->_service_str;
        // echo $origin_snapshot_file;
        if (is_file($origin_snapshot_file)) {
            return $origin_snapshot_file;
        }
        $sdir = scandir($snap_dir, SCANDIR_SORT_NONE);
        $svc_len = strlen($this->_service_str);
        foreach ($sdir as $index => $snapshot_file_name) {
            $svc_pos = stripos($snapshot_file_name, $this->_service_str);
            $snapshot_file = $snap_dir . DIRECTORY_SEPARATOR . $snapshot_file_name;
            if (is_file($snapshot_file)
            && $svc_pos > 0 && substr($snapshot_file_name, $svc_pos + $svc_len) == "") {
                $this->_group = \substr($snapshot_file_name, 0, $svc_pos-1);
                return $snapshot_file;
            }
        }
    }

    public function getNode()
    {
        if (defined('D_CONN_DEBUG')) {
            return D_CONN_DEBUG;
        }
        if (!defined('AGENT_RUN_PATH')) {
            throw new \Exception('need a AGENT_RUN_PATH defined for reading direct connection nodes');
        }
        $filepath = $this->_getSnapshotFile();
        $snap_str = @file_get_contents($filepath);
        if (!$snap_str) {
            throw new \Exception('open snapshot file err : ' . $filepath);
        }
        $nodes = array();
        $get_nodes = json_decode($snap_str, true)['nodes'];
        if (!$get_nodes) {
            throw new \Exception('fetch backup nodes err : ' . json_last_error());
        }
        if (key_exists('working', $get_nodes)) {
            $working_nodes = $get_nodes['working'];
            foreach ($working_nodes as $info) {
                $nodes[] = $info['host'];
            }
        } else {
            foreach ($get_nodes as $info) {
                $nodes[] = $info['address'];
            }
        }
        return static::select($nodes, $this->_url_obj->getRequestId());
    }
}
