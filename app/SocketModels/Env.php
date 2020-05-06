<?php
/**
 * Created by PhpStorm.
 * User: brend
 * Date: 14/11/2019
 * Time: 14:54
 */

namespace App\SocketModels;


use App\Helpers\ConnectionFilter;
use App\Helpers\MessageHandler;
use Illuminate\Support\Arr;

class Env
{
    private $connections;

    public function __construct() {
        $this->connections = [];

        $this->initConsumers();
    }

    private function initConsumers() {
        MessageHandler::addConsumer("smugps.actions.connect", $this, "connectRequestHandler");
    }

    function update() {
        $this->removeDeadConnections();
        // $this->deployNewDeploys();
    }

    function deployNewDeploys() {
        //$deploys = Deploy::where('new', true)->get();

        /*
        foreach ($deploys as $deploy) {
            if($deploy instanceof Deploy) {
                $deploy->new = false;
                $deploy->save();

                $this->getConnectionById($deploy->connection_id)->getConnection()->send(json_encode([
                    "address" => "msega.actions.deploy",
                    //"deploy" => new DeployResource($deploy)
                ]));
            }
        }
        */
    }

    function markDeadConnection($connection) {
        $connections = array_filter($this->connections, array(new ConnectionFilter($connection), 'equals'));

        foreach ($connections as $con) {
            if($con instanceof Connection) {
                $this->getConnectionById($con->getId())->setDead(true);
            }
        }

        $this->logGameConnections();

        foreach ($this->connections as $con) {
            if($con instanceof Connection) {
                echo "isDead: " . $con->isDead() . "\n";
            }
        }
    }

    function removeDeadConnections() {
        for($i=count($this->connections)-1; $i>=0; $i--) {
            $gameConnection = $this->connections[$i];

            if($gameConnection instanceof Connection) {
                if($gameConnection->isDead()) {
                    array_splice($this->connections, $i, 1);
                }
            }
        }
    }

    function getConnectionById($id) {
        $gc = null;

        foreach ($this->connections as $gameConnection) {
            if($gameConnection instanceof Connection) {
                if ($gameConnection->getId() === $id) {
                    $gc = $gameConnection;
                }
            }
        }

        return $gc;
    }

    function addConnection($name, $connection) : Connection {
        array_push($this->connections, new Connection($name, $connection));

        $gc  = $this->connections[count($this->connections)-1];

        return $gc;
    }

    function connectRequestHandler($connection, $data) {
        echo "Start Request Handler: Data: " . json_encode($data);

        $name = Arr::get($data, "name", -1);
        var_dump($name);

        if ($name != -1 && !$this->isAlreadyUsed($connection)) {
            $gameConnection = $this->addConnection($name, $connection);
            $gameConnection->getConnection()->send(json_encode([
                "address" => "msega.actions.connect",
                "id" => $gameConnection->getId(),
            ]));
        }

        echo "Connected:\n";
        $this->logGameConnections();
    }

    function isAlreadyUsed($connection) {
        $arr = array_filter($this->connections, array(new ConnectionFilter($connection), "equals"));
        $arr = array_filter($arr, function (Connection $gameConnection) {
            return !$gameConnection->isDead();
        });

        return count($arr) != 0;
    }

    function logGameConnections() {
        $string = "";
        foreach ($this->connections as $player) {
            $string .= "\t" . $player . "\n";
        }
        echo $string;
    }
}