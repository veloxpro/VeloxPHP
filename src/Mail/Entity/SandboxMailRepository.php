<?php
namespace Velox\Mail\Entity;

use Velox\Framework\Orm\Field;
use Velox\Framework\Orm\Repository;

class SandboxMailRepository extends Repository {
    public function __construct() {
        parent::__construct();
        $this->setTableName('velox_mail_sandboxMail');

        $this->field('id', Field::TYPE_INT)->setNoInsert(true)->setNoUpdate(true);
        $this->field('to', Field::TYPE_STR);
        $this->field('subject', Field::TYPE_STR);
        $this->field('headers', Field::TYPE_STR);
        $this->field('body', Field::TYPE_STR);
    }
}
