<?php
namespace Velox\Framework\Http;

class Cookie {
    protected $name;
    protected $value;
    protected $domain;
    protected $expire;
    protected $path;
    protected $isSecure;
    protected $isHttpOnly;

    public function __construct($name, $value = null, $expire = 0, $path = '/', $domain = null,
                                $isSecure = false, $isHttpOnly = true) {
        $this->setName($name);
        $this->setExpire($expire);
        $this->setValue($value);
        $this->setDomain($domain);
        $this->setPath($path);
        $this->isSecure($isSecure);
        $this->isHttpOnly($isHttpOnly);
    }

    public function __toString() {
        $str = urlencode($this->getName()).'=';

        if ('' === (string) $this->getValue()) {
            $str .= 'deleted; expires='.gmdate("D, d-M-Y H:i:s T", time() - 31536001);
        } else {
            $str .= urlencode($this->getValue());
            if ($this->getExpire() !== 0)
                $str .= '; expires='.gmdate("D, d-M-Y H:i:s T", $this->getExpire());
        }

        if ($this->getPath())
            $str .= '; path='.$this->getPath();

        if ($this->getDomain())
            $str .= '; domain='.$this->getDomain();

        if (true === $this->isSecure())
            $str .= '; secure';

        if (true === $this->isHttpOnly())
            $str .= '; httponly';

        return $str;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name))
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        if (empty($name))
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        $this->name = $name;
    }

    public function getExpire() {
        return $this->expire;
    }

    public function setExpire($expire) {
        if ($expire instanceof \DateTime) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);
            if (false === $expire || -1 === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }
        $this->expire = $expire;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = empty($path) ? '/' : $path;
    }

    public function isSecure($isSecure = null) {
        if (!is_null($isSecure))
            $this->isSecure = (bool) $isSecure;
        return $this->isSecure;
    }

    public function isHttpOnly($isHttpOnly = null) {
        if (!is_null($isHttpOnly))
            $this->isHttpOnly = (bool) $isHttpOnly;
        return $this->isHttpOnly;
    }
}
