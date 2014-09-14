<?php
namespace Velox\Mail;

use Velox\Mail\Entity\SandboxMailRepository;
use Velox\Mail\Entity\SandboxMail;
use Velox\Mail\Exception\MailException;

class Mail {
    protected $to;
    protected $body;
    protected $subject;
    protected $headers = [
        'Content-type' => 'text/html; charset=utf-8'
    ];
    protected $sandboxMode = true;

    public function __construct() {
        if (file_exists("app/config/mail.config.php")) {
            $c = require "app/config/mail.config.php";
            $this->sandboxMode = $c['sandboxMode'];
        }
    }

    public function setFrom($email, $name = null) {
        if ($name === null)
            $name = $email;
        $this->setHeader('From', sprintf('%s <%s>', $name, $email));
        return $this;
    }

    public function setReplyTo($email, $name = null) {
        if ($name === null)
            $name = $email;
        $this->setHeader('Reply-to', sprintf('%s <%s>', $name, $email));
        return $this;
    }

    public function getTo() {
        return $this->to;
    }

    public function setTo($to) {
        $this->to = $to;
        return $this;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
        return $this;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders(array $headers) {
        $this->headers = $headers;
        return $this;
    }

    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send() {
        if ($this->to === null)
            throw new Exception\MailException('Sending mail requires "to" field to be set.');
        if (!isset($this->headers['From']))
            throw new Exception\MailException('Sending mail requires "from" field to be set.');
        if ($this->body === null || strlen($this->body) < 1)
            throw new Exception\MailException('Sending mail requires "body" field to be set and not empty.');
        if ($this->subject === null || strlen($this->subject) < 1)
            throw new Exception\MailException('Sending mail requires "subject" field to be set and not empty.');

        $headers = '';
        foreach ($this->headers as $key => $value)
            $headers .= "$key: $value\r\n";

        if ($this->sandboxMode) {
            $sandboxMailRepository = new SandboxMailRepository();
            $sandboxMail = new SandboxMail();
            $sandboxMail->setTo($this->to);
            $sandboxMail->setHeaders($headers);
            $sandboxMail->setSubject($this->subject);
            $sandboxMail->setBody($this->body);
            $sandboxMailRepository->insert($sandboxMail);
        } else {
            $success = mail($this->to, $this->subject, $this->body, $headers);
            if (!$success)
                throw new MailException('Send Failed');
        }
    }
}