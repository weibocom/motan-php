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

use Motan\Constants;
use Motan\Utils;
use Motan\Client;

const MAGIC = 0xF1F1;
const MSG_TYPE = 0x02;
const VERSION_STATUS = 0x08;
const SERIALIZE = 0x08;

const SERIALIZE_HESSIAN = 0;
const SERIALIZE_PB = 1;
const SERIALIZE_SIMPLE = 1;

const MSG_STATUS_NORMAL = 0;
const MSG_STATUS_EXCEPTION = 1;

const MSG_TYPE_REQUEST = 0;
const MSG_TYPE_RESPONSE = 1;

const HEADER_BYTE = 13;
const META_SIZE_BYTE = 4;
const BODY_SIZE_BYTE = 4;

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
class Motan
{
    private static function buildHeader($msg_type, $proxy, $serialize, $request_id, $msg_status)
    {
        $m_type = 0x00;
        if ($proxy) {
            $m_type = $m_type | 0x02;
        }
        if ($msg_type == MSG_TYPE_REQUEST) {
            $m_type = $m_type & 0xfe;
        } else {
            $m_type = $m_type | 0x01;
        }

        $status = 0x08 | ($msg_status & 0x07);
        $serial = 0x00 | ($serialize << 3);
        return new Header($m_type, $status, $serial, $request_id);
    }

    public static function buildRequestHeader($request_id)
    {
        return self::buildHeader(MSG_TYPE_REQUEST, false, SERIALIZE_SIMPLE, $request_id, MSG_STATUS_NORMAL);
    }

    public static function buildResponseHeader($request_id, $msg_status)
    {
        return self::buildHeader(MSG_TYPE_RESPONSE, false, SERIALIZE_SIMPLE, $request_id, $msg_status);
    }

    public static function encode($request_id, $req_obj, $metadata)
    {
        $header = self::buildRequestHeader($request_id);
        if (defined('MOTAN_SERIALIZATION_TYPE')
            && (MOTAN_SERIALIZATION_TYPE === Client::MOTAN_SERIALIZATION_SIMPLE)) {
            $header->setSerialize(6);
        }
        if (isset($metadata['SERIALIZATION']) && $metadata['SERIALIZATION'] === Constants::SERIALIZATION_SIMPLE) {
            $header->setSerialize(6);
        }
        $msg = new Message($header, $metadata, $req_obj, MSG_TYPE_REQUEST);
        return $msg->encode();
    }

    public static function decode($connection)
    {
        $header_buffer = fread($connection, HEADER_BYTE);
        if (false == $header_buffer) {
            throw new \Exception('Error to read header_buffer');
        }
        $header = unpack("nmagic/CmessageType/Cversion_status/Cserialize/Nrequest_id_upper/Nrequest_id_lower", $header_buffer);
        $header['request_id'] = Utils::bigInt2float($header['request_id_upper'], $header['request_id_lower']);

        $header_obj = self::buildResponseHeader($header['request_id'], $header['version_status']);
        if (($header['messageType'] & 0x08) == 0x08) {
            $header_obj->setGzip(TRUE);
        }
        $metadata_size_buffer = fread($connection, META_SIZE_BYTE);
        if (false === $metadata_size_buffer) {
            throw new \Exception('Error to read metadata_size_buffer');
        }
        $metasize = unpack("Nmetasize", $metadata_size_buffer);
        $metadata = [];
        if ($metasize['metasize'] > 0) {
            $metadata_buffer = fread($connection, $metasize['metasize']);
            if (false === $metadata_buffer) {
                throw new \Exception('Error to read metadata_buffer');
            }
            $metadata_arr = explode("\n", unpack("A*metadata", $metadata_buffer)['metadata']);
            for ($i = 0; $i < count($metadata_arr); $i++) {
                $metadata[$metadata_arr[$i]] = $metadata_arr[++$i];
            }
        }
        // @TODO Exception
        $bodysize_buffer = fread($connection, BODY_SIZE_BYTE);
        if (false === $bodysize_buffer) {
            throw new \Exception('Error to read bodysize_buffer');
        }
        $body_size = unpack("Nbodysize", $bodysize_buffer);

        $body_buffer = '';
        while (($remaining = $body_size['bodysize'] - strlen($body_buffer)) > 0) {
            $buffer = @fread($connection, $remaining);
            if ($buffer === false) {
                return false;
            }
            $body_buffer .= $buffer;
        }
        return new Message($header_obj, $metadata, $body_buffer, MSG_TYPE_RESPONSE);
    }

    public static function getBuffer($buffer, &$pos, $size)
    {
        $rs = substr($buffer, $pos, $size);
        $pos += $size;
        return $rs;
    }

    public static function getMetaDataSize($buffer, $pos)
    {
        $metadata_size_buffer = substr($buffer, $pos, META_SIZE_BYTE);
        if (false === $metadata_size_buffer) {
            throw new \Exception('Error to read metadata_size_buffer');
        }
        $metasize = unpack("Nmetasize", $metadata_size_buffer);
        return $metasize['metasize'];
    }

    public static function getBodySize($buffer, $pos)
    {
        $bodysize_buffer = substr($buffer, $pos, BODY_SIZE_BYTE);
        if (false === $bodysize_buffer) {
            throw new \Exception('Error to read bodysize_buffer');
        }
        $body_size = unpack("Nbodysize", $bodysize_buffer);
        return $body_size['bodysize'];
    }

    public static function decodex($buffer)
    {
        if (empty($buffer)) {
            throw new \Exception('Got Empty buffer');
        }
        $buffer_len = strlen($buffer);
        if ($buffer_len < HEADER_BYTE) {
            return null;
        }
        $metasize = self::getMetaDataSize($buffer, HEADER_BYTE);
        $mt_check = HEADER_BYTE + META_SIZE_BYTE + $metasize;
        if ($buffer_len < $mt_check) {
            return null;
        }
        $bodysize = self::getBodySize($buffer, $mt_check);
        $msg_size = HEADER_BYTE + META_SIZE_BYTE + $metasize + BODY_SIZE_BYTE + $bodysize;
        if ($buffer_len < $msg_size) {
            return null;
        }
        $pos = 0;
        $header_buffer = self::getBuffer($buffer, $pos, HEADER_BYTE);
        $header = unpack("nmagic/CmessageType/Cversion_status/Cserialize/Nrequest_id_upper/Nrequest_id_lower", $header_buffer);
        $header['request_id'] = Utils::bigInt2float($header['request_id_upper'], $header['request_id_lower']);
        
        $header_obj = self::buildResponseHeader($header['request_id'], $header['version_status']);
        $pos += META_SIZE_BYTE;
        $metadata = [];
        if ($metasize > 0) {
            $metadata_buffer = self::getBuffer($buffer, $pos, $metasize);
            if (false === $metadata_buffer) {
                throw new \Exception('Error to read metadata_buffer');
            }
            $metadata_arr = explode("\n", unpack("A*metadata", $metadata_buffer)['metadata']);
            for ($i = 0; $i < count($metadata_arr); $i++) {
                $metadata[$metadata_arr[$i]] = $metadata_arr[++$i];
            }
        }

        $pos += BODY_SIZE_BYTE;

        $body_buffer = self::getBuffer($buffer, $pos, $bodysize);
        $msg = new Message($header_obj, $metadata, $body_buffer, MSG_TYPE_RESPONSE);
        $msg->setMsgSize($msg_size);
        return $msg;
    }
}
