<?php
namespace Velox\Framework\Dbal\Mysql\QueryBuilder;

class Update extends Query {
    public function __construct() {
        parent::__construct(self::TYPE_UPDATE);
    }
}
