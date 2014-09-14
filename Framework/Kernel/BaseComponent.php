<?php
namespace Velox\Framework\Kernel;

use Velox\Framework\Registry\Registry;

abstract class BaseComponent {
    public abstract function getServices();
    public abstract function getRoutes();
    public abstract function getEventListeners();
    //public abstract function getTemplates();

    public function addBreadcrumb($title, $route, $routeParams = [], $userForBack = true) {
        $href = Registry::get('Velox.HttpRouter')->generateUrl($route, $routeParams);
        Registry::get('Velox.Extra.Breadcrumbs')->addItem($title, $href, $userForBack);
    }
}
