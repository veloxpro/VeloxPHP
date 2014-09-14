<?php
namespace Velox\Security\Authentication;

class BaseUser {
    protected $id;
    protected $username;
    protected $password;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getIsActive() {
        return true;
    }

    public function getEmailVerification() {
        return null;
    }
}
