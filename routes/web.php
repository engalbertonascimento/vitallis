<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkController;


/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Página inicial
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Área autenticada
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:dashboard.view',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');
    
    
/*
|--------------------------------------------------------------------------
| Redes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:network.view',
])->group(function () {

    Route::get('/redes', [NetworkController::class, 'index'])
        ->name('networks.index');

});

Route::middleware([
    'auth',
    'permission:network.manage',
])->group(function () {

    Route::get('/redes/nova', [NetworkController::class, 'create'])
        ->name('networks.create');

    Route::post('/redes', [NetworkController::class, 'store'])
        ->name('networks.store');

    Route::get('/redes/{network}/editar', [NetworkController::class, 'edit'])
        ->name('networks.edit');

    Route::put('/redes/{network}', [NetworkController::class, 'update'])
        ->name('networks.update');

    Route::patch('/redes/{network}/status', [NetworkController::class, 'toggleStatus'])
        ->name('networks.toggle-status');

});