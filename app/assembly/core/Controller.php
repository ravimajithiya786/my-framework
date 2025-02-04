<?php

namespace App\Assembly\Core;

use App\Config\Database;

class Controller
{

    public function __construct()
    {        
    }

    // Method to load a view with optional data
    public function view(string $view, array $data = [])
    {
        $viewPath = __DIR__ . "/../../views/{$view}.php"; // Assuming views are located in /views

        if (file_exists($viewPath)) {
            extract($data);  // Extract data as variables
            require_once $viewPath;
        } else {
            echo "View not found!";
        }
    }

    // Method to return JSON response
    public function json(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Method to return XML response
    public function xml(array $data)
    {
        header('Content-Type: application/xml');
        $xml = new \SimpleXMLElement('<root/>');
        array_walk_recursive($data, function($value, $key) use ($xml) {
            $xml->addChild($key, htmlspecialchars((string)$value));
        });
        echo $xml->asXML();
        exit;
    }

    // Method to handle redirects
    public function redirect(string $url)
    {
        header("Location: /{$url}");
        exit;
    }

    // Optionally, define a middleware function to handle before controller actions
    public function middleware($middleware)
    {
        // You can call middleware functions here if you have any
        // For example, for authentication, logging, etc.
        if (is_callable($middleware)) {
            return $middleware();
        }
    }

    protected function prepareApiResponse($data = [], $statusCode = 200, $status = true, $message = 'Operation successful')
    {
        return ['code' => $statusCode, 'status' => $status, 'message' => $message, 'data' => $data];
    }

}
