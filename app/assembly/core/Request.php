<?php

namespace App\Assembly\Core;

class Request
{
    protected array $attributes = [];

    public function __construct()
    {
        $this->fill($_REQUEST); // Fill with $_REQUEST data by default
    }

    public function fill(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key)
    {
        return isset($this->attributes[$key]);
    }

    public static function json(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    public static function xml(): array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($input);
        if ($xml === false) {
            return [];
        }

        return json_decode(json_encode($xml), true);
    }

    public static function get(string $key): mixed
    {
        return $_GET[$key] ?? null;
    }

    public static function post(string $key): mixed
    {
        return $_POST[$key] ?? null;
    }

    public static function all(): array
    {
        return $_REQUEST;
    }

    public static function has(string $key): bool
    {
        return isset($_REQUEST[$key]);
    }

    public function input(string $key): mixed
    {
        return $this->attributes[$key] ?? null; // Instance method
    }

    public static function file(string $key): mixed
    {
        return $_FILES[$key] ?? null;
    }

    public static function server(string $key): mixed
    {
        return $_SERVER[$key] ?? null;
    }

    public static function cookie(string $key): mixed
    {
        return $_COOKIE[$key] ?? null;
    }

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function is(string $method): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function isPut(): bool
    {
        return self::method() === 'PUT';
    }

    public static function isDelete(): bool
    {
        return self::method() === 'DELETE';
    }

    public static function isPatch(): bool
    {
        return self::method() === 'PATCH';
    }

    public static function isHead(): bool
    {
        return self::method() === 'HEAD';
    }

    public static function isOptions(): bool
    {
        return self::method() === 'OPTIONS';
    }

    public static function isTrace(): bool
    {
        return self::method() === 'TRACE';
    }

    public static function isConnect(): bool
    {
        return self::method() === 'CONNECT';
    }

    public static function isSecure(): bool
    {
        return ($_SERVER['HTTPS'] ?? null) === 'on';
    }

    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) === 'XMLHttpRequest';
    }

    public static function isJson(): bool
    {
        return ($_SERVER['CONTENT_TYPE'] ?? null) === 'application/json';
    }

    public static function isXml(): bool
    {
        return ($_SERVER['CONTENT_TYPE'] ?? null) === 'application/xml';
    }
}