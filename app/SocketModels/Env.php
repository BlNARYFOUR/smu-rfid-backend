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
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Ratchet\ConnectionInterface;

class Env
{
    private $connections;

    public function __construct() {
        $this->connections = [];

        $this->initConsumers();
    }

    private function initConsumers() {
        MessageHandler::addConsumer("smugps.actions.connect", $this, "connectRequestHandler");
        MessageHandler::addConsumer("smugps.actions.data", $this, "dataRequestHandler");
        MessageHandler::addConsumer("smugps.actions.detail", $this, "getTagInfoHandler");
        MessageHandler::addConsumer("smugps.actions.accept", $this, "acceptVehicleHandler");
        MessageHandler::addConsumer("smugps.actions.readers", $this, "getRfidReaderWsIdListHandler");
    }

    function update() {
        $this->removeDeadConnections();
    }

    function dataRequestHandler($connection, $data) {
        $tag = Arr::get($data, "tag", null);
        $action = Arr::get($data, "action", 1);
        $wsID = Arr::get($data, "ws_id", null);
        var_dump($tag);

        foreach ($this->connections as $con) {
            if($con instanceof Connection) {
                $con->getConnection()->send(json_encode([
                    "address" => "smugps.actions.tag",
                    "data" => [
                        "tag" => $tag,
                        "action" => $action,
                        "ws_id" => $wsID
                    ],
                ]));
            }
        }
    }

    function getTagInfoHandler(ConnectionInterface $connection, $data)
    {
        $tag = Arr::get($data, "tag", null);
        var_dump($tag);

        if (!is_null($tag)) {
            $vehicle = Vehicle::where('rfid_tag', $tag)->first();

            if (is_null($vehicle)) {
                $connection->send(json_encode([
                    "address" => "smugps.actions.detail",
                    "data" => [
                        "vehicle" => null
                    ],
                ]));
            } else {
                $connection->send(json_encode([
                    "address" => "smugps.actions.detail",
                    "data" => [
                        "vehicle" => new VehicleResource($vehicle)
                    ],
                ]));
            }
        }
    }

    function acceptVehicleHandler(ConnectionInterface $connection, $data) {
        echo "Accept vehicle - ";

        $tag = Arr::get($data, "rfid_tag", null);
        $vehicleId = Arr::get($data, "vehicle_id", null);
        $actionIn = Arr::get($data, "action", 1);

        echo $tag . " - ";
        echo $vehicleId . "\n";

        if(is_null($vehicleId)) {
            echo "ID_NULL\n";
        }

        $vehicleLog = new VehicleLog();
        $vehicleLog->action_in = $actionIn;
        $vehicleLog->rfid_tag = $tag;
        $vehicleLog->vehicle_id = $vehicleId;
        $vehicleLog->date = now();

        try {
            $vehicleLog->save();
        } catch (QueryException $exception) {
            echo "\n" . $exception->getMessage() . "\n\n";
            $connection->send(json_encode([
                "address" => "smugps.actions.error",
                "data" => [
                    'error' => $exception->getMessage()
                ],
            ]));
        }
    }

    function getRfidReaderWsIdListHandler(ConnectionInterface $connection, $data) {
        $connections = array_filter($this->connections, array(new ConnectionFilter('rfid-windows-app'), 'equalsOnName'));

        foreach ($connections as $con) {
            echo "\t" . $con . "\n";
        }

        $connection->send(json_encode([
            "address" => "smugps.actions.readers",
            "data" => [
                'rfid_reader_list' => $connections
            ],
        ]));
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
            $rfidConnection = $this->addConnection($name, $connection);
            $rfidConnection->getConnection()->send(json_encode([
                "address" => "smugps.actions.connect",
                "id" => $rfidConnection->getId(),
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