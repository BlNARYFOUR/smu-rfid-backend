<?php
/**
 * Created by PhpStorm.
 * User: brend
 * Date: 4/12/2018
 * Time: 14:53
 */

namespace App\Helpers;

use Illuminate\Support\Arr;

class MessageHandler
{
    static $handlers = [];

    static function addConsumer($address, $object, $nameOfFunctionInObject)
    {
        array_push(static::$handlers, ["address" => $address, "handler" => [$object, $nameOfFunctionInObject]]);
    }

    static function listen($address, $connection, $data)
    {
        foreach (static::$handlers as $handlerMap) {
            $key = Arr::get($handlerMap, "address");
            $handler = Arr::get($handlerMap, "handler");

            if($handler != null && $address == $key)
            {
                call_user_func_array([$handler[0], $handler[1]], [$connection, $data]);
            }
        }
    }
}