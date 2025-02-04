<?php

namespace App\Controllers;

use App\Assembly\Core\Controller;
use App\Assembly\Core\Request;
use App\Assembly\Core\Response;

class HomeController extends Controller
{
    public function index()
    {
        Response::json(['message' => 'Hello World']);
    }
    public function JsonRequest(Request $request)
    {
        return $request->json();
    }

    public function XmlRequest(Request $request)  
    {
        return $request->xml();
    }

    public function JsonResponse()
    {
        Response::json(['message' => 'Hello World']);
    }

    public function XmlResponse()
    {
        Response::xml(['message' => 'Hello World']);
    }
}