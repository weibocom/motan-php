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

namespace Motan\Transport;

use Motan\URL;

/**
 * TCP Connection for PHP 5.6+
 *
 * <pre>
 * TCP 连接
 * </pre>
 *
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-11-18]
 */
class Connection
{
    protected $_url_obj;
    protected $_connection;
    protected $_connection_addr;

    public function __construct(URL $url_obj)
    {
        $this->_url_obj = $url_obj;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    public function buildConnection($connection_addr = NULL)
    {
        if (!empty($connection_addr)) {
            $this->_connection_addr = $connection_addr;
        } else {
            $this->_connection_addr = $this->_url_obj->getHost() . ':' . $this->_url_obj->getPort();
        }
        return $this->_initConnection();
    }

    private function _initConnection()
    {
        $retryCnt = -1;
        $retry_times = $this->_url_obj->getRetryTimes();
        $connection_time_out = $this->_url_obj->getConnectionTimeOut();
        $err_code = $err_msg = NULL;
        while ($retryCnt < $retry_times) {
            $connection = @stream_socket_client($this->_connection_addr, $err_code, $err_msg, $connection_time_out);
            if ($connection) {
                break;
            } else {
                $retryCnt++;
                usleep(10);
            }
        }
        if (!$connection) {
            throw new \Exception("Connect to $this->_connection_addr fail, err_code:{$err_code},err_msg:{$err_msg} ");
        }
        $this->_connection = $connection;
        $this->_setStreamOpt();
        return true;
    }

    private function _setStreamOpt()
    {
        if (!is_resource($this->_connection)) {
            return false;
        }
        $read_time_out = $this->_url_obj->getReadTimeOut();
        $read_buffer_size = $this->_url_obj->getReadBufferSize();
        $write_buffer_size = $this->_url_obj->getWriteBufferSize();
        @stream_set_timeout($this->_connection, 0, $read_time_out * 1000000); // 微秒级超时，大于1s的话php内核自动转
        @stream_set_read_buffer($this->_connection, $read_buffer_size);
        @stream_set_write_buffer($this->_connection, $write_buffer_size);
        return true;
    }

    public function write($buffer)
    {
        $length = strlen($buffer);
        while (true) {
            $sent = @fwrite($this->_connection, $buffer, $length);
            if ($sent === false) {
                $stream_meta = stream_get_meta_data($this->_connection);
                if ($stream_meta['timed_out'] == TRUE) {
                    throw new \Exception('Write to remote timeout.');
                } else {
                    throw new \Exception('Unknow error when write to remote. Stream detail:' . var_export($stream_meta, TRUE));
                }
            }
            if ($sent < $length) {
                $buffer = substr($buffer, $sent);
                $length -= $sent;
            } else {
                return true;
            }
            usleep(5);
        }
    }

    public function read()
    {
        return \Motan\Protocol\Motan::decode($this->_connection);
    }

    public function __destruct()
    {
        if ($this->_connection) {
            fclose($this->_connection);
        }
    }
}
