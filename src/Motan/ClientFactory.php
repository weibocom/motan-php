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

use Motan\Utils;
use Motan\Client;

/**
 * ClientFactory for PHP 5.4+
 * 
 * <pre>
 * ClientFactory
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-08-15]
 */
class ClientFactory
{
	private static $_clients;
	private static $_service_conf;

	public static function GetClient($key)
	{
		if (isset(self::$_clients['key_client'])) {
			return self::$_clients['key_client'];
		} else {
			self::$_service_conf = Utils::GetServiceConf();
			if (!isset(self::$_service_conf[$key])) {
				throw new \Exception("Couldn't found this service_conf about key:" . $key, 1);
			}
			$service_info = self::$_service_conf[$key];
			$protocol = $service_info['protocol'] == 'motan2' ? TRUE : FALSE;
			$serialization = $service_info['serialization'] == 'simple' ? 1 : 2;
			isset($service_info['application']) && define("APP_NAME", $service_info['application']);
			self::$_clients['key_client'] = new Client($service_info['path'], $service_info['group'], $protocol, $serialization);
			return self::$_clients['key_client'];
		}
	}
}