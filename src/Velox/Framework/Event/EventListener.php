<?php
namespace Velox\Framework\Event;

class EventListener {
    protected $events;
    protected $closure;

    public function __construct(array $events, \Closure $closure) {
        $this->setEvents($events);
        $this->setClosure($closure);
    }

    public function getEvents() {
        return $this->events;
    }

    public function setEvents(array $events) {
        $this->events = $events;
    }

    public function addEvent($event) {
        $this->events[] = $event;
    }

    public function hasEvent($event) {
        return in_array($event, $this->events);
    }

    public function getClosure() {
        return $this->closure;
    }

    public function setClosure(\Closure $closure) {
        $this->closure = $closure;
    }

    public function removeEvent($event) {
        if (!$this->hasEvent($event))
            throw new \LogicException('Removing non-existing event rom listener.');
        unset($this->events[array_search($event, $this->events)]);
    }

    public function fire(Event $event) {
        $name = $event->getName();
        if (!in_array($name, $this->events))
            return;
        call_user_func($this->closure, $event);
    }
}
