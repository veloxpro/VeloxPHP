<?php
namespace Velox\Framework\Persistence\Adapter\AbstractAdapter;

use Velox\Framework\Persistence\Query\Query;

abstract class AbstractAdapter {
    public abstract function query(Query $query);

    public abstract function delete($id);

    public abstract function insert(array $arr);

    public abstract function update(array $arr);

    public abstract function toStorage(array $arr);

    public abstract function fromStorage(array $arr);
}
