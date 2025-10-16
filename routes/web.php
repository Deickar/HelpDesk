<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 📌 Acceso común a todos los roles autenticados
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 📌 ADMIN: Mantenimiento y gestión
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('faqs', FaqController::class);
    Route::resource('users', UserController::class);

    Route::get('/tickets/gestion', [TicketController::class, 'gestion'])->name('tickets.gestion');
    Route::get('/tickets/gestion/{id}', [TicketController::class, 'gestiondatos'])->name('tickets.gestiondatos');
    Route::post('/tickets/actualizar/{id}', [TicketController::class, 'actualizarGestion'])->name('tickets.actualizarGestion');

    Route::get('/tickets/conformidad', [TicketController::class, 'conformidad'])->name('tickets.conformidad');
    Route::post('/tickets/{id}/marcar-resuelto', [TicketController::class, 'marcarResuelto'])->name('tickets.marcarResuelto');

    Route::post('/tickets/{id}/cancelar', [TicketController::class, 'cancelarticket'])->name('tickets.cancelar');
});

// 📌 CLIENT: Crear y ver sus propios tickets
Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

});

// 📌 AGENT: Tickets asignados
Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/tickets/asignados', [TicketController::class, 'asignados'])->name('tickets.asignados');
});

// 📌 TODOS los roles autenticados (cliente, agente, admin): respuestas y visualización
Route::middleware(['auth'])->group(function () {
    Route::get('/tickets/{id}/detalle', [TicketController::class, 'show'])->name('tickets.detalle');
    Route::post('/tickets/responder', [TicketController::class, 'responder'])->name('tickets.responder');

    Route::post('/tickets/{ticket}/cerrar', [TicketController::class, 'cerrar'])->name('tickets.cerrar');
    Route::post('/tickets/{ticket}/reabrir', [TicketController::class, 'reabrir'])->name('tickets.reabrir');

    Route::get('/tickets/{id}/pdf', [TicketController::class, 'generarPDF'])->name('tickets.pdf');
});

Route::get('/dashboard', [DashboardController::class, 'dashboardRouter'])
    ->middleware(['auth'])
    ->name('dashboard');

require __DIR__.'/auth.php';
