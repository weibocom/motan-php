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
 * Motan Protocol for PHP 5.6+
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
    /**
     * @param $msg_type
     * @param $proxy
     * @param $serialize  $serialize is hunman readable number
     * @param $request_id
     * @param $msg_status
     * @return Header
     */
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

    /**
     * @param $request_id
     * @return Header
     */
    public static function buildRequestHeader($request_id)
    {
        return self::buildHeader(MSG_TYPE_REQUEST, FALSE, SERIALIZE_SIMPLE, $request_id, MSG_STATUS_NORMAL);
    }

    /**
     * @param $request_id
     * @param $serialize $serialize is hunman readable number
     * @param $msg_status
     * @return Header
     */
    public static function buildResponseHeader($request_id, $serialize,$msg_status)
    {
        return self::buildHeader(MSG_TYPE_RESPONSE, FALSE, $serialize, $request_id, $msg_status);
    }

    public static function encode($request_id, $req_obj, $metadata)
    {
        $header = self::buildRequestHeader($request_id);
        if (isset($metadata['SERIALIZATION'])) {
            switch ($metadata['SERIALIZATION']) {
                case Constants::SERIALIZATION_SIMPLE:
                    $header->setSerialize(6);
                    break;
                case Constants::SERIALIZATION_BREEZE:
                    $header->setSerialize(8);
                    break;
                case Constants::SERIALIZATION_PB:// grpcPB
                    $header->setSerialize(1);
                    break;
                case Constants::SERIALIZATION_GRPC_JSON:
                    $header->setSerialize(7);
                    break;
            }
        }
        $msg = new Message($header, $metadata, $req_obj, MSG_TYPE_REQUEST);
        return $msg->encode();
    }

    public static function decode($connection)
    {
        $header_buffer = fread($connection, HEADER_BYTE);
        if (FALSE == $header_buffer) {
            $stream_meta = stream_get_meta_data($connection);
            if ($stream_meta['timed_out'] == TRUE) {
                throw new \Exception('Read header timeout.');
            } else {
                throw new \Exception('Unknow error when read header. Stream detail:' . var_export($stream_meta, TRUE));
            }
        }
        $header = unpack("nmagic/CmessageType/Cversion_status/Cserialize/Nrequest_id_upper/Nrequest_id_lower", $header_buffer);
        $header['request_id'] = Utils::bigInt2float($header['request_id_upper'], $header['request_id_lower']);
        $status = 0x08 | ($header['version_status'] & 0x07);
        $header_obj= new Header(MSG_TYPE_RESPONSE, $status, $header['serialize'], $header['request_id']);
        if (($header['messageType'] & 0x08) == 0x08) {
            $header_obj->setGzip(TRUE);
        }

        $metadata_size_buffer = fread($connection, META_SIZE_BYTE);
        if (FALSE === $metadata_size_buffer) {
            $stream_meta = stream_get_meta_data($connection);
            if ($stream_meta['timed_out'] == TRUE) {
                throw new \Exception('Read metasize timeout.');
            } else {
                throw new \Exception('Unknow error when read metasize. Stream detail:' . var_export($stream_meta, TRUE));
            }
        }
        $metasize = unpack("Nmetasize", $metadata_size_buffer);
        $metadata = [];
        if ($metasize['metasize'] > 0) {
            $metadata_buffer = fread($connection, $metasize['metasize']);
            if (FALSE === $metadata_buffer) {
                $stream_meta = stream_get_meta_data($connection);
                if ($stream_meta['timed_out'] == TRUE) {
                    throw new \Exception('Read metadata timeout.');
                } else {
                    throw new \Exception('Unknow error when read metadata. Stream detail:' . var_export($stream_meta, TRUE));
                }
            }
            $metadata_arr = explode("\n", unpack("A*metadata", $metadata_buffer)['metadata']);
            for ($i = 0; $i < count($metadata_arr); $i++) {
                $metadata[$metadata_arr[$i]] = $metadata_arr[++$i];
            }
        }

        $bodysize_buffer = fread($connection, BODY_SIZE_BYTE);
        if (FALSE === $bodysize_buffer) {
            $stream_meta = stream_get_meta_data($connection);
            if ($stream_meta['timed_out'] == TRUE) {
                throw new \Exception('Read bodysize timeout.');
            } else {
                throw new \Exception('Unknow error when read bodysize. Stream detail:' . var_export($stream_meta, TRUE));
            }
        }
        $body_size = unpack("Nbodysize", $bodysize_buffer);

        $body_buffer = '';
        while (($remaining = $body_size['bodysize'] - strlen($body_buffer)) > 0) {
            $buffer = @fread($connection, $remaining);
            if ($buffer === FALSE) {
                $stream_meta = stream_get_meta_data($connection);
                if ($stream_meta['timed_out'] == TRUE) {
                    throw new \Exception('Read body timeout.');
                } else {
                    throw new \Exception('Unknow error when read body. Stream detail:' . var_export($stream_meta, TRUE));
                }
            }
            $body_buffer .= $buffer;
        }
        return new Message($header_obj, $metadata, $body_buffer, MSG_TYPE_RESPONSE);
    }
}
