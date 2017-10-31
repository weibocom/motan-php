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

namespace Motan\Route;

class Range extends \Motan\Router 
{
    private static $_idc_route = [
            'aliyun'    => 'aliyun',
            'yf'        => 'yf',
            'tc'        => 'tc',
            'yz'        => 'tc',
            'bx'        => 'yf',
    ];
    
    private static $_idc_map = [
            'aliyun'    => [
                [173342720, 173670399], // 10.8
            ],
            'yf'        => [
    	       [172695040, 172695295], // 10.75.30
    	       [2886740480, 2886740735], // 172.16.42.%
    	       [2886765312, 2886765567], // 172.16.139
    	       [2886789888, 2886790143], // 172.16.235
            ],
            'tc'        => [
    	       [172559360, 172559615], // 10.73.12.%
    	       [2886756864, 2886757119], // 172.16.106.%
            ],
            'bx'        => [
    	       [172845056, 172845567], // 10.77.10[4-5]
            ],
            'yz'        => [
    	       [172826112, 172826367], // 10.77.30
            ],
    ];
    
    public function getIdcByIp($ip)
    {
        $ip2idc = [];
        $ip2long = ip2long($ip);
        $ips = [$ip2long];
        foreach (self::$_idc_map as $idc => $ip) {
            $ip2idc[$ip] = $idc;
            $ips[] = $ip;
        }
        sort($ips);
        $index = array_search($ip2long, $ips);
        if ($index <= 1 || $index >= count($ips) - 2) {
            return $this->getDefaultIdc();
        }
        $prev = $ip2idc[$index - 1];
        $next = $ip2idc[$index + 1];
        if ($prev == $next) {
            return $prev;
        }
        
        return $this->getDefaultIdc();
    }
    
    public function getAgentGroupByIp($ip)
    {
        if (empty($ip)) {
            return $this->getDefaultGroup();
        }
        $idc = $this->getIdcByIp($ip);
        
        return $this->getAgentGroupByIdc($idc);
    }
    
    public function getAgentGroupByIdc($idc)
    {
        if (array_key_exists($idc, self::$_idc_map)) {
            return self::$_idc_map[$idc];
        }
        
        return $this->getDefaultGroup();
    }
    
}
