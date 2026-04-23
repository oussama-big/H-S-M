<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DossierMedicalController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrdonnanceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SecretaryController;
use Illuminate\Support\Facades\Route;

// --- FRONTEND INTEGRE ---
Route::controller(FrontendPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/a-propos', 'about')->name('about');
    Route::get('/services', 'services')->name('services');
    Route::get('/equipe', 'equipe')->name('equipe');
    Route::get('/temoignages', 'temoignages')->name('temoignages');
    Route::get('/rendez-vous', 'contact')->name('contact');
    Route::post('/rendez-vous', 'submitContact')->name('contact.submit');
    Route::get('/connexion', 'auth')->name('login');
    Route::get('/inscription', 'auth')->name('register');
    Route::get('/auth', 'auth')->name('auth');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/admin/dashboard', 'adminDashboard')->name('admin.dashboard');
    Route::get('/profil', 'profile')->name('profile');
});

Route::redirect('/login', '/connexion');
Route::redirect('/register', '/inscription');

Route::prefix('doctor')->name('doctor.')->controller(FrontendPageController::class)->group(function () {
    Route::get('/dashboard', 'doctorDashboard')->defaults('initialView', 'dashboard')->name('dashboard');
    Route::get('/consultations', 'doctorDashboard')->defaults('initialView', 'consultations')->name('consultations');
    Route::get('/patients', 'doctorDashboard')->defaults('initialView', 'patients')->name('patients');
    Route::get('/schedule', 'doctorDashboard')->defaults('initialView', 'schedule')->name('schedule');
});

Route::prefix('patient')->name('patient.')->controller(FrontendPageController::class)->group(function () {
    Route::get('/dashboard', 'patientDashboard')->defaults('initialView', 'dashboard')->name('dashboard');
    Route::get('/rendez-vous', 'patientDashboard')->defaults('initialView', 'appointments')->name('appointments');
    Route::get('/dossier-medical', 'patientDashboard')->defaults('initialView', 'records')->name('records');
    Route::get('/consultations', 'patientDashboard')->defaults('initialView', 'consultations')->name('consultations');
    Route::get('/ordonnances', 'patientDashboard')->defaults('initialView', 'ordonnances')->name('ordonnances');
});

Route::prefix('secretary')->name('secretary.')->controller(FrontendPageController::class)->group(function () {
    Route::get('/dashboard', 'secretaryDashboard')->defaults('initialView', 'dashboard')->name('dashboard');
    Route::get('/patients', 'secretaryDashboard')->defaults('initialView', 'patients')->name('patients');
    Route::get('/appointments', 'secretaryDashboard')->defaults('initialView', 'appointments')->name('appointments');
    Route::get('/queues', 'secretaryDashboard')->defaults('initialView', 'queues')->name('queues');
});

// --- ENDPOINTS WEB LEGACY / BACKOFFICE ---
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('session.login');
    Route::post('/register', [AuthController::class, 'register'])->name('session.register');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- ZONE CONNECTEE (COMMUNE) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/backoffice/dashboard', [DashboardController::class, 'index'])->name('backoffice.dashboard');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::put('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clearAll');
    });

    Route::middleware(['role:ADMIN'])->group(function () {
        Route::resource('admins', AdminController::class);
        Route::resource('secretaries', SecretaryController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('cabinets', CabinetController::class);
    });

    Route::middleware(['role:MEDECIN'])->group(function () {
        Route::resource('consultations', ConsultationController::class);
        Route::resource('ordonnances', OrdonnanceController::class);
        Route::resource('patients', PatientController::class);
        Route::resource('dossiers', DossierMedicalController::class)->only(['show', 'edit', 'update']);
    });

    Route::middleware(['role:SECRETAIRE'])->group(function () {
        Route::resource('appointments', AppointmentController::class);
        Route::get('/search-patients', [PatientController::class, 'index'])->name('patients.search');
    });

    Route::middleware(['role:PATIENT'])->group(function () {
        Route::get('/mon-dossier', [DossierMedicalController::class, 'myRecord'])->name('dossier.mine');
        Route::get('/mes-rendez-vous', [AppointmentController::class, 'index'])->name('appointments.my_list');
        Route::get('/prendre-rdv', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/save-rdv', [AppointmentController::class, 'store'])->name('appointments.store');
    });
});

use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {
    $data = ['name' => 'Oussama'];
    
    Mail::raw('Félicitations ! Ton système d\'email pour le Cabinet Médical fonctionne.', function ($message) {
        $message->to('oussamabizg213@gmail.com') // Envoie-le à toi-même pour tester
                ->subject('Test Notification Cabinet Médical');
    });

    return "L'email a été envoyé ! Vérifie ta boîte de réception (et tes spams).";
});

Route::get('/ordonnances/{id}/pdf', [OrdonnanceController::class, 'downloadPDF'])->name('ordonnances.pdf');