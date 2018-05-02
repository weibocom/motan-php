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
    public function serialize($params)
    {
        $buffer = '';
        if (is_array($params)) {
            $buffer = pack('C', 2);
            $btemp = '';
            $btemp_len = 0;
            foreach ($params as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $btemp .= pack('N', strlen($k)) . $k . pack('N', strlen($v)) . $v;
                $btemp_len += strlen($k) + strlen($v) + 8;
            }
            $buffer = $buffer . pack('N', $btemp_len) . $btemp;
        } elseif (is_string($params)) {
            $buffer = pack('C', 1) . pack('N', strlen($params)) . $params;
        } elseif (is_null($params)) {
            $buffer = pack('C', 0);
        }
        return $buffer;
    }

    public function deserialize($obj, $data)
    {
        if (empty($data)) {
            return $obj;
        }
        $pos = 0;
        $type_buf = unpack("Cmsg_type", substr($data, 0, 1));
        $pos = $pos + 1;
        switch ($type_buf['msg_type']) {
            case 0:
                $obj = null;
                break;
            case 1:
                $body_len_buf = unpack("Nbody_len", substr($data, $pos, 4));
                $pos = $pos + 4;
                $obj = substr($data, $pos);
                break;
            case 2:
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
                break;
            default:
                throw new \Exception('Fail to Decode response body, got a no support type!');
        }
        return $obj;
    }
}
