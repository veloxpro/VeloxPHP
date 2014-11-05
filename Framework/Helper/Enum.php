<?php
namespace Velox\Framework\Helper;

use ReflectionClass;

class Enum {
    private static $constCacheArray = null;

    public static function getAll() {
        if (self::$constCacheArray == null)
            self::$constCacheArray = [];
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function exists($value) {
        $values = array_values(self::getAll());
        return in_array($value, $values, $strict = true);
    }
}
