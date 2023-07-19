<?php


namespace MockServer;


class Socket
{
    public static function getSocketServer(string $address)
    {
        $server = @stream_socket_server($address, $errno, $errorMessage);
        if ($server === false) {
            Log::Info("Could not bind to socket: $errorMessage");
            die("Could not bind to socket: $errorMessage");
        }
        return $server;
    }
}