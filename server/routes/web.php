<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
Route::resource('articles', ArticleController::class)->except(['index']);
