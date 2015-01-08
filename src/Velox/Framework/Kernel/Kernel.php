<?php
namespace Velox\Framework\Kernel;

use Velox\Framework\Event\Event;
use Velox\Framework\Registry\Registry;
use Velox\Framework\Registry\Service;
use Velox\Framework\Event\EventManager;

class Kernel {
    protected static $isHalted = true;
    protected static $cwd;

    public static function init() {
        date_default_timezone_set('GMT');

        // for restore on halt. cwd is being changed during Kernel::halt when called on shutdown. PHP bug.
        self::$cwd = getcwd();

        if (!class_exists('Velox\Framework\Kernel\SplClassLoader'))
            require(__DIR__ . '/SplClassLoader.php');
        self::autoloadFromFolder('../vendor/veloxpro/VeloxPHP/src');
        self::autoloadFromFolder('src');

        Registry::set('Velox.ComponentManager', new Service(function() {
            return new ComponentManager();
        }));
        Registry::set('Velox.EventManager', new Service(function() {
            return new EventManager();
        }));
    }

    public static function autoloadFromFolder($path) {
        if (!is_dir($path))
            return;
        $nodes = scandir($path);
        foreach ($nodes as $node) {
            if ($node[0] !== '.' && is_dir($path . '/' . $node)) {
                $classLoader = new SplClassLoader($node, $path);
                $classLoader->register();
            }
        }
    }

    public static function registerComponent($componentNamespace) {
        $componentClass = $componentNamespace . '\\Component';
        if (!class_exists($componentClass))
            throw new Exception\ComponentNotFoundException('Component "' . $componentClass . '" not found.');
        $c = new $componentClass();

        $componentManager = Registry::get('Velox.ComponentManager');
        $componentManager->push($c);
    }

    public static function launch() {
        $componentManager = Registry::get('Velox.ComponentManager');

        $services = $componentManager->getServices();
        foreach ($services as $name => $service)
            Registry::set($name, $service);

        $eventManager = Registry::get('Velox.EventManager');
        $eventListeners = $componentManager->getEventListeners();
        foreach ($eventListeners as $l)
            $eventManager->registerListener($l);

        self::$isHalted = false;
        register_shutdown_function('\\Velox\\Framework\\Kernel\\Kernel::halt');

        $eventManager->broadcast(new Event('Velox.Kernel.Launch'));
    }

    public static function halt() {
        chdir(self::$cwd);

        if (self::$isHalted)
            return;
        self::$isHalted = true;

        Registry::get('Velox.EventManager')
            ->broadcast(new Event('Velox.Kernel.Halt'));

        exit;
    }
}
