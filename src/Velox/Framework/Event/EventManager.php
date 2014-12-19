<?php
namespace Velox\Framework\Event;

class EventManager {
    protected $listeners = array();

    public function registerListener(EventListener $eventListener) {
        $events = $eventListener->getEvents();

        foreach ($events as $e) {
            if (!isset($this->listeners[$e]))
                $this->listeners[$e] = array();
            $this->listeners[$e][] = $eventListener;
        }
    }

    public function broadcast(Event $event) {
        $name = $event->getName();
        if (!isset($this->listeners[$name]))
            return;
        foreach ($this->listeners[$name] as $listener) {
            if ($event->isPropagationStopped())
                break;
            $listener->fire($event);
        }
    }
}