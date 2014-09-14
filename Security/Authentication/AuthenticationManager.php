<?php
namespace Velox\Security\Authentication;

use Velox\Framework\Http\Cookie;
use Velox\Framework\Http\Request;
use Velox\Framework\Http\Response;
use Velox\Framework\Registry\Registry;
use Velox\Security\Authentication\UserProvider\AbstractUserProvider;
use Velox\Security\Exception\InsufficientPrivilegesException;

class AuthenticationManager {
    const HISTORY_SIGN_IN = 1;
    const HISTORY_SIGN_OUT = 2;
    const HISTORY_WRONG_USERNAME = 3;
    const HISTORY_WRONG_PASSWORD = 4;
    const HISTORY_INACTIVE_USER = 5;
    const HISTORY_NOT_VERIFIED_EMAIL = 6;

    protected $userEntityClass;
    protected $userProvider;
    protected $usernameFormField;
    protected $passwordFormField;
    protected $rememberFormField;
    protected $rememberExpire;
    protected $salt;
    protected $sessionKey = 'Velox.Security.UserId';
    protected $rememberTokenName = 'REMEMBER_TOKEN';
    protected $currentUser = null;
    protected $history = [];

    /*public function __construct() {
        // set current user
    }*/

    public function getUserEntityClass() {
        return $this->userEntityClass;
    }

    public function setUserEntityClass($userEntityClass) {
        if (!is_a($userEntityClass, 'Velox\\Security\\Authentication\\BaseUser', true)) {
            throw new \LogicException('Authentication: User Entity Class should be instance
                of (or inherit) Velox\\Security\\Authentication\\BaseUser');
        }
        $this->userEntityClass = $userEntityClass;
        return $this;
    }

    public function handleRequest(Request $request) {
        if (!$this->isAuthenticated())
            $this->loginRememberToken($request->cookie->getString($this->rememberTokenName));

        if ($request->post->has($this->usernameFormField)) {
            $username = $request->post->getString($this->usernameFormField);
            $password = $request->post->getString($this->passwordFormField);
            $isRemember = $request->post->getBool($this->rememberFormField);
            $this->login($username, $password, $isRemember);
        }
    }

    public function login($username, $password, $isRemember) {
        $user = $this->userProvider->findUserByUsername($username);
        if (is_null($user)) {
            $this->history[] = self::HISTORY_WRONG_USERNAME;
            sleep(1);
            return;
        }
        $hash = $this->hashPassword($password);
        if ($user->getPassword() != $hash) {
            $this->history[] = self::HISTORY_WRONG_PASSWORD;
            sleep(1);
            return;
        }
        if (!$user->getIsActive()) {
            $this->history[] = self::HISTORY_INACTIVE_USER;
            return;
        }
        if ($user->getEmailVerification() !== null) {
            $this->history[] = self::HISTORY_NOT_VERIFIED_EMAIL;
            return;
        }

        $this->_login($user);
        if ($isRemember)
            $this->sendRememberCookie($user);
        else
            $this->removeRememberCookie($user);
    }

    public function loginRememberToken($token) {
        if (empty($token))
            return;
        $parts = explode('_', $token, 3);
        if (count($parts) != 3)
            return;

        $time = (int)$parts[0];
        $userId = (int)$parts[1];
        $token = $parts[2];

        if ($time < 1 || $userId < 1 || strlen($token) < 1)
            return;

        if ($time + $this->rememberExpire < time())
            return;

        $user = $this->userProvider->findUserById($userId);
        if (!$user)
            return;
        $calculatedToken = $this->generateRememberToken($user, $time);

        if ($token === $calculatedToken) {
            $this->_login($user);
        }
    }

    protected function _login($user) {
        $_SESSION[$this->sessionKey] = $user->getId();
        $this->history[] = self::HISTORY_SIGN_IN;
    }

    public function logout() {
        unset($_SESSION[$this->sessionKey]);
        Registry::get('Velox.Http.Response')->setCookie(new Cookie($this->rememberTokenName, '', time() - 9999));
        $this->history[] = self::HISTORY_SIGN_OUT;
    }

    public function sendRememberCookie($user) {
        $time = time();
        $cookieValue = $time.'_'.$user->getId().'_'.$this->generateRememberToken($user, $time);
        Registry::get('Velox.Http.Response')->setCookie(
            new Cookie($this->rememberTokenName, $cookieValue, $time + $this->rememberExpire));
    }

    public function removeRememberCookie($user) {
        Registry::get('Velox.Http.Response')->setCookie(
            new Cookie($this->rememberTokenName, '', time() - 9999));
    }

    public function generateRememberToken($user, $time) {
        return sha1(md5($user->getPassword().$this->rememberExpire)
            .$user->getUsername().md5(sha1($user->getId().$time)));
    }

    public function hashPassword($password) {
        return sha1($password.$this->salt);
    }

    public function getUsernameFormField() {
        return $this->usernameFormField;
    }

    public function setUsernameFormField($usernameFormField) {
        $this->usernameFormField = $usernameFormField;
        return $this;
    }

    public function getPasswordFormField() {
        return $this->passwordFormField;
    }

    public function setPasswordFormField($passwordFormField) {
        $this->passwordFormField = $passwordFormField;
        return $this;
    }

    public function getRememberFormField() {
        return $this->rememberFormField;
    }

    public function setRememberFormField($rememberFormField) {
        $this->rememberFormField = $rememberFormField;
        return $this;
    }

    public function getUserProvider() {
        return $this->userProvider;
    }

    public function setUserProvider(AbstractUserProvider $userProvider) {
        $this->userProvider = $userProvider;
        return $this;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function setSalt($salt) {
        $this->salt = $salt;
        return $this;
    }

    public function getRememberExpire() {
        return $this->rememberExpire;
    }

    public function setRememberExpire($rememberExpire) {
        $this->rememberExpire = $rememberExpire;
        return $this;
    }

    public function isAuthenticated() {
        return isset($_SESSION[$this->sessionKey]) && $_SESSION[$this->sessionKey] > 0;
    }

    public function getUser() {
        if (is_null($this->currentUser) && $this->isAuthenticated()) {
            $userId = $_SESSION[$this->sessionKey];
            $this->currentUser = $this->userProvider->findUserById($userId);
        }
        return $this->currentUser;
    }

    public function failPrivileges() {
        throw new InsufficientPrivilegesException();
    }

    public function getHistory() {
        return $this->history;
    }
}
