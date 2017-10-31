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
class Message
{
    private $_header;
    private $_metadata;
    private $_body;
    private $_type;
    
    private $_msg_size;

    public function __construct(Header $header, $metadata, $body, $type)
    {
        $this->_header = $header;
        $this->_metadata = $metadata;
        $this->_body = $body;
        $this->_type = $type;
    }

    public function setMsgSize($msg_size)
    {
        if (!empty($msg_size)) {
            $this->_msg_size = $msg_size;
        }
    }

    public function getMsgSize()
    {
        if (!empty($this->_msg_size)) {
            return $this->_msg_size;
        }
        return false;
    }

    public function getHeader()
    {
        return $this->_header;
    }

    public function getMetadata()
    {
        return $this->_metadata;
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function encode()
    {
        $buffer = $this->_header->buildHeaderBuf();
        if (!isset($this->_metadata['M_p']) || !isset($this->_metadata['M_m'])) {
            throw new \Exception('None Service Or Method get');
        }
        $mt = [];
        foreach ($this->_metadata as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $mt[] = $k . "\n" . $v;
        }
        $mt_str = implode("\n", $mt);
        $buffer = $buffer . pack('N', strlen($mt_str)) . $mt_str;
        return $buffer . pack('N', strlen($this->_body)) . $this->_body;
    }
}
