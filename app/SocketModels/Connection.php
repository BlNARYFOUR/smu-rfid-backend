<?php
/**
 * Created by PhpStorm.
 * User: brend
 * Date: 14/11/2019
 * Time: 15:18
 */

namespace App\SocketModels;


use Ratchet\ConnectionInterface;

class Connection
{
    private static $nextId = 1;

    private $id;
    private $name;
    private $connection;

    private $isDead;

    function __construct($name, $connection) {
        $this->id = Connection::$nextId;
        Connection::$nextId++;

        $this->name = $name;
        $this->connection = $connection;

        $this->isDead = false;
    }

    function getConnection() : ConnectionInterface {
        return $this -> connection;
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function equals(Connection $gameConnection) {
        return $this->getId() == $gameConnection->getId();
    }

    function isDead() {
        return $this->isDead;
    }

    public function setDead($isDead)
    {
        $this->isDead = $isDead;
    }

    function __toString()
    {
        return "Connection: [id => " . $this->id . ", name => " . $this->name . ($this->isDead ? ", DEAD" : "") . "]";
    }
}