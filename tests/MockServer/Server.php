<?php


namespace MockServer;

use Exception;
use Motan\Protocol\Header;
use Motan\Protocol\Message;
use Motan\Protocol\Motan;
use Motan\Serializer;
use Motan\Utils;
use const Motan\Protocol\MSG_STATUS_EXCEPTION;
use const Motan\Protocol\MSG_TYPE_RESPONSE;

class Server
{
    private $socketServer;
    private $clientSocks = [];
    public function __construct($address = "127.0.0.1:9981")
    {
        $this->socketServer = Socket::getSocketServer($address);
        Log::Info("socket address: " . $address);
    }

    public function listen()
    {
        Log::Info("start listen");
        while (true) {
            $readSocks = $this->clientSocks;
            $readSocks[] = $this->socketServer;
            if (!@stream_select($readSocks,$write, $except, 300000))
            {
                Log::Info('something went wrong while selecting');
                exit(0);
            }

            if (in_array($this->socketServer, $readSocks)) {
                $newClient = stream_socket_accept($this->socketServer);
                Log::Info("newClient" );
                if ($newClient) {
                    Log::Info('Connection accepted from ' . stream_socket_get_name($newClient, true));
                    $this->clientSocks[] = $newClient;
                    Log::Info("Now there are total ". count($this->clientSocks) . " clients.");
                }
                unset($readSocks[array_search($this->socketServer, $readSocks) ]);
            }

            foreach($readSocks as $sock)
            {
                $this->doRequest($sock);
            }
        }
    }

    public function doRequest($sock)
    {
        Log::Info("do request" );
        try {
            $requestMsg = Motan::decode($sock);
        } catch (Exception $e) {
            unset($this->clientSocks[array_search($sock, $this->clientSocks)]);
            @fclose($sock);
            Log::Info("Close socket, Now there are total ". count($this->clientSocks) . " clients.");
            return;
        }

        $metaData = $requestMsg->getMetadata();
        $header = $requestMsg->getHeader();
        $serializer = Utils::getSerializer($header->getSerialize());
        if (empty($serializer)) {
            $metaData["M_e"] = "invalid serialize";
            $respHeader = Motan::buildResponseHeader($header->getRequestId(), $header->getSerialize(),MSG_STATUS_EXCEPTION);
            $message = new Message($respHeader,$metaData,"",MSG_TYPE_RESPONSE);
            @fwrite($sock, $message->encode());
            return;
        }
        $body = $requestMsg->getBody();
        if ($header->isGzip()) {
            $body = zlib_decode($body);
        }
        $msg = $this->call($metaData, $header, $body, $serializer);
        @fwrite($sock, $msg);
    }

    public function call(array $metaData,Header $header, $body, Serializer $serializer)
    {
        Log::Info("do call" );
        if (empty($metaData[BasicService::$serviceSign]) || empty($metaData[BasicService::$methodSign])) {
            return BasicService::exceptionResponse($metaData, $header->getRequestId(), $header->getSerialize(),
                (new MotanException("provider call panic" , 500, MotanException::ServiceException))->getMsg());
        }
        switch ($header->getSerialize()) {
            case 1:
                return GrpcPbService::call($metaData, $header, $body, $serializer);
            case 8:
                return BreezeService::call($metaData, $header, $body, $serializer);
            case 6:
            default:
                return SimpleService::call($metaData, $header, $body, $serializer);
        }
    }
}