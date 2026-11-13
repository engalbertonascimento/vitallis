<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;


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
/*
|--------------------------------------------------------------------------
| Unidades
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:unit.view',
])->group(function () {

    Route::get('/unidades', [UnitController::class, 'index'])
        ->name('units.index');

});

Route::middleware([
    'auth',
    'permission:unit.manage',
])->group(function () {

    Route::get('/unidades/nova', [UnitController::class, 'create'])
        ->name('units.create');

    Route::post('/unidades', [UnitController::class, 'store'])
        ->name('units.store');

    Route::get('/unidades/{unit}/editar', [UnitController::class, 'edit'])
        ->name('units.edit');

    Route::put('/unidades/{unit}', [UnitController::class, 'update'])
        ->name('units.update');

    Route::patch('/unidades/{unit}/status', [UnitController::class, 'toggleStatus'])
        ->name('units.toggle-status');

});
/*
|--------------------------------------------------------------------------
| Usuários
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:users.view',
])->group(function () {

    Route::get('/usuarios', [UserController::class, 'index'])
        ->name('users.index');

});

Route::middleware([
    'auth',
    'permission:users.create',
])->group(function () {

    Route::get('/usuarios/novo', [UserController::class, 'create'])
        ->name('users.create');

    Route::post('/usuarios', [UserController::class, 'store'])
        ->name('users.store');

});

Route::middleware([
    'auth',
    'permission:users.update',
])->group(function () {

    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])
        ->name('users.edit');

    Route::put('/usuarios/{user}', [UserController::class, 'update'])
        ->name('users.update');

    Route::patch('/usuarios/{user}/status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');

});
/*
|--------------------------------------------------------------------------
| Pacientes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:patients.view',
])->group(function () {

    Route::get('/pacientes', [PatientController::class, 'index'])
        ->name('patients.index');

    Route::get('/pacientes/{patient}', [PatientController::class, 'show'])
        ->name('patients.show');
});

Route::middleware([
    'auth',
    'permission:patients.create',
])->group(function () {

    Route::get('/pacientes/novo', [PatientController::class, 'create'])
        ->name('patients.create');

    Route::post('/pacientes', [PatientController::class, 'store'])
        ->name('patients.store');
});

Route::middleware([
    'auth',
    'permission:patients.update',
])->group(function () {

    Route::get('/pacientes/{patient}/editar', [PatientController::class, 'edit'])
        ->name('patients.edit');

    Route::put('/pacientes/{patient}', [PatientController::class, 'update'])
        ->name('patients.update');

    Route::patch('/pacientes/{patient}/status', [PatientController::class, 'toggleStatus'])
        ->name('patients.toggle-status');
});

/*
| Cadastro
*/
Route::middleware([
    'auth',
    'permission:patients.create',
])->group(function () {

    Route::get('/pacientes/novo', [PatientController::class, 'create'])
        ->name('patients.create');

    Route::post('/pacientes', [PatientController::class, 'store'])
        ->name('patients.store');
});


/*
| Listagem e visualização
*/
Route::middleware([
    'auth',
    'permission:patients.view',
])->group(function () {

    Route::get('/pacientes', [PatientController::class, 'index'])
        ->name('patients.index');

    Route::get('/pacientes/{patient}', [PatientController::class, 'show'])
        ->whereNumber('patient')
        ->name('patients.show');
});


/*
| Edição
*/
Route::middleware([
    'auth',
    'permission:patients.update',
])->group(function () {

    Route::get('/pacientes/{patient}/editar', [PatientController::class, 'edit'])
        ->whereNumber('patient')
        ->name('patients.edit');

    Route::put('/pacientes/{patient}', [PatientController::class, 'update'])
        ->whereNumber('patient')
        ->name('patients.update');

    Route::patch('/pacientes/{patient}/status', [PatientController::class, 'toggleStatus'])
        ->whereNumber('patient')
        ->name('patients.toggle-status');
});