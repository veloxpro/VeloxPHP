<?php
namespace Velox\Framework\Orm;

use Velox\Framework\Dbal\Mysql\Exception\DuplicateKeyException;
use Velox\Framework\Event\Event;
use Velox\Framework\Registry\Registry;

class Repository {
    protected $_isConstructed = false;
    protected $dbDriver = null;
    protected $fields = array();
    protected $joins = array();
    protected $entityClass = null;
    protected $tableName = null;
    protected $dumpQueryOnce = false;
    protected $pk;

    public $onlyCountries = array();
    public $countryColumn = 'requestCountry';
    public $countryColumnAppendTable = true;

    public function __construct() {
        $this->_isConstructed = true;
        $this->dbDriver = Registry::get('MainDB');
    }

    public function getDbDriver() {
        return $this->dbDriver;
    }

    public function setDbDriver($dbDriver) {
        $this->dbDriver = $dbDriver;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setFields(array $fields) {
        $this->fields = $fields;
    }

    public function addField(Field $field) {
        $this->fields[] = $field;
        return $field;
    }

    public function field($name, $type) {
        $f = new Field($name, $type);
        $this->addField($f);
        return $f;
    }

    public function getFieldByPropName($propName) {
        foreach ($this->fields as $f) {
            if ($f->getPropName() == $propName)
                return $f;
        }
        foreach ($this->joins as $j) {
            foreach ($j->getFields() as $f) {
                if ($f->getPropName() == $propName)
                    return $f;
            }
        }
        return null;
    }

    public function getEntityClass() {
        if (is_null($this->entityClass)) {
            $repoFqn = get_called_class();
            $a = explode('\\', $repoFqn);
            $className = str_replace('Repository', '', end($a));
            $a[count($a) - 1] = $className;
            $entityFqn = implode('\\', $a);
            if ($entityFqn != $repoFqn && class_exists($entityFqn))
                $this->entityClass = $entityFqn;
            else
                $this->entityClass = '\stdClass';
        }
        return $this->entityClass;
    }

    public function setEntityClass($classFqn) {
        $this->entityClass = $classFqn;
    }

    public function getJoins() {
        return $this->joins;
    }

    public function setJoins(array $joins) {
        $this->joins = $joins;
        return $this;
    }

    public function addJoin(Join $join) {
        $this->joins[] = $join;
        return $this;
    }

    public function getTableName() {
        if (is_null($this->tableName)) {
            $a = explode('\\', get_called_class());
            $rc = end($a);
            $this->tableName = lcfirst(str_replace('Repository', '', $rc));
        }
        return $this->tableName;
    }

    public function setTableName($tableName) {
        $this->tableName = $tableName;
        return $this;
    }

    public function setDumpQueryOnce($dumpQueryOnce) {
        $this->dumpQueryOnce = (bool) $dumpQueryOnce;
    }

    public function isDumpQueryOnce() {
        return $this->dumpQueryOnce;
    }

    public function getEmptyObject() {
        $entityClass = $this->getEntityClass();
        return new $entityClass();
    }

    public function getPk() {
        foreach ($this->fields as $f) {
            if ($f->isPk())
                return $f;
        }
        $pk = $this->getFieldByPropName('id');
        if (is_null($pk))
            throw new \LogicException(sprintf('No Primary Key found for entity %s', $this->entityClass));
        return $pk;
    }

    public function find(array $config = array()) {
        if (!$this->_isConstructed)
            throw new \LogicException('ORM Repository constructor not called!');

        $fields = array();
        $where = (isset($config['where']) && $config['where'] != '') ? array($config['where']) : array();
        $having = (isset($config['having']) && $config['having'] != '') ? array($config['having']) : array();
        $orderBy = (isset($config['orderBy']) && $config['orderBy'] != '') ? array($config['orderBy']) : array();
        $groupBy = (isset($config['groupBy']) && $config['groupBy'] != '') ? array($config['groupBy']) : array();
        $startCount = isset($config['startCount']) ? $config['startCount'] : null;
        $limitCount = isset($config['limitCount']) ? $config['limitCount'] : null;
        $from = array('`' . $this->getTableName() . '`');

        foreach ($this->joins as $j)
            $from[] = $j->getSql();

        foreach ($this->fields as $f)
            $fields[] = sprintf('%s AS %s', $f->getSql($this->getTableName()), $f->getPropName());
        foreach ($this->joins as $j) {
            foreach ($j->getFields() as $f)
                $fields[] = sprintf('%s AS %s', $f->getSql($j->getTableName()), $f->getPropName());
        }

        $where = empty($where) ? 1 : implode(', ', $where);
        $having = empty($having) ? 1 : implode(', ', $having);
        $orderBy = empty($orderBy) ? '' : ('ORDER BY '.implode(', ', $orderBy));
        $groupBy = empty($groupBy) ? '' : ('GROUP BY '.implode(', ', $groupBy));

        $q = sprintf('SELECT %s FROM %s WHERE %s %s HAVING %s %s',
            implode(', ', $fields), implode(' ', $from),
            $where, $groupBy, $having, $orderBy);

        $q = $this->prepare($q);

        if (!is_null($startCount) && !is_null($limitCount))
            $q .= sprintf(' LIMIT %d, %d', $startCount, $limitCount);

        if ($this->dumpQueryOnce) {
            _dump($q);
            $this->dumpQueryOnce = false;
        }
        return new ResultCollection($this, $this->dbDriver->query($q));
    }

    public function findByPk($val) {
        $pk = $this->getPk();
        return $this->findOne(array('where' => '[$'.$pk->getPropName().']=[escape('.$val.')]'));
    }

    public function findWhere($whereSql) {
        return $this->find(array('where' => $whereSql));
    }

    public function findOne(array $config = array()) {
        if (!isset($config['startCount']) && !isset($config['limitCount'])) {
            $config['startCount'] = 0;
            $config['limitCount'] = 1;
        }
        $result = $this->find($config);
        return isset($result[0]) ? $result[0] : null;
    }

    public function delete($entity, $broadcastEvents = true) {
        if (is_null($entity))
            return null;
        $pk = $this->getPk();
        if (is_null($pk))
            throw new \LogicException(sprintf('Trying to delete entity "%s" with no Primary Key defined', $this->getEntityClass()));

        if (method_exists($entity, 'ormBeforeDelete'))
            $entity->ormBeforeDelete();
        $eventManager = null;
        $eventName = '';
        if ($broadcastEvents) {
            $eventName = str_replace('\\', '.', $this->getEntityClass());
            $eventManager = Registry::get('Velox.EventManager');
            $eventManager->broadcast(new Event($eventName.'.BeforeDelete', $entity));
        }

        $getter = 'get'.ucfirst($pk->getPropName());
        $pkVal = $entity->$getter();
        $whereSql = '[$'.$pk->getPropName().']=[escape('.$pkVal.')]';

        $q = sprintf('DELETE FROM `%s` WHERE %s', $this->getTableName(), $whereSql);
        $q = $this->prepare($q);

        if ($this->dumpQueryOnce) {
            $this->dumpQueryOnce = false;
            _dump($q);
        }

        $toReturn = $this->dbDriver->delete($q);
        if (method_exists($entity, 'ormAfterDelete'))
            $entity->ormAfterDelete();
        if ($broadcastEvents)
            $eventManager->broadcast(new Event($eventName.'.AfterDelete', $entity));

        return $toReturn;
    }

    /*public function deleteByPk($pkVal) {
        $pk = $this->getPk();
        if (is_null($pk))
            throw new \LogicException(sprintf('Trying to delete entity "%s" with no Primary Key defined', $this->getEntityClass()));

        if (is_null($pkVal))
            return;

        return $this->deleteWhere('[$'.$pk->getPropName().']=[escape('.$pkVal.')]');
    }*/

    /*public function deleteWhere($whereSql) {
        $whereSql = trim($whereSql);
        if (empty($whereSql))
            throw new \LogicException('Trying to delete entities with empty "where" statement');
        $q = sprintf('DELETE FROM %s WHERE %s', $this->getTableName(), $whereSql);
        $q = $this->prepare($q);

        if ($this->dumpQueryOnce) {
            $this->dumpQueryOnce = false;
            _dump($q);
        }

        return $this->dbDriver->delete($q);
    }*/

    public function insert($entity, $broadcastEvents = true) {
        if (method_exists($entity, 'ormBeforeInsert'))
            $entity->ormBeforeInsert();

        $eventManager = null;
        $eventName = '';
        if ($broadcastEvents) {
            $eventName = str_replace('\\', '.', $this->getEntityClass());
            $eventManager = Registry::get('Velox.EventManager');
            $eventManager->broadcast(new Event($eventName.'.BeforeInsert', $entity));
        }

        $a = $this->castToDbArray($entity);

        $fieldsSql = array();
        $valuesSql = array();
        foreach ($this->fields as $f) {
            if ($f->isNoInsert())
                continue;
            $propName = $f->getPropName();
            if (is_null($a[$propName]) && !$f->getIsNullable()) {
                throw new Exception\NullValueException(sprintf('Inserting null for not nullable "%s" property of "%s".',
                    $propName, $this->getEntityClass()));
            }
            $fieldsSql[] = $f->getSql($this->getTableName());
            if (is_null($a[$propName]))
                $valuesSql[] = 'NULL';
            else
                $valuesSql[] = $this->dbDriver->escape($a[$propName]);
        }

        $q = sprintf('INSERT INTO `%s` (%s) VALUES (%s)',
            $this->getTableName(), implode(', ', $fieldsSql), implode(', ', $valuesSql));
        $q = $this->prepare($q);

        if ($this->dumpQueryOnce) {
            $this->dumpQueryOnce = false;
            _dump($q);
        }

        $toReturn = $this->dbDriver->insert($q);
        $pk = $this->getPk();
        if ($pk)
            $entity->{'set' . ucFirst($pk->getPropName())}($toReturn);
        if (method_exists($entity, 'ormAfterInsert'))
            $entity->ormAfterInsert();
        if ($broadcastEvents)
            $eventManager->broadcast(new Event($eventName.'.AfterInsert', $entity));

        return $toReturn;
    }

    public function update($entity, $broadcastEvents = true) {
        $pk = $this->getPk();
        if (is_null($pk))
            throw new \LogicException(sprintf('Trying to update entity "%s" with no Primary Key defined', $this->getEntityClass()));

        if (method_exists($entity, 'ormBeforeUpdate'))
            $entity->ormBeforeUpdate();
        $eventManager = null;
        $eventName = '';
        if ($broadcastEvents) {
            $eventName = str_replace('\\', '.', $this->getEntityClass());
            $eventManager = Registry::get('Velox.EventManager');
            $eventManager->broadcast(new Event($eventName.'.BeforeUpdate', $entity));
        }

        $a = $this->castToDbArray($entity);

        // broadcast event

        $assignments = array();
        foreach ($this->fields as $f) {
            if ($f->isNoUpdate())
                continue;
            $propName = $f->getPropName();
            if (is_null($a[$propName]) && !$f->getIsNullable()) {
                throw new Exception\NullValueException(sprintf('Updating not nullable "%s" property of "%s" with null.',
                    $propName, $this->getEntityClass()));
            }
            if (is_null($a[$propName]))
                $assignments[] = $f->getSql($this->getTableName()).'=NULL';
            else
                $assignments[] = $f->getSql($this->getTableName()).'='.$this->dbDriver->escape($a[$propName]);
        }

        $q = sprintf('UPDATE `%s` SET %s WHERE %s',
            $this->getTableName(), implode(', ', $assignments),
            $pk->getSql($this->getTableName()).'='.$this->dbDriver->escape($a[$pk->getPropName()]));
        $q = $this->prepare($q);

        if ($this->dumpQueryOnce) {
            $this->dumpQueryOnce = false;
            _dump($q);
        }

        $toReturn = $this->dbDriver->update($q);
        if (method_exists($entity, 'ormAfterUpdate'))
            $entity->ormAfterUpdate();
        if ($broadcastEvents)
            $eventManager->broadcast(new Event($eventName.'.AfterUpdate', $entity));

        return $toReturn;
    }

    public function persist($entity, $broadcastEvents = true) {
        /*$pk = $this->getPk();
        if (is_null($pk))
            throw new \LogicException(sprintf('Trying to persist entity "%s" with no Primary Key defined', $this->getEntityClass()));

        $pkVal = $entity->{'get'.ucfirst($pk->getPropName())}();
        if (is_null($pkVal)) {
            $this->insert($entity, $broadcastEvents);
        } else {*/
            try {
                $this->insert($entity, $broadcastEvents);
            } catch (DuplicateKeyException $ex) {
                $this->update($entity, $broadcastEvents);
            }
        //}
    }

    public function getSqlByPropName($propName) {
        foreach ($this->fields as $f) {
            if ($f->getPropName() == $propName)
                return $f->getSql($this->tableName);
        }

        foreach ($this->joins as $j) {
            foreach ($j->getFields() as $f) {
                if ($f->getPropName() == $propName)
                    return $f->getSql($j->getTableName());
            }
        }

        return null;
    }

    public function prepare($q) {
        $self = $this;
        $q = preg_replace_callback('|\[([a-zA-Z0-9-_]+)\(([^[]+)?\)\]|', function($m) use ($self) {
            switch ($m[1]) {
                case 'escape':
                    return $self->dbDriver->escape(isset($m[2]) ? $m[2] : '');
                case 'int':
                    return (int) (isset($m[2]) ? $m[2] : 0);
                case 'float':
                    return (float) (isset($m[2]) ? $m[2] : 0);
                case 'bool':
                    return (bool) (isset($m[2]) ? $m[2] : false);
                default:
                    return null;
            }
        }, $q);
        $q = preg_replace_callback('|\[\$([a-zA-Z0-9-_]+)\]|', function($m) use ($self) {
            switch ($m[1]) {
                case '_tableName':
                    return $self->tableName;
                default:
                    return $self->getSqlByPropName($m[1]);
            }
        }, $q);
        return $q;
    }

    public function createFromDbArray(array $a) {
        $fields = $this->fields;
        foreach ($this->joins as $j)
            $fields = array_merge($fields, $j->getFields());

        $entityClass = $this->getEntityClass();
        $o = new $entityClass();
        foreach ($fields as $f) {
            $propName = $f->getPropName();
            $setter = 'set'.ucfirst($propName);
            if (!method_exists($o, $setter)) {
                throw new \LogicException(sprintf('Entity "%s" must have public setter "%s".',
                    $this->entityClass, $setter));
            }
            if (!array_key_exists($propName, $a)) {
                throw new \LogicException(sprintf('Missing sql column "%s" for entity "%s"',
                    $propName, $entityClass));
            }
            if (!$f->getIsNullable() && is_null($a[$propName])) {
                throw new \LogicException(sprintf('The field "%s" is not nullable, but it\'s value in database is null',
                    $propName));
            }

            if (is_null($a[$propName])) {
                $o->$setter(null);
            } else {
                switch ($f->getType()) {
                    case Field::TYPE_BOOL :
                        $o->$setter((bool) $a[$propName]);
                        break;
                    case Field::TYPE_INT :
                        $o->$setter((int) $a[$propName]);
                        break;
                    case Field::TYPE_FLOAT :
                        $o->$setter((float) $a[$propName]);
                        break;
                    case Field::TYPE_STR :
                        $o->$setter($a[$propName]);
                        break;
                    case Field::TYPE_TIMESTAMP :
                        $o->$setter((int) $a[$propName]);
                        break;
                    case Field::TYPE_ARR :
                        $o->$setter(empty($a[$propName]) ? array() : json_decode($a[$propName], true));
                        break;
                    case Field::TYPE_OBJ :
                        $o->$setter(empty($a[$propName]) ? null : json_decode($a[$propName], false));
                        break;
                    default :
                        throw new \LogicException('Field type doesn\'t exists.');
                }
            }
        }
        return $o;
    }

    public function castToDbArray($entity) {
        // WARNING: Don not modify $entity! it may contain reference
        // to external variable and cause side effects!
        $entityClass = $this->getEntityClass();
        if (!($entity instanceof $entityClass)) {
            throw new \LogicException(sprintf('Repository "%s" can update only instances of "%s"',
                get_called_class(), $entityClass));
        }
        $a = array();
        foreach ($this->fields as $f) {
            $propName = $f->getPropName();
            $getter = 'get'.ucfirst($propName);
            $v = $entity->$getter();
            if (is_null($v)) {
                $a[$propName] = null;
            } else {
                switch($f->getType()) {
                    case Field::TYPE_BOOL :
                        $a[$propName] = $v ? 1 : 0;
                        break;
                    case Field::TYPE_INT :
                        $a[$propName] = (int) $v;
                        break;
                    case Field::TYPE_FLOAT :
                        $a[$propName] = (float) $v;
                        break;
                    case Field::TYPE_STR :
                        $a[$propName] = $v;
                        break;
                    case Field::TYPE_TIMESTAMP :
                        $a[$propName] = (int) $v;
                        break;
                    case Field::TYPE_ARR :
                        if (!is_array($v) && !is_null($v)) {
                            throw new Exception\InvalidValueException(
                                sprintf('Value for the property "%s" of entity "%s" should be an array.',
                                    $propName, $this->getEntityClass()));
                        }
                        $a[$propName] = json_encode($v);
                        break;
                    case Field::TYPE_OBJ :
                        $a[$propName] = json_encode($v);
                        break;
                    default :
                        throw new \LogicException('Field type doesn\'t exists.');
                }
            }
        }
        return $a;
    }

    public function query() {
        return new RepositoryQuery($this);
    }
}
