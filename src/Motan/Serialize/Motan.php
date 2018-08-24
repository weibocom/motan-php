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

namespace Motan\Serialize;

use Motan\Constants;
use Motan\Utils;

/**
 * Motan Simple Serializer for PHP 5.4+
 * 
 * <pre>
 * Motan 简单序列化
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2017-1-15]
 */
class Motan implements \Motan\Serializer
{
    static function motan_table_type($params)
    {
        $data_type = NULL;
        if (!Utils::is_assoc($params)) {
            $type_tmp_arr = [];
            foreach ($params as $p) {
                is_string($p) && $type_tmp_arr['string'][] = $p;
                is_int($p) && $p <= 0xff && $type_tmp_arr['byte'][] = $p;
            }
            if (count($type_tmp_arr) == 1) {
                $data_type = isset($type_tmp_arr['string']) ? Constants::DTYPE_STRING_ARRAY : Constants::DTYPE_BYTE_ARRAY;
            } else {
                $data_type = Constants::DTYPE_ARRAY;
            }
        } else {
            $type_tmp_arr = [];
            $type_tmp = NULL;
            foreach ($params as $key => $value) {
                $type_tmp = gettype($value);
                $type_tmp_arr[$type_tmp][] = $value;
            }
            if (count($type_tmp_arr) == 1 && count($type_tmp_arr[$type_tmp]) == count($params) && $type_tmp == 'string') {
                $data_type = Constants::DTYPE_STRING_MAP;
            } else {
                $data_type = Constants::DTYPE_MAP;
            }
        }
        return $data_type;
    }

    static function motan_number_type($n)
    {
        $data_type = NULL;
        if(floor($n) != $n) {
            $data_type = Constants::DTYPE_FLOAT64;
        }
        if($n >= 0) {
            // if(n <= 0xff) {
            //     $data_type = Constants::DTYPE_BYTE;
            // } elseif(n <= 0xffff) {
            //     $data_type = Constants::DTYPE_INT16;
            // } elseif(n <= 4294967295.0) {
            //     $data_type = Constants::DTYPE_INT32;
            // } else {
            //     $data_type = Constants::DTYPE_INT64;
            // }
            $data_type = Constants::DTYPE_INT64;
        } else {
            //@TODO n < 0
            // if(n >= -0x8000) {
            //     $data_type = Constants::DTYPE_INT16;
            // } elseif(n >= -0x80000000) {
            //     $data_type = Constants::DTYPE_INT32;
            // } else {
            //     $data_type = Constants::DTYPE_INT64;
            // }
            $data_type = Constants::DTYPE_INT64;
        }
        return $data_type;
    }

