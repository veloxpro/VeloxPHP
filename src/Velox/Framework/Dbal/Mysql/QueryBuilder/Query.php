<?php
namespace Velox\Framework\Dbal\Mysql\QueryBuilder;

class Query {
    const TYPE_SELECT = 1;
    const TYPE_INSERT = 2;
    const TYPE_UPDATE = 3;
    const TYPE_DELETE = 4;
    const TYPE_SYS = 5;

    protected $type;
    protected $where = array();
    protected $fieldPair = array();
    protected $fromPair = array();

    public function __construct($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function field($sql, $alias = null) {
        if (is_null($alias))
            $alias = $sql;
        $this->fieldPair[$alias] = $sql;
        return $this;
    }

    public function getFields() {
        return $this->fieldPair;
    }

    public function setFields(array $fields) {
        $this->fieldPair = $fields;
    }

    public function getFrom() {
        return $this->fromPair;
    }

    public function setFrom(array $from) {
        $this->fromPair = $from;
    }

    public function from($sqlOrSubquery, $alias = null) {
        if ($sqlOrSubquery instanceof Query && is_null($alias))
            throw new Exception\AliasRequiredException('Subquery should have an alias.');

        if (is_null($alias))
            $alias = $sqlOrSubquery;

        $this->fromPair[$alias] = $sqlOrSubquery;
        return $this;
    }

    public function getQuery() {
        $fields = array();
        foreach ($this->fieldPair as $alias => $sql) {
            $s = "$alias";
            if ($sql != $alias)
                $s = $sql.' AS '.$alias;
            $fields[] = $s;
        }
        _dump($fields);
    }

    /*public function execute($dbDriver = null) {
    }*/
}
