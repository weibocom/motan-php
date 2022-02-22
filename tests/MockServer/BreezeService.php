<?php


namespace MockServer;


use Motan\Protocol\Header;
use Motan\Serializer;

class BreezeService extends BasicService
{
    public static function call(array $metaData,Header $header, $body, Serializer $serializer): string
    {
        // todo
        return self::TransResponse($metaData, $header->getRequestId(), $body,$header->getSerialize());
    }
}