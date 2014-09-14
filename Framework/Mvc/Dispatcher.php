<?php
namespace Velox\Framework\Mvc;

use Velox\Framework\Http\Response;
use Velox\Framework\Registry\Registry;
use Velox\Framework\Event\Event;

class Dispatcher {
    private $component;
    private $controller;
    private $action = 'index';

    public function __construct($component = null, $controller = null, $action = null) {
        if (!is_null($component))
            $this->setComponent($component);
        if (!is_null($controller))
            $this->setController($controller);
        if (!is_null($action))
            $this->setAction($action);
    }

    public function dispatch($component = null, $controller = null, $action = null) {
        $eventManager = Registry::get('Velox.EventManager');
        $eventManager->broadcast(new Event('Velox.Dispatcher.beforeDispatch', $this));

        if (!is_null($component))
            $this->setComponent($component);
        if (!is_null($controller))
            $this->setController($controller);
        if (!is_null($action))
            $this->setAction($action);

        $controllerFqn = sprintf('\\%s\\Controller\\%sController', $this->getComponent(), $this->getController());
        if (!class_exists($controllerFqn))
            throw new Exception\ControllerNotFoundException(sprintf('Controller "%s" doesn\'t exists.', $controllerFqn));

        $controllerInstance = new $controllerFqn();
        $action = $this->action . 'Action';

        if (!method_exists($controllerInstance, $action))
            throw new Exception\ActionNotFoundException(sprintf('Action "%s" of controller %s doesn\'t exists.', $action, $controllerFqn));

        $content = call_user_func([$controllerInstance, $action]);

        $eventManager->broadcast(new Event('Velox.Dispatcher.afterDispatch', $this));
        return $content;
    }

    public function getComponent() {
        return $this->component;
    }

    public function setComponent($component) {
        $this->component = $component;
    }

    public function getController() {
        return $this->controller;
    }

    public function setController($controller) {
        $this->controller = $controller;
    }

    public function getAction() {
        return $this->action;
    }

    public function setAction($action) {
        $this->action = $action;
    }
}
