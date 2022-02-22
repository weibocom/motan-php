<?php


namespace MockServer;


use Motan\Protocol\Header;
use Motan\Protocol\Message;
use Motan\Protocol\Motan;
use Motan\Serializer;
use const Motan\Protocol\MSG_STATUS_NORMAL;
use const Motan\Protocol\MSG_TYPE_RESPONSE;

class SimpleService extends BasicService
{
    public static $routeHello = "com.weibo.HelloMTService/Hello";
    private static $routeHelloX = "com.weibo.HelloMTService/HelloX";

    public static function call(array $metaData,Header $header, $body, Serializer $serializer): string
    {
        $token = $metaData[self::$serviceSign] . '/' . $metaData[self::$methodSign];
        switch ($token) {
            case self::$routeHello:
                return self::hello($metaData, $header, $body,  $serializer);
            case self::$routeHelloX:
                return self::exceptionResponse($metaData, $header->getRequestId(), $header->getSerialize(),
                    (new MotanException("method HelloX is not found in provider.",500, MotanException::ServiceException))->getMsg());
            default:
                return self::exceptionResponse($metaData, $header->getRequestId(), $header->getSerialize(),
                    (new MotanException("provider call panic" , 500, MotanException::ServiceException))->getMsg());
        }
    }

    public static function hello(array $metaData,Header $header, $body, Serializer $serializer): string
    {
        $body = $serializer->deserialize(null, $body);
        $respBody = $serializer->serialize("hello " . $body);
        $respHeader = Motan::buildResponseHeader($header->getRequestId(), $header->getSerialize(), MSG_STATUS_NORMAL);
        $respMsg = new Message($respHeader, $metaData, $respBody, MSG_TYPE_RESPONSE);
        return $respMsg->encode();
    }
}