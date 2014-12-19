<?php
namespace Velox\Framework\Orm;

class RepositoryQuery {
    protected $repository;
    protected $where = array();
    protected $having = array();
    protected $orderBy = array();
    protected $groupBy = array();
    protected $startCount = null;
    protected $limitCount = null;

    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }

    public function getRepository() {
        return $this->repository;
    }

    public function setRepository(Repository $repository) {
        $this->repository = $repository;
        return $this;
    }

    public function where($whereSql) {
        $this->where[] = $whereSql;
        return $this;
    }

    public function having($havingSql) {
        $this->having[] = $havingSql;
        return $this;
    }

    public function orderBy($orderBySql) {
        $this->orderBy[] = $orderBySql;
        return $this;
    }

    public function groupBy($groupBySql) {
        $this->groupBy[] = $groupBySql;
        return $this;
    }

    public function start($startCount) {
        $this->startCount = $startCount;
        return $this;
    }

    public function limit($limitCount) {
        $this->limitCount = $limitCount;
        return $this;
    }

    public function find() {
        $config = array(
            'where' => implode(' AND ', array_map(function($v) { return '('.$v.')'; }, $this->where)),
            'having' => implode(' AND ', array_map(function($v) { return '('.$v.')'; }, $this->having)),
            'orderBy' => implode(', ', $this->orderBy),
            'groupBy' => implode(', ', $this->groupBy),
        );
        if (!is_null($this->startCount))
            $config['startCount'] = $this->startCount;
        if (!is_null($this->limitCount))
            $config['limitCount'] = $this->limitCount;
        return $this->repository->find($config);
    }
}
