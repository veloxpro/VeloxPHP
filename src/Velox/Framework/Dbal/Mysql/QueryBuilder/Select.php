<?php
namespace Velox\Framework\Dbal\Mysql\QueryBuilder;

class Select extends Query {
    public function __construct() {
        parent::__construct(self::TYPE_SELECT);
    }
}

