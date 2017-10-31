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

/**
 * Created by PhpStorm.
 * User: zhoujing2
 * Date: 14/09/2017
 * Time: 7:19 PM
 */

namespace Motan\Serialize;


class GrpcJson implements \Motan\Serializer
{
    public function serialize($params)
    {
        return $params->serialize();
    }

    public function deserialize($obj, $data)
    {
        return $obj->deserialize($data);
    }
}