<?php

namespace App\Console\Commands;

use App\Http\Controllers\WebSocketController;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:run';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $wsc = new WebSocketController();

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $wsc
                )
            ),
            4321
        );

        $server->loop->addPeriodicTimer(0.25, [$wsc::$env, "update"]);
        $server->run();
    }
}
