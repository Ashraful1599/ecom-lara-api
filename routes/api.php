<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\TagController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);;
Route::get('/login', [AuthController::class, 'unauthenticated'])->name('login');

Route::middleware('auth:sanctum')->group(function () {

    // Product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/bulkDelete', [ProductController::class, 'bulkDeleteProducts']);

    // Category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']); // Added show route
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Tag routes
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::get('/tags/{id}', [TagController::class, 'show']); // Added show route
    Route::put('/tags/{id}', [TagController::class, 'update']);
    Route::delete('/tags/{id}', [TagController::class, 'destroy']);

    // Attribute routes
    Route::get('/attributes', [AttributeController::class, 'index']);
    Route::post('/attributes', [AttributeController::class, 'store']);
    Route::get('/attributes/{id}', [AttributeController::class, 'show']); // Added show route
    Route::put('/attributes/{id}', [AttributeController::class, 'update']);
    Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);

    // Attribute Value routes
    Route::get('/attributes/{attributeId}/values', [AttributeValueController::class, 'index']);
    Route::post('/attributes/{attributeId}/values', [AttributeValueController::class, 'store']);
    Route::get('/attributes/{attributeId}/values/{valueId}', [AttributeValueController::class, 'show']); // Added show route
    Route::put('/attributes/{attributeId}/values/{valueId}', [AttributeValueController::class, 'update']);
    Route::delete('/attributes/{attributeId}/values/{valueId}', [AttributeValueController::class, 'destroy']);


    // user routes
    Route::get('/users', [AuthController::class, 'index']);
    Route::get('/users/{userId}', [AuthController::class, 'show']); // Added show route
    Route::put('/users/{userId}', [AuthController::class, 'update']);
    Route::delete('/users/{userId}', [AuthController::class, 'destroy']);
    Route::post('/users/bulkDelete', [AuthController::class, 'bulkDeleteUsers']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);

});
