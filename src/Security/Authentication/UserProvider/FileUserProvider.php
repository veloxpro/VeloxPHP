<?php
namespace Velox\Security\Authentication\UserProvider;

class FileUserProvider extends AbstractUserProvider {
    protected $path;
    protected $allUsers = [];

    public function findUserByUsername($username) {
        foreach ($this->allUsers as $id => $params) {
            if ($params['username'] == $username)
                return $this->constructUser($id, $params);
        }
        return null;
    }

    public function findUserById($id) {
        return isset($this->allUsers[$id]) ? $this->constructUser($id, $this->allUsers[$id]) : null;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
        if (is_readable($this->path))
            $this->allUsers = include $this->path;
        else
            $this->allUsers = [];
        return $this;
    }

    protected function constructUser($id, $params) {
        $userEntityClass = $this->getUserEntityClass();
        $user = new $userEntityClass();
        $user->setId($id);
        foreach ($params as $k => $v)
            $user->{'set'.ucfirst($k)}($v);
        return $user;
    }
}
