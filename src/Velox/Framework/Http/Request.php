<?php
namespace Velox\Framework\Http;

use Velox\Framework\Http\ParameterBag;

class Request {
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    /** @var  ParameterBag */
    public $get;
    /** @var  ParameterBag */
    public $post;
    /** @var  ParameterBag */
    public $cookie;
    /** @var  ParameterBag */
    public $file;
    /** @var  ParameterBag */
    public $server;
    public $content; // TODO : implement request content abstraction
    public $header; // TODO: implement headers
    public $requestUri;
    protected $method;
    /** @var  ParameterBag */
    public $session;
    /** @var  ParameterBag */
    public $route;

    public static function createFromGlobals() {
        $request = new self();
        $request->setGet(new ParameterBag($_GET));
        $request->setPost(new ParameterBag($_POST));
        $request->setCookie(new ParameterBag($_COOKIE));
        $request->setSession(new ParameterBag($_SESSION));
        $request->setServer(new ParameterBag($_SERVER));
        $request->setFile(new ParameterBag($_FILES));
        return $request;
    }

    public function setGet(ParameterBag $get = null) {
        $this->get = is_null($get) ? new ParameterBag() : $get;
    }

    public function setPost(ParameterBag $post = null) {
        $this->post = is_null($post) ? new ParameterBag() : $post;
    }

    public function setCookie(ParameterBag $cookie = null) {
        $this->cookie = is_null($cookie) ? new ParameterBag() : $cookie;
    }

    public function setFile(ParameterBag $file = null) {
        $this->file = is_null($file) ? new ParameterBag() : $file;
    }

    public function setServer(ParameterBag $server = null) {
        $this->server = is_null($server) ? new ParameterBag() : $server;
    }

    public function setSession(ParameterBag $session = null) {
        $this->session = is_null($session) ? new ParameterBag() : $session;
    }

    public function setRoute(ParameterBag $route = null) {
        $this->route = is_null($route) ? new ParameterBag() : $route;
    }

    public function getMethod() {
        return strtoupper($this->server->getString('REQUEST_METHOD'));
    }

    public function getRequestUri($withQueryString = true) {
        $uri = $this->server->getString('REQUEST_URI');
        $qs = $this->server->getString('QUERY_STRING');
        if ($withQueryString || $qs == '')
            return $uri;
        return substr($uri, 0, strrpos($uri, '?'.$qs));
    }

    public function getBaseUrl() {
        $scriptName = $this->server->getString('SCRIPT_NAME');
        return 'http://' . $this->server->getString('HTTP_HOST') . dirname($scriptName) . '/';
    }
}
