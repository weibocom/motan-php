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
 * Motan Utils for PHP 5.6+
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
    const MAX_VARINT_BYTES = 10;

    public static function genRequestId(URL $url_obj = NULL)
    {
        $time = explode(" ", microtime());
        $request_id = sprintf("%d%06d%03d", $time[1], (int) ($time[0]*1000000), mt_rand(1, 999));
        return $request_id;
    }

    public function getGroup()
    {
        return '';
    }

    public static function is_assoc($var)
    {
        return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
    }
    
    public static function get_bytes($string)
    {  
        $bytes = array();
        for($i = 0; $i < strlen($string); $i++){
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }
    
    public static function toStr($bytes)
    {  
        $str = ''; 
        foreach($bytes as $ch) {
            $str .= chr($ch);  
        }
        return $str;
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

    // Following functions are zigzag varint transform, for 32 bit platfom we need BC Math extension
    public static function encodeZigzag64($int64)
    {
        if (PHP_INT_SIZE == 4) {
            if (bccomp($int64, 0) >= 0) {
                return bcmul($int64, 2);
            } else {
                return bcsub(bcmul(bcsub(0, $int64), 2), 1);
            }
        } else {
            return ($int64 << 1) ^ ($int64 >> 63);
        }
    }

    public static function decodeZigzag64($uint64)
    {
        if (PHP_INT_SIZE == 4) {
            if (bcmod($uint64, 2) == 0) {
                return bcdiv($uint64, 2, 0);
            } else {
                return bcsub(0, bcdiv(bcadd($uint64, 1), 2, 0));
            }
        } else {
            return (($uint64 >> 1) & 0x7FFFFFFFFFFFFFFF) ^ (-($uint64 & 1));
        }
    }

    public static function divideInt64ToInt32($value, &$high, &$low)
    {
        $neg = (bccomp($value, 0) < 0);
        if ($neg) {
            $value = bcsub(0, $value);
        }

        $high = bcdiv($value, 4294967296);
        $low = bcmod($value, 4294967296);
        if (bccomp($high, 2147483647) > 0) {
            $high = (int) bcsub($high, 4294967296);
        } else {
            $high = (int) $high;
        }
        if (bccomp($low, 2147483647) > 0) {
            $low = (int) bcsub($low, 4294967296);
        } else {
            $low = (int) $low;
        }

        if ($neg) {
            $high = ~$high;
            $low = ~$low;
            $low++;
            if (!$low) {
                $high = (int)($high + 1);
            }
        }
    }

    public static function encodeVarint($value)
    {
        $buffer = '';

        $high = 0;
        $low = 0;
        if (PHP_INT_SIZE == 4) {
            static::divideInt64ToInt32($value, $high, $low);
        } else {
            $low = $value;
        }

        while (($low >= 0x80 || $low < 0) || $high != 0) {
            $buffer .= chr($low | 0x80);
            $carry = ($high & 0x7F) << ((PHP_INT_SIZE << 3) - 7);
            $high = ($high >> 7) & ~(0x7F << ((PHP_INT_SIZE << 3) - 7));
            $low = (($low >> 7) & ~(0x7F << ((PHP_INT_SIZE << 3) - 7)) | $carry);
        }
        $buffer .= chr($low);
        return $buffer;
    }

    public static function combineInt32ToInt64($high, $low)
    {
        $neg = $high < 0;
        if ($neg) {
            $high = ~$high;
            $low = ~$low;
            $low++;
            if (!$low) {
                $high = (int) ($high + 1);
            }
        }
        $result = bcadd(bcmul($high, 4294967296), $low);
        if ($low < 0) {
            $result = bcadd($result, 4294967296);
        }
        if ($neg) {
          $result = bcsub(0, $result);
        }
        return $result;
    }

    public static function decodeVarint($buffer)
    {
        $count = 0;

        if (PHP_INT_SIZE == 4) {
            $high = 0;
            $low = 0;
            $b = 0;

            do {
                if ($count === self::MAX_VARINT_BYTES) {
                    throw new Exception("Varint overflow");
                }
                $b = ord($buffer[$count]);
                $bits = 7 * $count;
                if ($bits >= 32) {
                    $high |= (($b & 0x7F) << ($bits - 32));
                } else if ($bits > 25){
                    // $bits is 28 in this case.
                    $low |= (($b & 0x7F) << 28);
                    $high = ($b & 0x7F) >> 4;
                } else {
                    $low |= (($b & 0x7F) << $bits);
                }
                $count += 1;
            } while ($b & 0x80);

            $result = static::combineInt32ToInt64($high, $low);
            if (bccomp($result, 0) < 0) {
                $var = bcadd($result, "18446744073709551616");
            }
            return [$result, $count];
        } else {
            $result = 0;
            $shift = 0;

            do {
                if ($count === self::MAX_VARINT_BYTES) {
                    throw new Exception("Varint overflow");
                }
                $byte = ord($buffer[$count]);
                $result |= ($byte & 0x7f) << $shift;
                $shift += 7;
                $count += 1;
            } while ($byte > 0x7f);
            return [$result, $count];
        }
    }

    public static function encodeZigzagVarint($number)
    {
        return static::encodeVarint(static::encodeZigzag64($number));
    }

    public static function decodeZigzagVarint($buffer)
    {
        $r = static::decodeVarint($buffer);
        return [static::decodeZigzag64($r[0]), $r[1]];
    }
    // zigzag varint end
}
