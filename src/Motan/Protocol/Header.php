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

namespace Motan\Protocol;

use Motan\Utils;

/**
 * Motan Protocol for PHP 5.4+
 * 
 * <pre>
 * Motan 协议
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-10-02]
 */
class Header
{
    private $_magic;
    private $_msg_type;
    private $_version_status;
    private $_serialize;
    private $_request_id;

    public function __construct($msg_type, $version_status, $serialize, $request_id)
    {
        $this->_magic = MAGIC;
        $this->_msg_type = $msg_type;
        $this->_version_status = $version_status;
        $this->_serialize = $serialize;
        $this->_request_id = $request_id;
    }

    public function buildHeaderBuf()
    {
        $this->_request_id = 2008074349849897768674;
        $buffer = pack("n", $this->_magic);
        $buffer = $buffer . pack("C", $this->_msg_type);
        $buffer = $buffer . pack("C", $this->_version_status);
        $buffer = $buffer . pack("C", $this->_serialize);
        Utils::split2Int($upper, $lower, $this->_request_id);
        $buffer = $buffer . pack('NN', $upper, $lower);
        return $buffer;
    }

    public function setVersion($version)
    {
        if ($version > 31) {
            throw new \Exception('motan header: version should not great than 31');
        }
        $this->_version_status = ($this->_version_status & 0x07) | ($version << 3 & 0xf8);
    }

    public function getVersion()
    {
        return hexdec($this->_version_status >> 3 & 0x1f);
    }

    public function setHeartbeat($is_heartbeat)
    {
        if (true === $is_heartbeat) {
            $this->_msg_type = $this->_msg_type | 0x10;
        } else {
            $this->_msg_type = $this->_msg_type & 0xef;
        }
    }

    public function isHeartbeat()
    {
        return ($this->_msg_type & 0x10) == 0x10;
    }

    public function setGzip($is_gzip)
    {
        if (true === $is_gzip) {
            $this->_msg_type = $this->_msg_type | 0x08;
        } else {
            $this->_msg_type = $this->_msg_type & 0xf7;
        }
    }

    public function isGzip()
    {
        return ($this->_msg_type & 0x08) == 0x08;
    }

    public function setOneWay($is_one_way)
    {
        if (true === $is_one_way) {
            $this->_msg_type = $this->_msg_type | 0x04;
        } else {
            $this->_msg_type = $this->_msg_type & 0xfb;
        }
    }

    public function isOneWay()
    {
        return ($this->_msg_type & 0x04) == 0x04;
    }

    public function setProxy($is_proxy)
    {
        if (true === $is_proxy) {
            $this->_msg_type = $this->_msg_type | 0x02;
        } else {
            $this->_msg_type = $this->_msg_type & 0xfd;
        }
    }

    public function isProxy()
    {
        return ($this->_msg_type & 0x02) == 0x02;
    }

    public function setRequest($is_request)
    {
        if (true === $is_request) {
            $this->_msg_type = $this->_msg_type & 0xfe;
        } else {
            $this->_msg_type = $this->_msg_type | 0x01;
        }
    }

    public function isRequest()
    {
        return ($this->_msg_type & 0x01) == 0x00;
    }

    public function setStatus($status)
    {
        if ($status > 7) {
            throw new \Exception('motan header: status should not great than 7');
        }
        $this->_version_status = ($this->_version_status & 0xf8) | ($status & 0x07);
    }

    public function getStatus()
    {
        return hexdec($this->_version_status & 0x07);
    }

    public function setSerialize($serialize)
    {
        if ($serialize > 31) {
            throw new \Exception('motan header: serialize should not great than 31');
        }
        $this->_serialize = ($this->_serialize & 0x07) | ($serialize << 3 & 0xf8);
    }

    public function getSerialize()
    {
        return $this->_serialize >> 3 & 0x1f;
    }
}
