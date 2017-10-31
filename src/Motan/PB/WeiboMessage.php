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

namespace Motan\PB;

use DrSlump\Protobuf;

/**
 * WeiboMessage for PHP 5.4+
 * 
 * <pre>
 * WeiboMessage
 * </pre>
 * 
 * @author idevz <zhoujing00k@gmail.com>
 * @version V1.0 [created at: 2016-10-03]
 */
abstract class  WeiboMessage extends Protobuf\Message
{
    protected $_MOTAN_JSON_RESULT = false;
    protected $_USE_WEIBOJSON_CODECER = false;
    protected $_DEFAULT_CODECER = null;

    public function setMotanJsonResult($json_rs)
    {
        $this->_MOTAN_JSON_RESULT = $json_rs;
    }

    public function getMotanJsonResult($value = '')
    {
        if ($this->_MOTAN_JSON_RESULT !== false) {
            return $this->_MOTAN_JSON_RESULT;
        }
        return false;
    }

    public function useWeiboJsonCodecer()
    {
        $this->_DEFAULT_CODECER = Protobuf::getCodec();
        Protobuf::setDefaultCodec(new WeiboJsonCodecer());
        $this->_USE_WEIBOJSON_CODECER = true;
    }

    public function resetCodecer()
    {
        if ($this->_DEFAULT_CODECER instanceof Protobuf\CodecInterface) {
            Protobuf::setDefaultCodec($this->_DEFAULT_CODECER);
        }
    }

    public function isUseingWeiboJsonCodecer()
    {
        return $this->_USE_WEIBOJSON_CODECER === true;
    }

    public function __construct($data = null)
    {
        parent::__construct($data);
        if (defined('USE_WEIBOJSON_CODECER') && USE_WEIBOJSON_CODECER === true) {
            Protobuf::setDefaultCodec(new WeiboJsonCodecer());
        }
    }

    // public static function deserialize($data,
    //                                    Protobuf\CodecInterface $codec = null){
    //   $codec = new WeiboJsonCodecer();
    //   $retval = new static();
    //   // echo "---------->" . strlen($data) . $data . PHP_EOL;
    //   $retval->parse($data, $codec);
    //   return $retval;
    // }

    // /**
    //  * Serialize the current object data
    //  *
    //  * @param CodecInterface|null $codec
    //  * @return string
    //  */
    // public function serialize(Protobuf\CodecInterface $codec = null)
    // {
    //     $codec = new WeiboJsonCodecer();
    //     return $codec->encode($this);
    // }
}
