<?php
namespace Velox\Framework\Orm;

class ResultCollection implements \Iterator, \Countable, \ArrayAccess {
    private $position = 0;
    private $cache = [];
    private $mysqlResource;
    private $repository;

    public function __construct(Repository $repository, $mysqlResource) {
        $this->repository = $repository;
        $this->mysqlResource = $mysqlResource;
        $this->position = 0;
        $this->fetchOne();
    }

    public function current() {
        return $this->cache[$this->position];
    }

    public function next() {
        ++$this->position;
        if (!isset($this->cache[$this->position]))
            $this->fetchOne();
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->cache[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }

    private function fetchOne() {
        $a = $this->mysqlResource->fetch_assoc();
        if (!is_null($a))
            $this->cache[] = $this->repository->createFromDbArray($a);
    }

    private function fetchToPosition($position) {
        for ($i = 0; $i <= $position; $i++) {
            if (!isset($this->cache[$i]))
                $this->fetchOne();
        }
    }

    public function count() {
        return $this->mysqlResource->num_rows;
    }

    public function offsetExists($offset) {
        $this->fetchToPosition($offset);
        return isset($this->cache[$offset]);
    }

    public function offsetGet($offset) {
        $this->fetchToPosition($offset);
        return isset($this->cache[$offset]) ? $this->cache[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        throw new \LogicException('ORM Result Collection is readonly.');
    }

    public function offsetUnset($offset) {
        throw new \LogicException('ORM Result Collection is readonly.');
    }
}