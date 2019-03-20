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

/**
 * Motan PHP Constants for PHP 5.4+
 * 
 * <pre>
 * Motan PHP Constants
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-08-08]
 */
class Constants
{
    const RESTY_COOKIE_PREFIX = 'M_cookie_';
    const DEFAULT_VERSION = 0.1;

    const REQ_URL_TYPE_RESTY = 'resty';
    const REQ_URL_TYPE_MOTAN = 'motan';

    const PROTOCOL_MOTAN2 = 'motan2';
    const PROTOCOL_GRPC = 'grpc';
    const PROTOCOL_CEDRUS = 'cedrus';
    const DEFAULT_POST_CONTENTTYPE= 'application/x-www-form-urlencoded';
    const PROTOCOL_CGI = 'cgi';
    const PROTOCOL_MEMCACHE = 'memcache';

    const SERIALIZATION_SIMPLE = 'simple';
    const SERIALIZATION_PB = 'pb';
    const SERIALIZATION_GRPC_JSON = 'grpc-json';

    const MOTAN_HA_FAILFAST = 'failfast';
    const MOTAN_HA_FAILOVER = 'failover';

    const MOTAN_LB_RANDOM = 'random';
    const MOTAN_LB_ROUNDROBIN = 'roundrobin';

    const ENDPOINT_AGENT = 'agent';
    const ENDPOINT_MOTAN = 'motan';
    const ENDPOINT_GRPC = 'grpc';

    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';

    const URL_GROUP_KEY = 'group';
    const URL_SERVICE_KEY = 'service';
    const URL_SERIALIZE_KEY = 'serialize';
    const URL_METHOD_KEY = 'method';

    const DEFAULE_APP_NAME='motan-php-with-none-appname';

    const MOTAN_CONNECTION_TIME_OUT = 0.2;
    const MOTAN_READ_TIME_OUT = 2;
    const DEFAULT_SOCKET_BUFFER_SIZE = 8192;
    const DEFAULT_SOCKET_CONNECT_RETRY_TIMES = 1;

    const DEFAULT_AGENT_HOST = '127.0.0.1';
    const DEFAULT_AGENT_PORT = 9981;

    // ---------------- simple serialize constants -----------------

    const DEFAULT_BUFFER_SIZE = 2048;

    const DTYPE_NULL = 0;
    const DTYPE_STRING = 1;
    const DTYPE_STRING_MAP = 2;
    const DTYPE_BYTE_ARRAY = 3;
    const DTYPE_STRING_ARRAY = 4;
    const DTYPE_BOOL = 5;
    const DTYPE_BYTE = 6;
    const DTYPE_INT16 = 7;
    const DTYPE_INT32 = 8;
    const DTYPE_INT64 = 9;
    const DTYPE_FLOAT32 = 10;
    const DTYPE_FLOAT64 = 11;

    const DTYPE_MAP = 20;
    const DTYPE_ARRAY = 21;
}