    static function serialize_buf($params, &$buffer)
    {
        if (is_string($params)) {
            $buffer .= pack('C', Constants::DTYPE_STRING) . pack('N', strlen($params)) . $params;
        } elseif (is_bool($params)) {
            $buffer .= pack('C', Constants::DTYPE_BOOL) . pack('C', intval($params));
        } elseif (is_numeric($params)) {
            $number_type = self::motan_number_type($params);
            switch ($number_type) {
                case Constants::DTYPE_BYTE:
                // break;
                case Constants::DTYPE_INT16:
                // break;
                case Constants::DTYPE_INT32:
                // break;
                case Constants::DTYPE_INT64:
                    $buffer .= pack('C', Constants::DTYPE_INT64) . pack('J', $params);
                    print_r(Utils::get_bytes($buffer));
                    // Utils::split2Int($upper, $lower, $params);
                    // $buffer .= pack('C', Constants::DTYPE_INT64) . pack('NN', $upper, $lower);
                break;
                case Constants::DTYPE_FLOAT32: // @TODO encode_float32
                // break;
                case Constants::DTYPE_FLOAT64:
                    $buffer .= pack('C', Constants::DTYPE_FLOAT64) . pack('E', $params);
                break;
            }
        } elseif (is_array($params)) {
            $array_type = self::motan_table_type($params);
            switch ($array_type) {
                case Constants::DTYPE_STRING_ARRAY:
                    $buffer .= pack('C', Constants::DTYPE_STRING_ARRAY);
                    $btemp = '';
                    $btemp_len = 0;
                    foreach ($params as $param) {
                        $btemp .= pack('N', strlen($param)) . $param;
                        $btemp_len += strlen($param) + 4;
                    }
                    $buffer .= pack('N', $btemp_len) . $btemp;
                break;
                case Constants::DTYPE_BYTE_ARRAY :
                    $buffer .= pack('C', Constants::DTYPE_BYTE_ARRAY);
                    $btemp = Utils::toStr($params);
                    $btemp_len = strlen($btemp);
                    $buffer .= pack('N', $btemp_len) . $btemp;
                break;
                case Constants::DTYPE_ARRAY :
                    $buffer .= pack('C', Constants::DTYPE_ARRAY);
                    $btemp = '';
                    $btemp_len = 0;
                    foreach ($params as $param) {
                        $param_bt = '';
                        self::serialize_buf($param, $param_bt);
                        $btemp .= $param_bt;
                        $btemp_len += strlen($param_bt);
                    }
                    $buffer .= pack('N', $btemp_len) . $btemp;
                break;
                case Constants::DTYPE_STRING_MAP :
                    $buffer .= pack('C', Constants::DTYPE_STRING_MAP);
                    $btemp = '';
                    $btemp_len = 0;
                    foreach ($params as $k => $v) {
                        if (is_array($v)) {
                            continue;
                        }
                        $btemp .= pack('N', strlen($k)) . $k . pack('N', strlen($v)) . $v;
                        $btemp_len += strlen($k) + strlen($v) + 8;
                    }
                    $buffer .= pack('N', $btemp_len) . $btemp;
                break;
                case Constants::DTYPE_MAP :
                    $buffer .= pack('C', Constants::DTYPE_MAP);
                    $btemp = '';
                    $btemp_len = 0;
                    foreach ($params as $k => $v) {
                        $bft_k = $bft_v = '';
                        self::serialize_buf($k, $bft_k);
                        self::serialize_buf($v, $bft_v);
                        $btemp .= $bft_k . $bft_v;
                        $btemp_len += strlen($bft_k) + strlen($bft_v);
                    }
                    $buffer .= pack('N', $btemp_len) . $btemp;
                break;
            }
        } elseif (is_null($params)) {
            $buffer .= pack('C', 0);
        }
    }

    public function serialize($params)
    {
        $buffer = '';
        self::serialize_buf($params, $buffer);
        return $buffer;
    }

    public function serializeMulti(...$params)
    {
        $buffer = '';
        if (empty($params)) {
            self::serialize_buf($params, $buffer);
            return $buffer;
        }
        foreach ($params as $param) {
            if (is_array($param) && empty($param)) {
                $param = NULL;
            }
            self::serialize_buf($param, $buffer);
        }
        return $buffer;
    }

