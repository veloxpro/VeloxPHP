<?php

namespace Velox\Framework;

use Velox\Framework\Dbal\Mysql\Driver;
use Velox\Framework\Event\Event;
use Velox\Framework\Event\EventListener;
use Velox\Framework\Http\Request;
use Velox\Framework\Http\Response;
use Velox\Framework\Kernel\BaseComponent;
use Velox\Framework\Registry\Registry;
use Velox\Framework\Registry\Service;
use Velox\Framework\Router\HttpRouter;

class Component extends BaseComponent {
    public function getServices() {
        /*$response = new \Velox\Framework\Http\Response('blah');
        $cookie1 = new \Velox\Framework\Http\Cookie('1bbbbaa10', 'bb10');
        $cookie2 = new \Velox\Framework\Http\Cookie('2ccccaa11', 'bb11', '+10days');
        $response->setCookie($cookie1);
        $response->setCookie($cookie2);
        $response->send();*/
        /*$pb = new \Velox\Framework\Http\ParameterBag();
        $pb->setArray($_GET);
        _dump($pb->getString('tiv'));*/

        return array(
            'Velox.Http.Request' => new Service(function() {
                    return Request::createFromGlobals();
                }),
            'Velox.Http.Response' => new Service(function() {
                    return new Response();
                }),
            'Velox.HttpRouter' => new Service(function() {
                    $componentManager = Registry::get('Velox.ComponentManager');
                    $router = new HttpRouter();
                    $router->addRoutes($componentManager->getRoutes());
                    return $router;
                }),
            'MainDB' => new Service(function() {
                    // load settings
                    $driver = new Driver();
                    $conf = include('config/db.config.php');
                    $driver->connect($conf['host'], $conf['user'], $conf['password'], $conf['database']);
                    return $driver;
                }),
        );
    }

    public function getRoutes() {
        return array();
    }

    public function getEventListeners() {
        return array(
            new EventListener(array('Velox.Kernel.Launch'), function(Event $event) {
                //echo 'Fired!';
            }),
            new EventListener(array('Velox.Kernel.Halt'), function(Event $event) {
                Registry::get('Velox.Http.Response')->send();
                //echo 'Fired2!';
                //\Velox\Framework\Kernel\Kernel::halt();
            }),
        );
    }
}
