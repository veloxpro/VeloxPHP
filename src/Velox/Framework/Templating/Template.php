<?php
namespace Velox\Framework\Templating;

use Velox\Framework\Mvc\Dispatcher;
use Velox\Framework\Registry\Registry;

class Template {
    protected $path = null;
    protected $vars = array();
    protected $parent = null;
    protected $blocks = array();
    protected $content = '';
    protected $_openBlocks = array();

    public function __construct($path = null, array $vars = array()) {
        if (!is_null($path))
            $this->setPath($path);
        if (!empty($vars))
            $this->addVars($vars);
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getVars() {
        return $this->vars;
    }

    public function addVars(array $vars) {
        $this->vars = array_merge($this->vars, $vars);
    }

    public function emptyVars() {
        $this->vars = array();
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent(Template $parent) {
        $this->parent = $parent;
    }

    public function startBlock($name) {
        $this->_openBlocks[] = $name;
        ob_start();
    }

    public function endBlock() {
        $name = array_pop($this->_openBlocks);
        if (!isset($this->blocks[$name]))
            $this->blocks[$name] = ob_get_clean();
        else
            ob_get_clean();
    }

    public function renderBlock($name) {
        if (isset($this->blocks[$name]))
            echo $this->blocks[$name];
    }

    public function render($blocks = array()) {
        $this->blocks = array_merge($this->blocks, $blocks);

        if (!file_exists($this->path))
            throw new Exception\TemplateNotFoundException(sprintf('Template file "%s" not found.', $this->path));

        ob_start();
        extract($this->vars, EXTR_REFS);
        include $this->path;
        $this->content = ob_get_clean();

        if (is_null($this->parent))
            return $this->content;
        else
            return $this->parent->render($this->blocks) . $this->content;
    }

    public function extend($path, $vars = array()) {
        $this->setParent(new Template($path, $vars));
    }

    public function escape($str) {
        return htmlspecialchars($str);
    }

    public function execute($component, $controller, $action) {
        $dispatcher = new Dispatcher($component, $controller, $action);
        echo $dispatcher->dispatch();
    }

    public function baseUrl() {
        return Registry::get('Velox.Http.Request')->getBaseUrl();
    }

    public function uri() {
        return Registry::get('Velox.Http.Request')->getRequestUri();
    }

    public function url() {
        return 'http://' . Registry::get('Velox.Http.Request')->server->getString('HTTP_HOST') . $this->uri();
    }

    public function route($name, array $params = array(), array $ignoreConstraintsFor = array()) {
        return Registry::get('Velox.HttpRouter')->generateUrl($name, $params, $ignoreConstraintsFor);
    }

    public function service($name) {
        return Registry::get($name);
    }

    public function request() {
        return Registry::get('Velox.Http.Request');
    }
}
