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
    private $filter;

    function __construct($filter) {
        $this->filter = $filter;
    }

    function equals(Connection $connection) {
        return $connection->getConnection() == $this->filter;
    }

    function notEquals(Connection $connection) {
        return !$this->equals($connection);
    }

    function equalsOnName(Connection $connection) {
        return $connection->getName() == $this->filter;
    }
}