<?php

namespace App\Assembly\Core\Response;

// JSON Response extends the base response
class JsonResponse extends AbstractResponse
{
    public function __construct()
    {
        $this->statusCode = 200; // Default success status code
    }

    public function setStatusCode(int $code) : void
    {
        $this->statusCode = $code;
    }

    public function setHeader(string $key, string $value) : void
    {
        header("$key: $value");
    }

    public function setData(array $data) : void
    {
        $this->data = $data;
    }

    public function send() : void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode($this->data);
        exit;
    }
}
