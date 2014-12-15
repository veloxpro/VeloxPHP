<?php
namespace Velox\Framework\Registry;

class Registry
{
    private static $registry = [];

    public static function get($name) {
        if (!isset(self::$registry[$name]))
            throw new Exception\NotFoundException();
        return self::$registry[$name]->get();
    }

    public static function set($name, Service $service) {
        self::$registry[$name] = $service;
    }

    public static function exists($name) {
        return isset(self::$registry[$name]);
    }
}
