<?php

use App\Assembly\Core\Route;
use App\Controllers\{HomeController, UserController};

Route::get('/', [HomeController::class, 'index']);
Route::get('/jsonResponse', [HomeController::class, 'JsonResponse']);
Route::get('/xmlResponse', [HomeController::class, 'XmlResponse']);
Route::get('/jsonRequest', [HomeController::class, 'JsonRequest']);
Route::get('/xmlRequest', [HomeController::class, 'XmlRequest']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
