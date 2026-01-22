<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoahFaceController;

Route::get('/noahface/users', [NoahFaceController::class, 'index']);
Route::get('/noahface/users/{guid}', [NoahFaceController::class, 'show']);


Route::post('/noahface/event-notification', [NoahFaceController::class, 'receiveEvent']);