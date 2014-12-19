<?php
namespace Velox\Framework\Kernel;

class ComponentManager {
    private $components = array();

    public function push(BaseComponent $component) {
        $this->components[] = $component;
    }

    public function getServices() {
        $services = array();
        foreach ($this->components as $c) {
            $s = $c->getServices();
            if (!is_null($s))
                $services = array_merge($services, $s);
        }
        return $services;
    }

    public function getEventListeners() {
        $eventListeners = array();
        foreach($this->components as $c)
            $eventListeners = array_merge($eventListeners, $c->getEventListeners());
        return $eventListeners;
    }

    public function getRoutes() {
        $routes = array();
        foreach ($this->components as $c) {
            $r = $c->getRoutes();
            if (!is_null($r))
                $routes = array_merge($routes, $r);
        }
        return $routes;
    }
}
