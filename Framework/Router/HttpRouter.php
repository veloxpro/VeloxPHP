<?php
namespace Velox\Framework\Router;

use Velox\Framework\Http\ParameterBag;
use Velox\Framework\Http\Request;
use Velox\Framework\Http\Response;
use Velox\Framework\Registry\Registry;
use Velox\Framework\Event\Event;
use Velox\Framework\Router\Exception\RouteNotFoundException;

class HttpRouter {
    private $routes = [];

    public function getRoutes() {
        return $this->routes;
    }

    public function addRoute($name, HttpRoute $route) {
        if (isset($this->routes[$name]))
            throw new Exception\DuplicateNameForRouteException();
        $this->routes[$name] = $route;
    }

    public function addRoutes($routes) {
        $currentKeys = array_keys($this->routes);
        $pendingKeys = array_keys($routes);
        if (count(array_intersect($currentKeys, $pendingKeys)) > 0)
            throw new Exception\DuplicateNameForRouteException();

        $this->routes = array_merge($this->routes, $routes);
    }

    public function match(Request $request) {
        foreach ($this->routes as $r) {
            if ($r->match($request) !== false)
                return $r;
        }

        return null;
    }

    public function respond() {
        $eventManager = Registry::get('Velox.EventManager');
        $eventManager->broadcast(new Event('Velox.Router.beforeRespond', $this));

        $route = $this->match(Registry::get('Velox.Http.Request'));
        if (is_null($route)) {
            $response = Registry::get('Velox.Http.Response');
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->addContent($response->getStatusText());
        } else {
            Registry::get('Velox.Http.Request')->setRoute(new ParameterBag($route->getMatches()));
            $content = $route->getDispatcher()->dispatch();
            Registry::get('Velox.Http.Response')->addContent($content);
        }

        $eventManager->broadcast(new Event('Velox.Router.afterRespond', $this));
    }

    public function generateUrl($name, $params = []) {
        if (!isset($this->routes[$name]))
            throw new RouteNotFoundException(sprintf('Named route "%s" doesn\'t exists', $name));
        $request = Registry::get('Velox.Http.Request');
        return $this->routes[$name]->generateUrl($request, $params);
    }
}
