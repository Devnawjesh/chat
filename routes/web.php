<?php

use Illuminate\Support\Facades\Route;
use App\Events\TestBroadcastEvent;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\VisitorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

// use Spatie\Permission\Middlewares\RoleMiddleware;
// use Spatie\Permission\Middlewares\PermissionMiddleware;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-broadcast', function () {
    broadcast(new TestBroadcastEvent('This is a test message!'));
    return 'Broadcast event fired!';
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/agent', fn() => view('agent'))->name('agent.dashboard');
    
});

Route::get('/visitor', fn() => view('chat'))->name('visitor.dashboard');


// Public chat routes
Route::post('/chat/initiate', [ChatController::class, 'initiate'])->name('chat.initiate');
Route::post('/chat/{session}/message', [ChatController::class, 'storeMessage'])->name('chat.message.store');

// Agent routes
Route::middleware(['auth'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/chat', [AgentController::class, 'index'])->name('chat.index');
    Route::get('/chat/{chat}', [AgentController::class, 'show'])->name('chat.show');
    Route::post('/chat/{session}/message', [AgentController::class, 'storeMessage'])->name('chat.message.store');
    Route::post('/chat/status', [AgentController::class, 'updateStatus'])->name('chat.status.update');
    Route::patch('/chat/{session}/end', [AgentController::class, 'endSession'])->name('chat.end');
    Route::post('/send-message', [AgentController::class, 'sendMessageAsAgent'])->name('send-message');
});
// Route::post('/broadcasting/auth', function (Request $request) {
//     return Broadcast::auth($request);
// });

// Visitor routes
Route::middleware('web')->group(function () {
Route::post('/visitor/start-chat', [VisitorController::class, 'startChat'])->name('visitor.start-chat');
Route::post('/visitor/send-message', [VisitorController::class, 'sendMessage'])->name('visitor.send-message');
});
// Agent routes
// Route::middleware('auth')->group(function () {
//     Route::get('/agent/chat', [ChatController::class, 'agentChat'])->name('agent.chat');
//     Route::post('/agent/send-message', [ChatController::class, 'sendMessage'])->name('agent.send-message');
// });


// Add this at the bottom of your web.php
Broadcast::routes(['middleware' => 'web']);
