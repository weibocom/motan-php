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

define("BIGINT_DIVIDER", 0xffffffff + 1);

/**
 * Motan Utils for PHP 5.4+
 * 
 * <pre>
 * Motan 工具包
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-10-08]
 */
class Utils
{
    public static function genRequestId(URL $url_obj)
    {
        $time = explode(" ", microtime());
        $request_id = sprintf("%d%06d%03d", $time[1], (int) ($time[0]*1000000), 999999);
        return $request_id;
    }

    public static function crc32Hash($arr, $key)
    {
        return $arr[intval(sprintf("%u", crc32($key)) % count($arr))];
    }

    public static function isAgentAlive()
    {
        if ($_SERVER['AGENT_ALIVE'] === 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function split2Int(&$upper, &$lower, $value)
    {
        $lower = intval($value % BIGINT_DIVIDER);
        $upper = intval(($value - $lower) / BIGINT_DIVIDER);
    }

    public static function bigInt2float($upper, $lower)
    {
        return $upper * BIGINT_DIVIDER + $lower;
    }
    
    public static function getRouter() 
    {
        return new \Motan\Route\Range();
    }

    public static function getSerializer($type)
    {
        $serializer = null;
        switch ($type) {
            case Constants::SERIALIZATION_SIMPLE:
                $serializer = new \Motan\Serialize\Motan();
                break;
            case Constants::SERIALIZATION_PB:
                $serializer = new \Motan\Serialize\PB();
                break;
            case Constants::SERIALIZATION_GRPC_JSON:
                $serializer = new \Motan\Serialize\GrpcJson();
                break;
        }
        return $serializer;
    }

    public static function getHa($ha_strategy, URL $url_obj)
    {
        $ha = null;
        switch ($ha_strategy) {
            case Constants::MOTAN_HA_FAILFAST:
                $ha = new \Motan\Cluster\Ha\Failfast($url_obj);
                break;
            case Constants::MOTAN_HA_FAILOVER:
                $ha = new \Motan\Cluster\Ha\Failover($url_obj);
                break;
        }
        return $ha;
    }

    public static function getLB($lb_strategy, URL $url_obj)
    {
        $lb = null;
        switch ($lb_strategy) {
            case Constants::MOTAN_LB_RANDOM:
                $lb = new \Motan\Cluster\LoadBalance\Random($url_obj);
                break;
            case Constants::MOTAN_LB_ROUNDROBIN:
                $lb = new \Motan\Cluster\LoadBalance\RoundRobin($url_obj);
                break;
        }
        return $lb;
    }

    public static function getEndPoint(URL $url_obj)
    {
        $endpoint = null;
        switch ($url_obj->getEndpoint()) {
            case Constants::ENDPOINT_AGENT:
                $endpoint = new \Motan\Endpoint\Agent($url_obj);
                break;
            case Constants::ENDPOINT_GRPC:
                $endpoint = new \Motan\Endpoint\Grpc($url_obj);
                break;
            case Constants::ENDPOINT_MOTAN:
                $endpoint = new \Motan\Endpoint\Motan($url_obj);
                break;
        }
        return $endpoint;
    }

    static $agent_conf = NULL;
    public static function GetAgentConf()
    {
        if (!function_exists('yaml_parse_file')) {
            throw new \Exception('you should install yaml extension!');
        }
        
        if (static::$agent_conf !== NULL) {
            return static::$agent_conf;
        }
        $conf_file = defined("MOTAN_AGENT_CONF_FILE") ? defined("MOTAN_AGENT_CONF_FILE") : AGENT_RUN_PATH . '/motan.yaml';
        static::$agent_conf = yaml_parse_file($conf_file);
        return static::$agent_conf;
    }

    static $service_conf = NULL;
    public static function GetServiceConf()
    {
        if (static::$service_conf !== NULL) {
            return static::$service_conf;
        }
        $conf = self::GetAgentConf();
        $basic_refs = $conf['motan-basicRefer'];
        $motan_refs = $conf['motan-refer'];
        foreach ($motan_refs as $key => $ref_info) {
            $tmp = [];
            if (isset($ref_info['basicRefer'])) {
                $tmp = isset($basic_refs[$ref_info['basicRefer']]) ? $basic_refs[$ref_info['basicRefer']] : [];
                foreach ($ref_info as $ck => $value) {
                    $tmp[$ck] = $value;
                }
            } else {
                $tmp = $ref_info;
            }
            static::$service_conf[$key] = $tmp;
        }
        return static::$service_conf;
    }
}
