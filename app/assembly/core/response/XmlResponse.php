<?php

namespace App\Assembly\Core\Response;

// XML Response extends the base response
class XmlResponse extends AbstractResponse
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
        header('Content-Type: application/xml');
        $xml = new \SimpleXMLElement('<root/>');
        array_walk_recursive($this->data, function($value, $key) use ($xml) {
            $xml->addChild($key, htmlspecialchars((string)$value));
        });
        echo $xml->asXML();
        exit;
    }
}
