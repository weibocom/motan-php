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

namespace Motan\Resty;

use \Motan\Interfaces\RestyConfs as RestyConfsInterface;

/**
 * Motan Resty Confs for PHP 5.4+
 * 
 * <pre>
 * Resty confs
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2017-3-07]
 */
class Confs implements RestyConfsInterface
{
	private $_resty_url;
	private $_resty_url_info;
	private $_motan_resty_conf;

	public function __construct($url)
	{
        $this->_resty_url_info = parse_url($url);
		$this->_resty_url = $url;
		$this->_motan_resty_conf = require MOTAN_RESTY_CONFS_FILE;
	}

	public function getRestyUrlInfo()
	{
		return $this->_resty_url_info;
	}

	public function getReqParams()
	{
		$rs = [];
		isset($this->_resty_url_info['query']) && parse_str($this->_resty_url_info['query'], $rs);
		return $rs;
	}

	public function getPath()
	{
		return $this->_resty_url_info['path'];
	}

	public function getService() {
		return $this->_motan_resty_conf[$this->_resty_url_info['host']]['service'];
	}

	public function getSerialization() {
		return $this->_motan_resty_conf[$this->_resty_url_info['host']]['serialization'] === 'simple' ? 1 : 2;
	}

	public function getProtocol() {
		return $this->_motan_resty_conf[$this->_resty_url_info['host']]['protocol'] === 'grpc' ? FALSE : TRUE;
	}

	public function getGroup() {
		return $this->_motan_resty_conf[$this->_resty_url_info['host']]['group'];
	}
}