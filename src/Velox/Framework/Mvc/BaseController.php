<?php
namespace Velox\Framework\Mvc;

use Velox\Framework\Http\Response;
use Velox\Framework\Kernel\Kernel;
use Velox\Framework\Registry\Registry;
use Velox\Framework\Templating\Template;

class BaseController {
    protected $classPath;

    public function __construct() {
        $reflector = new \ReflectionClass($this);
        $this->classPath = substr($reflector->getFileName(), strlen(rtrim(getcwd(), '/')) + 1);
    }

    public function call($component = null, $controller = null, $action = null) {
        if (is_null($component) && is_null($controller) && is_null($action))
            throw new \LogicException('Action calling the same action, circular calls detected.');

        if (is_null($component))
            $component = $this->getComponentNamespace();
        if (is_null($controller))
            $controller = $this->getControllerClass();
        if (is_null($action))
            $action = 'index';

        $dispatcher = new Dispatcher();
        return $dispatcher->dispatch($component, $controller, $action);
    }

    public function getComponentNamespace() {
        return strstr(get_class($this), '\\Controller\\', true);
    }

    public function getControllerClass() {
        $s = strstr(get_class($this), '\\Controller\\');
        $s = substr($s, strlen('\\Controller\\'));
        return strstr($s, 'Controller', true);
    }

    public function render($path, $vars = array(), $isRelative = true) {
        /*if (empty($path)) {
            // TODO: find the canonical path
        }*/

        $absolutePath = $path;
        if ($isRelative)
            $absolutePath = strstr($this->classPath, '/Controller/', true) . '/View/' . $absolutePath;
        return $this->renderAbsolute($absolutePath, $vars);
    }

    public function renderAbsolute($path, $vars = array()) {
        $tmpl = new Template($path, $vars);
        return $tmpl->render();
    }

    public function getBaseUrl() {
        return Registry::get('Velox.Http.Request')->getBaseUrl();
    }

    public function setRedirect($url) {
        Registry::get('Velox.Http.Response')->setStatusCode(Response::HTTP_PERMANENTLY_REDIRECT)->setHeader('location', $url);
    }

    public function generateUrl($route, $params = array(), array $ignoreConstraintsFor = array()) {
        return Registry::get('Velox.HttpRouter')->generateUrl($route, $params, $ignoreConstraintsFor);
    }

    public function requireAuthenticated() {
        if (!Registry::exists('Velox.Security.AuthenticationManager'))
            throw new \LogicException('Velox.Security component is required but not found.');
        $authenticationManager = Registry::get('Velox.Security.AuthenticationManager');
        if (!$authenticationManager->isAuthenticated()) {
            $this->setRedirect('/');
            exit;
        }
        return $authenticationManager->getUser();
    }

    public function addBreadcrumb($title, $route, $routeParams = array(), $useForBack = true) {
        $href = $this->generateUrl($route, $routeParams);
        Registry::get('Velox.Extra.Breadcrumbs')->addItem($title, $href, $useForBack);
    }
}
