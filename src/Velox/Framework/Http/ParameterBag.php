<?php
namespace Velox\Framework\Http;

class ParameterBag {
    private $parameters = array();

    public function __construct(array $params = null) {
        if (is_array($params))
            $this->setArray($params);
    }

    public function set($key, $value) {
        $this->parameters[$key] = $value;
    }

    public function setArray(Array $parameters) {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function has($key) {
        return isset($this->parameters[$key]);
    }

    public function remove($key) {
        if ($this->has($key))
            unset($this->parameters[$key]);
    }

    public function get($key, $default) {
        if (!$this->has($key))
            return $default;
        return $this->parameters[$key];
    }

    public function getBool($key, $default = false) {
        return (bool) $this->get($key, $default);
    }

    public function getInt($key, $default = 0) {
        return (int) $this->get($key, $default);
    }

    public function getString($key, $default = '') {
        return (string) $this->get($key, $default);
    }

    public function getArray($key, $default = array()) {
        $a = $this->get($key, $default);
        if (!is_array($a))
            $a = $default;
        return $a;
    }

    public function getAlpha($key, $default = '') {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, ''));
    }

    public function getAlnum($key, $default = '') {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, ''));
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function getFile($key) {
        $params = $this->get($key, null);
        if ($params == null)
            return null;
        return new UploadedFile($key, $params);
    }
}
