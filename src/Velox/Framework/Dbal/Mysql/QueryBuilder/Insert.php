<?php
namespace Velox\Framework\Dbal\Mysql\QueryBuilder;

class Insert extends Query {
    public function __construct() {
        parent::__construct(self::TYPE_INSERT);
    }
}

