<?php
namespace Velox\Framework\Event;

class Event {
    protected $name;
    protected $target;
    protected $propagationStopped = false;

    public function __construct($name, $target = null) {
        $this->setName($name);
        $this->setTarget($target);
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getTarget() {
        return $this->target;
    }

    public function setTarget($target) {
        $this->target = $target;
    }

    public function stopPropagation() {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped() {
        return $this->propagationStopped;
    }

}
