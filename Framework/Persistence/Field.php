<?php
namespace Velox\Framework\Persistence;

class Field {
    CONST TYPE_BOOL = 1;
    CONST TYPE_INT = 2;
    CONST TYPE_FLOAT = 3;
    CONST TYPE_STR = 4;
    CONST TYPE_TIMESTAMP = 5;
    CONST TYPE_ARR = 6;
    CONST TYPE_OBJ = 7;

    protected $name;                    // Entity Property Name
    protected $nameInDB;                  // Entity Property Name in DB
    protected $type;                    // AbstractRepository::TYPE_... type of the field
    protected $noUpdate = false;        // skip for UPDATE queries
    protected $noInsert = false;        // skip for INSERT queries
    //protected $isNullable = false;      // can be null

    public function __construct($name, $type) {
        $this->name = $name;
        $this->collectionAttribute = $name;
        $this->type = $type;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getNameInDB() {
        return $this->nameInDB;
    }

    public function setNameInDB($nameInDB) {
        $this->nameInDB = $nameInDB;
        return $this;
    }

    /*public function setIsNullable($isNullable) {
        $this->isNullable = $isNullable;
        return $this;
    }*/

    /*public function getIsNullable() {
        return $this->isNullable;
    }*/

    public function setNoInsert($noInsert) {
        $this->noInsert = $noInsert;
        return $this;
    }

    public function getNoInsert() {
        return $this->noInsert;
    }

    public function setNoUpdate($noUpdate) {
        $this->noUpdate = $noUpdate;
        return $this;
    }

    public function getNoUpdate() {
        return $this->noUpdate;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getType() {
        return $this->type;
    }
}