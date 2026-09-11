<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
Route::get('/lessons/{lesson}/editor', [LessonController::class, 'editor'])->name('lessons.editor');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::post('/lessons/{lesson}/mark-complete', [LessonController::class, 'markComplete'])
        ->name('lessons.mark-complete');

    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/{project}/react', [ProjectController::class, 'react'])->name('projects.react');
    Route::post('/projects/{project}/comments', [ProjectController::class, 'storeComment'])
        ->name('projects.comments.store');
});
