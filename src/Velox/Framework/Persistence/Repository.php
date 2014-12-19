<?php
namespace Velox\Framework\Persistence;

use Velox\Framework\Persistence\Adapter\AbstractAdapter\AbstractAdapter;
use Velox\Framework\Persistence\Query\Query;

abstract class Repository {
    protected $fields = array();
    protected $name;
    protected $adapter;
    protected $entityClassFqn;

    public abstract function retrieveAdapter();
    public abstract function retrieveFields();
    public abstract function retrieveName();
    public abstract function retrieveEntityClassFqn();

    public function __construct() {
        $this->setName($this->retrieveName());
        $this->setFields($this->retrieveFields());
        $this->setAdapter($this->retrieveAdapter());
        $this->setEntityClassFqn($this->retrieveEntityClassFqn());
    }

    public function setFields(Array $fields) {
        $this->fields = $fields;
        return $this;
    }

    public function getFields() {
        return $this->fields;
    }

    public function addField(Field $field) {
        $this->fields[] = $field;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getAdapter() {
        return $this->adapter;
    }

    public function setAdapter(AbstractAdapter $adapter) {
        $this->adapter = $adapter;
    }

    public function find(Query $query) {
        return array();
    }

    public function findById($id) {

    }

    public function findOne(Query $query) {

    }

    public function delete($entity) {

    }

    public function insert($entity) {
        _dump($this->toArray($entity, true, false));
    }

    public function update($entity) {

    }

    public function getEntityClassFqn() {
        return $this->entityClassFqn;
    }

    public function setEntityClassFqn($entityClassFqn) {
        $this->entityClassFqn = $entityClassFqn;
        return $this;
    }

    protected function toArray($entity, $includeNoUpdate = false, $includeNoInsert = false) {
        $toReturn = array();
        foreach ($this->fields as $f) {
            if (!$includeNoInsert && $f->getNoInsert())
                continue;
            if (!$includeNoUpdate && $f->getNoUpdate())
                continue;
            $getter = 'get' . ucfirst($f->getName());
            $v = $entity->$getter();
            _dump($v);
            $toReturn[$f->getNameInDB()] = $v;
        }
        return $toReturn;
    }

    protected function fromArray() {

    }

    protected function createNewEntity() {
        return new $this->entityClassFqn;
    }
}
