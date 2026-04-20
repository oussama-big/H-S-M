<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\OrdonnanceController;
use App\Http\Controllers\DossierMedicalController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;

// --- PAGES PUBLIQUES ---
Route::get('/', function () { return view('welcome'); });

// --- AUTHENTIFICATION ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- ZONE CONNECTÉE (COMMUNE) ---
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications (Accessibles par tous les rôles connectés)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::put('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clearAll');
    });

    // --- ACCÈS ADMINISTRATEUR ---
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::resource('admins', AdminController::class);
        Route::resource('secretaries', SecretaryController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('cabinets', CabinetController::class);
    });

    // --- ACCÈS MÉDECIN ---
    Route::middleware(['role:MEDECIN'])->group(function () {
        Route::resource('consultations', ConsultationController::class);
        Route::resource('ordonnances', OrdonnanceController::class);
        Route::resource('patients', PatientController::class); // Le médecin gère ses patients
        Route::resource('dossiers', DossierMedicalController::class)->only(['show', 'edit', 'update']);
    });

    // --- ACCÈS SECRÉTAIRE ---
    Route::middleware(['role:SECRETAIRE'])->group(function () {
        Route::resource('appointments', AppointmentController::class); // La secrétaire gère les RDV
        Route::get('/search-patients', [PatientController::class, 'index'])->name('patients.search');
    });

    // --- ACCÈS PATIENT ---
    Route::middleware(['role:PATIENT'])->group(function () {
        Route::get('/mon-dossier', [DossierMedicalController::class, 'myRecord'])->name('dossier.mine');
        Route::get('/mes-rendez-vous', [AppointmentController::class, 'index'])->name('appointments.my_list');
        // Un patient peut seulement "créer" un rendez-vous
        Route::get('/prendre-rdv', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/save-rdv', [AppointmentController::class, 'store'])->name('appointments.store');
    });
});