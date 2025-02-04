<?php

namespace App\Assembly\Core\Response;

// Abstract base class for different response types
abstract class AbstractResponse
{
    protected $data;
    protected $statusCode;
    protected $headers = [];

    // Set the status code
    abstract public function setStatusCode(int $code) : void;

    // Add a custom header
    abstract public function setHeader(string $key, string $value) : void;

    // Send the response
    abstract public function send() : void;

    // Set data for the response (e.g., JSON/XML data)
    abstract public function setData(array $data) : void;
}