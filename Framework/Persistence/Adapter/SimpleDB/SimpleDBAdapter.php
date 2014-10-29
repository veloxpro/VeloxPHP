<?php
namespace Velox\Framework\Persistence\Adapter\SimpleDB;

use Velox\Framework\Persistence\Adapter\AbstractAdapter\AbstractAdapter;
use Velox\Framework\Persistence\Query\Query;

class SimpleDBAdapter extends AbstractAdapter {
    public function query(Query $query) {
    }

    public function delete($id) {
    }

    public function insert(array $arr) {
    }

    public function update(array $arr) {
    }

    public function toStorage(array $arr) {
        $toReturn = [];
    }

    public function fromStorage(array $arr) {

    }
}
