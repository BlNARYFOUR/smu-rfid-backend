<?php

namespace App\Http\Controllers;

use App\Helpers\MessageHandler;
use App\Models\Connection;
use App\Models\Deploy;
use App\SocketModels\Env;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketController extends Controller implements  MessageComponentInterface
{
    public static $env = null;

    public function __construct()
    {
        if(is_null($this::$env)) {
            $this::$env = new Env();
        }

        echo "Server started...\n";
    }

    function onOpen(ConnectionInterface $conn)
    {
        echo "Connection opened\n";
    }

    function onClose(ConnectionInterface $conn)
    {
        echo "Connection closed\n";

        if($this::$env instanceof Env) {
            $this::$env->markDeadConnection($conn);
        }
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error occurred: " . $e -> getMessage() . "\n";
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Message received: " . $msg . "\n";

        $jsonObj = json_decode($msg, true);
        $address = Arr::get($jsonObj, "address", null);
        $data = Arr::get($jsonObj, "data", null);
        MessageHandler::listen($address, $from, $data);
    }
}
