<?php
namespace Velox\Security\Authentication\UserProvider;

abstract class AbstractUserProvider {
    protected $userEntityClass;

    public function __construct($userEntityClass) {
        $this->userEntityClass = $userEntityClass;
    }

    public abstract function findUserByUsername($username);

    public abstract function findUserById($id);

    public function getUserEntityClass() {
        return $this->userEntityClass;
    }

    public function setUserEntityClass($userEntityClass) {
        $this->userEntityClass = $userEntityClass;
        return $this;
    }
}
