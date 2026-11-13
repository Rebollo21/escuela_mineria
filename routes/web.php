<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalisisController;

Route::get('/', [AnalisisController::class, 'index'])->name('dashboard');