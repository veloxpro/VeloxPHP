<?php
namespace Velox\Framework\Http;

class Response {
    protected $headers = [];
    protected $version = '1.1';
    protected $charset = 'UTF-8';
    protected $statusCode;
    protected $content = '';
    protected $cookies = [];

    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;
    const HTTP_ALREADY_REPORTED = 208;
    const HTTP_IM_USED = 226;
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;
    const HTTP_UNPROCESSABLE_ENTITY = 422;
    const HTTP_LOCKED = 423;
    const HTTP_FAILED_DEPENDENCY = 424;
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;
    const HTTP_UPGRADE_REQUIRED = 426;
    const HTTP_PRECONDITION_REQUIRED = 428;
    const HTTP_TOO_MANY_REQUESTS = 429;
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
    const HTTP_INSUFFICIENT_STORAGE = 507;
    const HTTP_LOOP_DETECTED = 508;
    const HTTP_NOT_EXTENDED = 510;
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function __construct($content = '', $status = 200, array $headers=[]) {
        $this->content = $content;
        $this->statusCode = $status;
        if (!empty($headers))
            $this->setHeaders($headers);
    }

    public function __toString()
    {
        $toReturn = '';
        $headers = $this->getPreparedHeaders();
        foreach ($headers as $h)
            $toReturn .= $h . "\r\n";

        return $toReturn . $this->getContent();
    }

    public function send() {
        $this->sendHeaders();
        echo $this->content;

        return $this;
    }

    public function sendHeaders() {
        if (!headers_sent()) {
            $prepared = $this->getPreparedHeaders();
            foreach($prepared as $h)
                header($h);

            foreach ($this->getCookies() as $cookie) {
                setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpire(), $cookie->getPath(),
                    $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
            }
        }

        return $this;
    }

    public function getPreparedHeaders() {
        $prepared = [sprintf("HTTP/%s %s %s", $this->version, $this->getStatusCode(), $this->getStatusText())];
        foreach ($this->headers as $key => $value)
            $prepared[] = $key . ': ' . $value;
        return $prepared;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function addContent($content) {
        $this->content .= $content;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders($headers) {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setCookie(Cookie $cookie) {
        $this->cookies[] = $cookie;
        return $this;
    }

    public function getCookies() {
        return $this->cookies;
    }

    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getStatusText() {
        return isset(self::$statusTexts[$this->statusCode]) ? self::$statusTexts[$this->statusCode] : '';
    }

    public function isInvalid() {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    public function isInformational() {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    public function isSuccessful() {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirection() {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError() {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError() {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    public function isOk() {
        return 200 === $this->statusCode;
    }

    public function isForbidden() {
        return 403 === $this->statusCode;
    }

    public function isNotFound() {
        return 404 === $this->statusCode;
    }

    public function isEmpty() {
        return in_array($this->statusCode, [204, 304]);
    }
}
