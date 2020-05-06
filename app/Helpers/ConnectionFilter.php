<?php
/**
 * Created by PhpStorm.
 * User: brend
 * Date: 7/12/2018
 * Time: 9:50
 */

namespace App\Helpers;


use App\SocketModels\Connection;

class ConnectionFilter
{
    private $connection;

    function __construct($connection) {
        $this->connection = $connection;
    }

    function equals(Connection $gameConnection) {
        return $gameConnection->getConnection() == $this->connection;
    }

    function notEquals(Connection $gameConnection) {
        return !$this->equals($gameConnection);
    }
}