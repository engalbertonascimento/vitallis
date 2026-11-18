<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\UserContextController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ServiceLocationController;


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
/*
|--------------------------------------------------------------------------
| Contexto operacional
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/contexto',
        [UserContextController::class, 'index']
    )->name('context.index');

    Route::put(
        '/contexto',
        [UserContextController::class, 'update']
    )->name('context.update');

});
/*
|--------------------------------------------------------------------------
| Funções e Permissões
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:roles.view',
])->group(function () {

    Route::get(
        '/funcoes-permissoes',
        [RolePermissionController::class, 'index']
    )->name('roles.index');

    Route::get(
        '/funcoes-permissoes/{role}/editar',
        [RolePermissionController::class, 'edit']
    )
        ->whereNumber('role')
        ->name('roles.edit');
});


/*
|--------------------------------------------------------------------------
| Permissões específicas da Unidade
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:unit_permissions.manage',
])->group(function () {

    Route::put(
        '/funcoes-permissoes/{role}',
        [RolePermissionController::class, 'update']
    )
        ->whereNumber('role')
        ->name('roles.update-unit-permissions');
});


/*
|--------------------------------------------------------------------------
| Gerenciamento de Funções
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:roles.manage',
])->group(function () {

    Route::get(
        '/funcoes-permissoes/nova',
        [RolePermissionController::class, 'create']
    )->name('roles.create');

    Route::post(
        '/funcoes-permissoes',
        [RolePermissionController::class, 'store']
    )->name('roles.store');

    Route::put(
        '/funcoes-permissoes/{role}/padrao',
        [RolePermissionController::class, 'updateDefaults']
    )
        ->whereNumber('role')
        ->name('roles.update-defaults');
});
/*
|--------------------------------------------------------------------------
| Agenda - Visualização
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:appointments.view',
])->group(function () {

    Route::get(
        '/agenda',
        [AppointmentController::class, 'index']
    )->name('appointments.index');

});


/*
|--------------------------------------------------------------------------
| Agenda - Agendamentos
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:appointments.create',
])->group(function () {

    Route::get(
        '/agenda/novo',
        [AppointmentController::class, 'create']
    )->name('appointments.create');

    Route::post(
        '/agenda',
        [AppointmentController::class, 'store']
    )->name('appointments.store');

});
/*
|--------------------------------------------------------------------------
| Grades de Agenda
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:schedules.view',
])->group(function () {

    Route::get(
        '/agenda/grades',
        [ScheduleController::class, 'index']
    )->name('schedules.index');

});


Route::middleware([
    'auth',
    'permission:schedules.create',
])->group(function () {

    Route::get(
        '/agenda/grades/nova',
        [ScheduleController::class, 'create']
    )->name('schedules.create');

    Route::post(
        '/agenda/grades',
        [ScheduleController::class, 'store']
    )->name('schedules.store');

});


Route::middleware([
    'auth',
    'permission:schedules.update',
])->group(function () {

    Route::get(
        '/agenda/grades/{schedule}/editar',
        [ScheduleController::class, 'edit']
    )
        ->whereNumber('schedule')
        ->name('schedules.edit');

    Route::put(
        '/agenda/grades/{schedule}',
        [ScheduleController::class, 'update']
    )
        ->whereNumber('schedule')
        ->name('schedules.update');

});
/*
|--------------------------------------------------------------------------
| Especialidades
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:specialties.view',
])->group(function () {

    Route::get(
        '/especialidades',
        [SpecialtyController::class, 'index']
    )->name('specialties.index');
});

Route::middleware([
    'auth',
    'permission:specialties.manage',
])->group(function () {

    Route::get(
        '/especialidades/nova',
        [SpecialtyController::class, 'create']
    )->name('specialties.create');

    Route::post(
        '/especialidades',
        [SpecialtyController::class, 'store']
    )->name('specialties.store');

    Route::get(
        '/especialidades/{specialty}/editar',
        [SpecialtyController::class, 'edit']
    )
        ->whereNumber('specialty')
        ->name('specialties.edit');

    Route::put(
        '/especialidades/{specialty}',
        [SpecialtyController::class, 'update']
    )
        ->whereNumber('specialty')
        ->name('specialties.update');

    Route::patch(
        '/especialidades/{specialty}/status',
        [SpecialtyController::class, 'toggleStatus']
    )
        ->whereNumber('specialty')
        ->name('specialties.toggle-status');
});
/*
|--------------------------------------------------------------------------
| Locais de Atendimento
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:service_locations.view',
])->group(function () {

    Route::get(
        '/locais-atendimento',
        [ServiceLocationController::class, 'index']
    )->name('service-locations.index');

});


Route::middleware([
    'auth',
    'permission:service_locations.manage',
])->group(function () {

    Route::get(
        '/locais-atendimento/novo',
        [ServiceLocationController::class, 'create']
    )->name('service-locations.create');

    Route::post(
        '/locais-atendimento',
        [ServiceLocationController::class, 'store']
    )->name('service-locations.store');

    Route::get(
        '/locais-atendimento/{serviceLocation}/editar',
        [ServiceLocationController::class, 'edit']
    )
        ->whereNumber('serviceLocation')
        ->name('service-locations.edit');

    Route::put(
        '/locais-atendimento/{serviceLocation}',
        [ServiceLocationController::class, 'update']
    )
        ->whereNumber('serviceLocation')
        ->name('service-locations.update');

    Route::patch(
        '/locais-atendimento/{serviceLocation}/status',
        [ServiceLocationController::class, 'toggleStatus']
    )
        ->whereNumber('serviceLocation')
        ->name('service-locations.toggle-status');

});
/*
|--------------------------------------------------------------------------
| Demais Cadastros
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get(
    '/cadastros',
    function () {
        $user = auth()->user();

        if (
            ! $user->hasPermission('specialties.view')
            && ! $user->hasPermission('service_locations.view')
        ) {
            abort(403);
        }

        return view('registries.index');
    }
)->name('registries.index');
/*
|--------------------------------------------------------------------------
| Agenda - Check-in
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'permission:appointments.checkin',
])->group(function () {

    Route::patch(
        '/agenda/{appointment}/checkin',
        [AppointmentController::class, 'checkin']
    )->name('appointments.checkin');

});

Route::middleware([
    'auth',
    'permission:appointments.no_show',
])->group(function () {

    Route::patch(
        '/agenda/{appointment}/no-show',
        [AppointmentController::class, 'noShow']
    )->name('appointments.no_show');

});

Route::middleware([
    'auth',
    'permission:appointments.confirm',
])->patch(
    '/agenda/{appointment}/confirmar',
    [AppointmentController::class, 'confirm']
)->name('appointments.confirm');

Route::middleware([
    'auth',
    'permission:appointments.reschedule',
])->get(
    '/agenda/{appointment}/reagendar',
    [AppointmentController::class, 'reschedule']
)->name('appointments.reschedule');

Route::middleware([
    'auth',
    'permission:appointments.cancel',
])->patch(
    '/agenda/{appointment}/cancelar',
    [AppointmentController::class, 'cancel']
)->name('appointments.cancel');

Route::middleware([
    'auth',
    'permission:appointments.reschedule',
])->patch(
    '/agenda/{appointment}/reagendar',
    [AppointmentController::class, 'updateSchedule']
)->name('appointments.reschedule.update');

/*
|--------------------------------------------------------------------------
| Agenda - Iniciar / Finalizar Atendimento
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth',
    'permission:appointments.start',
])->patch(
    '/agenda/{appointment}/iniciar',
    [AppointmentController::class, 'start']
)->name('appointments.start');

Route::middleware([
    'auth',
    'permission:appointments.complete',
])->patch(
    '/agenda/{appointment}/finalizar',
    [AppointmentController::class, 'complete']
)->name('appointments.complete');