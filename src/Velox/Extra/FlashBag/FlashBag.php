<?php

namespace Velox\Extra\FlashBag;

class FlashBag {
    protected $sessionKey = 'Velox.Extra.FlashBag.Flashes';

    public function __construct() {
        if (!isset($_SESSION[$this->sessionKey]))
            $_SESSION[$this->sessionKey] = array();
    }

    public function addMessage($namespase, $msg) {
        if (!isset($_SESSION[$this->sessionKey][$namespase]))
            $_SESSION[$this->sessionKey][$namespase] = array();
        $_SESSION[$this->sessionKey][$namespase][] = $msg;
    }

    public function add($namespace, $msg) {
        return $this->addMessage($namespace, $msg);
    }

    public function getMessages($namespace) {
        if (!isset($_SESSION[$this->sessionKey][$namespace]))
            return array();

        $messages = $_SESSION[$this->sessionKey][$namespace];
        $_SESSION[$this->sessionKey][$namespace] = array();
        return $messages;
    }

    public function get($namespace) {
        return $this->getMessages($namespace);
    }
}