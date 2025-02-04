<?php

namespace App\Assembly\Core;

// Final response class that handles various types of responses
class Response
{
    // Set the status code
    public static function status(int $code) : void
    {
        http_response_code($code);
    }

    // Set header
    public static function header(string $key, string $value) : void
    {
        header("$key: $value");
    }

    // Redirect
    public static function redirect(string $url) : void
    {
        header("Location: $url");
        exit;
    }

    // Handle JSON response
    public static function json(array $data, int $statusCode = 200)
    {
        $jsonResponse = new Response\JsonResponse();
        $jsonResponse->setData($data);
        $jsonResponse->setStatusCode($statusCode); // default status code for JSON
        $jsonResponse->send();
    }



    // Handle XML response
    public static function xml(array $data, int $statusCode = 200) 
    {
        $xmlResponse = new Response\XmlResponse();
        $xmlResponse->setData($data);
        $xmlResponse->setStatusCode($statusCode); // default status code for XML
        $xmlResponse->send();

    }
}
