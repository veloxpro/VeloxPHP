<?php
namespace Velox\Framework\Orm;

class Join {
    const TYPE_LEFT = 1;
    const TYPE_INNER = 2;
    const TYPE_RIGHT = 3;
    const TYPE_FULL = 4;

    protected $type;
    protected $tableName;
    protected $onSql;
    protected $alias;
    protected $fields = [];

    public function __construct($tableName, $type, $onSql) {
        $this->setTableName($tableName);
        $this->setType($type);
        $this->setOnSql($onSql);
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getTableName() {
        return $this->tableName;
    }

    public function setTableName($tableName) {
        $this->tableName = $tableName;
        return $this;
    }

    public function getOnSql() {
        return $this->onSql;
    }

    public function setOnSql($onSql) {
        $this->onSql = $onSql;
        return $this;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
        return $this;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setFields(array $fields) {
        $this->fields = $fields;
    }

    public function addField(Field $field) {
        $this->fields[] = $field;
    }

    public function field($name, $type) {
        $f = new Field($name, $type);
        $this->addField($f);
        return $f;
    }

    public function getSql() {
        $joinType = '';
        switch ($this->type) {
            case self::TYPE_LEFT:
                $joinType = 'LEFT JOIN'; break;
            case self::TYPE_RIGHT:
                $joinType = 'RIGHT JOIN'; break;
            case self::TYPE_INNER:
                $joinType = 'INNER JOIN'; break;
            case self::TYPE_FULL:
                $joinType = 'FULL JOIN'; break;
            default:
                throw new \LogicException('Unknown join type!');
        }

        return $joinType . ' ' . $this->tableName . ' ON ' . $this->onSql;
    }
}
