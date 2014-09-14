<?php

namespace Velox\Extra\FlashBag;

class FlashBag {
    protected $sessionKey = 'Velox.Extra.FlashBag.Flashes';

    public function __construct() {
        if (!isset($_SESSION[$this->sessionKey]))
            $_SESSION[$this->sessionKey] = [];
    }

    public function addMessage($namespase, $msg) {
        if (!isset($_SESSION[$this->sessionKey][$namespase]))
            $_SESSION[$this->sessionKey][$namespase] = [];
        $_SESSION[$this->sessionKey][$namespase][] = $msg;
    }

    public function getMessages($namespace) {
        if (!isset($_SESSION[$this->sessionKey][$namespace]))
            return [];

        $messages = $_SESSION[$this->sessionKey][$namespace];
        $_SESSION[$this->sessionKey][$namespace] = [];
        return $messages;
    }
} 