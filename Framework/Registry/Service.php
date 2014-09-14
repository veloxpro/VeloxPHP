<?php
namespace Velox\Framework\Registry;

class Service {
    private $isShared = true;
    private $instance;
    private $factory;
    private $isInstantiated = false;

    public function __construct($factory = null) {
        if ($factory instanceof \Closure)
            $this->setFactory($factory);
    }

    public function setIsShared($isShared) {
        $this->isShared = (bool) $isShared;
    }

    public function getIsShared() {
        return $this->isShared;
    }

    public function getInstance() {
        return $this->instance;
    }

    public function setInstance($instance) {
        $this->instance = $instance;
        $this->isInstantiated = true;
    }

    public function getFactory() {
        return $this->factory;
    }

    public function setFactory(\Closure $factory) {
        $this->factory = $factory;
    }

    public function get() {
        if (!$this->isShared)
            $this->isInstantiated = false;

        if (!$this->isInstantiated) {
            if (is_object($this->factory) && $this->factory instanceof \Closure)
                $this->instance = call_user_func($this->getFactory());
            else
                throw new Exception\InvalidFactoryException();
            $this->isInstantiated = true;
        }
        return $this->instance;
    }
}