    static function deserialize_buf($data, &$pos = 0, $data_type = NULL)
    {
        $obj = NULL;
        if(NULL === $data_type) {
            $buf_type = unpack("Cmsg_type", substr($data, 0, 1));
            $pos = $pos + 1;
            $data_type = $buf_type['msg_type'];
        }

        switch ($data_type) {
            case Constants::DTYPE_NULL:
                $obj = null;
            break;
            case Constants::DTYPE_STRING:
                $body_len_buf = unpack("Nbody_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $obj = substr($data, $pos, $body_len_buf['body_len']);
                $pos = $pos + $body_len_buf['body_len'];
            break;
            case Constants::DTYPE_STRING_MAP:
                $body_len_buf = unpack("Nbody_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $map_buf = substr($data, $pos, $body_len_buf['body_len']);

                $obj = [];
                $map_pos = 0;
                $key_len_buf = unpack("Nkey_len", substr($map_buf, $map_pos, 4));
                $map_pos = $map_pos + 4;
                $key = substr($map_buf, $map_pos, $key_len_buf['key_len']);
                $map_pos = $map_pos + $key_len_buf['key_len'];
                while ($key !== false) {
                    $value_len_buf = unpack("Nvalue_len", substr($map_buf, $map_pos, 4));
                    $map_pos = $map_pos + 4;
                    $value = substr($map_buf, $map_pos, $value_len_buf['value_len']);
                    $map_pos = $map_pos + $value_len_buf['value_len'];

                    if ($value !== false) {
                        $obj[$key] = $value;
                    }

                    if ($map_pos == $body_len_buf['body_len']) {
                        break;
                    }
                    $key_len_buf = unpack("Nkey_len", substr($map_buf, $map_pos, 4));
                    $map_pos = $map_pos + 4;
                    $key = substr($map_buf, $map_pos, $key_len_buf['key_len']);
                    $map_pos = $map_pos + $key_len_buf['key_len'];
                }
                $pos = $pos + $body_len_buf['body_len'];
            break;
            case Constants::DTYPE_BYTE_ARRAY:
                $body_len_buf = unpack("Nbody_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $obj = Utils::get_bytes(substr($data, $pos, $body_len_buf['body_len']));
                $pos = $pos + $body_len_buf['body_len'];
            break;
            case Constants::DTYPE_STRING_ARRAY:
                $total_len_buf = unpack("Ntotal_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $total_len = $total_len_buf['total_len'];

                $obj = [];
                $str_arr_pos = 0;
                $str_arr_buf = substr($data, $pos, $total_len);

                while ($str_arr_pos < $total_len) {
                    $str_tmp = self::deserialize_buf($str_arr_buf, $str_arr_pos, Constants::DTYPE_STRING);
                    $obj[] = $str_tmp;
                }
                $pos = $pos + $total_len;
            break;
            case Constants::DTYPE_BOOL:
                $obj_tmp = unpack("Cobj_buf", substr($data, $pos, 1));
                $pos = $pos + 1;
                $obj = (bool)$obj_tmp['obj_buf'];
            break;
            case Constants::DTYPE_BYTE:
                $obj_buf = unpack("Xbuf", substr($data, $pos, 1));
                $pos = $pos + 1;
                $obj = $obj_buf['buf'];
            break;
            case Constants::DTYPE_INT16:
                $obj_buf = unpack("nbuf", substr($data, $pos, 2));
                $pos = $pos + 2;
                $obj = (int)$obj_buf['buf'];
            break;
            case Constants::DTYPE_INT32:
                $obj_buf = unpack("Nbuf", substr($data, $pos, 4));
                $pos = $pos + 4;
                $obj = (int)$obj_buf['buf'];
            break;
            case Constants::DTYPE_INT64:
                $obj_buf = unpack("Nbuf_upper/Nbuf_lower", substr($data, $pos, 8));
                $pos = $pos + 8;
                $obj = Utils::bigInt2float($obj_buf['buf_upper'], $obj_buf['buf_lower']);
            break;
            case Constants::DTYPE_FLOAT32:
                $obj_buf = unpack("Gbuf", substr($data, $pos, 4));
                $pos = $pos + 4;
                $obj = $obj_buf['buf'];
            break;
            case Constants::DTYPE_FLOAT64:
                $obj_buf = unpack("Ebuf", substr($data, $pos, 8));
                $pos = $pos + 8;
                $obj = $obj_buf['buf'];
            break;
            case Constants::DTYPE_MAP:
                $total_len_buf = unpack("Ntotal_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $total_len = $total_len_buf['total_len'];

                $obj = [];
                $map_pos = 0;
                $map_buf = substr($data, $pos, $total_len);

                while ($map_pos < $total_len) {
                    $k_type = unpack("Ck_type_buff", substr($map_buf, $map_pos, 1));
                    $map_pos = $map_pos + 1;
                    $obj_k_tmp = self::deserialize_buf($map_buf, $map_pos, $k_type['k_type_buff']);
                    
                    $v_type = unpack("Cv_type_buff", substr($map_buf, $map_pos, 1));
                    $map_pos = $map_pos + 1;
                    $obj_v_tmp = self::deserialize_buf($map_buf, $map_pos, $v_type['v_type_buff']);
                    $obj[$obj_k_tmp] = $obj_v_tmp;
                }
                $pos = $pos + $total_len;
            break;
            case Constants::DTYPE_ARRAY:
                $total_len_buf = unpack("Ntotal_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $total_len = $total_len_buf['total_len'];
                $obj = [];
                while ($pos < $total_len) {
                    $arr_type = unpack("Carr_type_buff", substr($data, $pos, 1));
                    $pos = $pos + 1;
                    $obj_tmp = self::deserialize_buf($data, $pos, $arr_type['arr_type_buff']);
                    $obj[] = $obj_tmp;
                }
                $pos = $pos + $total_len;
            break;
            default:
                throw new \Exception('Fail to Decode response body, got a no support type!');
        }
        return $obj;
    }

    public function deserialize($obj, $data)
    {
        if (empty($data)) {
            return $obj;
        }
        return self::deserialize_buf($data);
    }
}
