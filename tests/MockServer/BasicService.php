<?php


namespace MockServer;

use Motan\Protocol\Message;
use Motan\Protocol\Motan;
use const Motan\Protocol\MSG_STATUS_EXCEPTION;
use const Motan\Protocol\MSG_STATUS_NORMAL;
use const Motan\Protocol\MSG_TYPE_RESPONSE;

class Log {
    private static $filePath = __DIR__."/log.txt";
    public static function Info($info)
    {
        $dir_name = dirname(self::$filePath);
        if(!file_exists($dir_name)) {
           mkdir(iconv("UTF-8","GBK",$dir_name),0777,true);
        }
        $fp = fopen(self::$filePath,"a");
        fwrite($fp,var_export($info,true)."\r\n");//写入文件
        fclose($fp);//关闭资源通道
    }
}

class MotanException {
    const FrameworkException = 0;
    // ServiceException : exception by service call
    const ServiceException = 1;
    // BizException : exception by service implements
    const BizException = 2;

    private $metaData;
    public function __construct($errmsg = '', $errcode = 0, $errtype = 0)
    {
        $this->metaData = [
            'errcode' => $errcode,
            'errmsg' => $errmsg,
            'errtype' => $errtype
        ];
    }
    public function getMsg()
    {
        return json_encode($this->metaData);
    }
}

class BasicService
{
    public static $serviceSign = "M_p";
    public static $methodSign = "M_m";

    public static $exceptionEmptySM = "empty service or method";
    public static $exceptionNotMatchRoute = "provider call panic";

    public static function exceptionResponse($metaData, $requestId, $serialize, $exceptionMsg): string
    {
        $metaData["M_e"] = $exceptionMsg;
        $respHeader = Motan::buildResponseHeader($requestId, $serialize,MSG_STATUS_EXCEPTION);
        $message = new Message($respHeader,$metaData,"",MSG_TYPE_RESPONSE);
        return $message->encode();
    }

    public static function TransResponse($metaData, $requestId, $body, $serialize): string
    {
        $respHeader = Motan::buildResponseHeader($requestId, $serialize, MSG_STATUS_NORMAL);
        $respMsg = new Message($respHeader, $metaData, $body, MSG_TYPE_RESPONSE);
        return $respMsg->encode();
    }
}