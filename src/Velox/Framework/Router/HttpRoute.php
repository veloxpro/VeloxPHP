<?php
namespace Velox\Framework\Router;

use Velox\Framework\Http\Request;

class HttpRoute {
    private $defaultVarRegexp = '[A-Za-z0-9-_]+';
    private $url;
    private $requirements;
    private $method;
    private $dispatcherFactory;
    private $matches = [];

    public function __construct(\Closure $dispatcherFactory, $url, array $requirements = [], $method = null) {
        $this->url = $url;
        $this->requirements = $requirements;
        $this->method = $method;
        $this->dispatcherFactory = $dispatcherFactory;
    }

    public function getDispatcher() {
        return call_user_func($this->dispatcherFactory, $this);
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getRequirements() {
        return $this->requirements;
    }

    public function addRequirements(array $requirements) {
        $this->requirements = array_merge($this->requirements, $requirements);
    }

    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function getMatches() {
        return $this->matches;
    }

    public function setMatches($matches) {
        $m = [];
        preg_match_all('/\[:([a-zA-Z0-9]+)\]/', $this->url, $m);
        $params = $m[1];

        $this->matches = [];
        foreach ($params as $k => $p) {
            if (!isset($matches[$k + 1]))
                break;
            $this->matches[$p] = $matches[$k + 1];
        }

        return $this->matches;
    }

    public function generateUrl(Request $request, $params = [], array $ignoreConstraintsFor = []) {
        $queryParams = [];
        $routeParams = [];

        $m = [];
        preg_match_all('/\[:([a-zA-Z0-9]+)\]/', $this->url, $m);
        $routeParamNames = $m[1];
        foreach ($params as $k => $v) {
            if (in_array($k, $routeParamNames))
                $routeParams[$k] = $v;
            else
                $queryParams[$k] = $v;
        }

        $url = $this->url;
        foreach ($routeParams as $key => $val) {
            if (in_array($key, $ignoreConstraintsFor))
                continue;
            $regexp = isset($this->requirements[$key]) ? $this->requirements[$key] : $this->defaultVarRegexp;
            $regexp = sprintf('/^%s$/', $regexp);
            if (!preg_match($regexp, $val)) {
                throw new Exception\InvalidParameterException(sprintf(
                    'Parameter "%s" = "%s" doesn\'t match required regexp "%s"',
                        $key, $val, $regexp));
            }
        }

        foreach ($routeParams as $key => $val) {
            $url = str_replace("[:$key]", $val, $url);
        }

        $parts = explode('[?]', $url);
        if (strpos($parts[0], '[:') !== false) {
            $pName = substr($parts[0], strpos($parts[0], '[:') + 2);
            $pName = strtok($pName, ']');
            throw new Exception\InvalidParameterException(sprintf('Parameter "%s" cannot be empty', $pName));
        }

        $u = '';
        foreach ($parts as $p) {
            if (strpos($p, '[:') !== false)
                break;
            $u .= $p;
        }

        if (count($queryParams) > 0) {
            $qs = [];
            foreach ($queryParams as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $p)
                        $qs[] = $k.'[]='.urlencode($p);
                } else {
                    $qs[] = $k.'='.urlencode($v);
                }
            }
            $u .= '?'.implode('&', $qs);
        }

        return $request->getBaseUrl() . ltrim($u, '/');
    }


    public function getRegexp() {
        $url = addcslashes($this->url, '/');

        $u = '';
        $a = explode('[:', $url);
        foreach ($a as $k => $p) {
            if ($k == 0) {
                $u .= $p;
                continue;
            }
            $nu = explode(']', $p, 2);
            if (count($nu) > 1) {
                $varRegexp = isset($this->requirements[$nu[0]]) ? $this->requirements[$nu[0]] : $this->defaultVarRegexp;
                $u .= sprintf('(%s)%s', $varRegexp, $nu[1] );
            } else {
                $u .= $nu[0];
            }
        }

        $parts = explode('[?]', $u);
        $append = str_repeat(')?', count($parts) - 1);
        $u = implode('(?:', $parts) . $append;

        return '/^' . $u . '$/';
    }

    public function match(Request $request) {
        if (!is_null($this->method) && $this->method != $request->getMethod())
            return false;

        $regexp = $this->getRegexp();
        $matches = [];
        $uri = $request->getRequestUri(false);
        $base = $request->server->getString('REDIRECT_BASE');
        if (strlen($base) < 1)
            $uri = '/';
        else
            $uri = '/' . substr($uri, strlen($base));

        if (preg_match($regexp, $uri, $matches)) {
            $this->setMatches($matches);
            return true;
        }
        return false;
    }
}
