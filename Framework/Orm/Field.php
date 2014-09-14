<?php
namespace Velox\Framework\Orm;

class Field {
    CONST TYPE_BOOL = 1;
    CONST TYPE_INT = 2;
    CONST TYPE_FLOAT = 3;
    CONST TYPE_STR = 4;
    CONST TYPE_TIMESTAMP = 5;
    CONST TYPE_ARR = 6;
    CONST TYPE_OBJ = 7;

    protected $propName;         // Entity Property Name
    protected $type;             // AbstractRepository::TYPE_... type of the field
    protected $sql = null;              // sql expression (if different from ident)
    protected $noUpdate;         // skip for INSERT queries
    protected $noInsert;         // skip for INSERT queries
    protected $isNullable;
    protected $isPk = false;

    public function __construct($propName, $type) {
        $this->setPropName($propName);
        $this->setType($type);
    }

    public function isNoInsert() {
        return $this->noInsert;
    }

    public function setNoInsert($noInsert) {
        $this->noInsert = (bool) $noInsert;
        return $this;
    }

    public function isNoUpdate() {
        return $this->noUpdate;
    }

    public function setNoUpdate($noUpdate) {
        $this->noUpdate = (bool) $noUpdate;
        return $this;
    }

    public function getPropName() {
        return $this->propName;
    }

    public function setPropName($propName) {
        $this->propName = $propName;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getIsNullable() {
        return $this->isNullable;
    }

    public function setIsNullable($isNullable) {
        $this->isNullable = $isNullable;
        return $this;
    }

    public function getSql($tableName) {
        if (is_null($this->sql))
            return sprintf('`%s`.`%s`', $tableName, $this->propName);
        return $this->sql;
    }

    public function setSql($sql) {
        $this->sql = $sql;
        return $this;
    }

    public function isPk() {
        return $this->isPk;
    }

    public function setIsPk($isPk) {
        $this->isPk = $isPk;
    }
}
