<?php

namespace App\Controllers;

use App\Assembly\Core\Controller;
use App\Assembly\Core\Request;
use App\Assembly\Core\Response;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $user = User::find(1);
        $response = $this->prepareApiResponse($user);
        Response::json($response);
    }


    public function store(Request $request)
    {
        $user = User::find(1);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = md5($request->password);
        $user->save();
        $response = $this->prepareApiResponse($user);
        Response::json($response);
    }


    public function show(Request $request)
    {
        $user = User::find($request->id);
        $response = $this->prepareApiResponse(200, true, 'User fetched successfully', $user);
        Response::json($response);
    }

    public function update(Request $request)
    {
        $user = User::find($request->id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
    }
}