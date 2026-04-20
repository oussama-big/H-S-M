<?php

use Illuminate\Http\Request;
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

// ==================== PUBLIC ROUTES ====================

/**
 * Authentication Routes (Non protégées)
 * POST /api/register - Inscription
 * POST /api/login - Connexion
 * GET /api/test-token - Token de test (TEMPORAIRE)
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test-token', [AuthController::class, 'testToken']); // TEMPORAIRE
Route::post('/patients/register', [PatientController::class, 'register'])->name('patients.register');
Route::post('/doctors/register', [DoctorController::class, 'register'])->name('doctors.register');
Route::post('/admins/register', [AdminController::class, 'register'])->name('admins.register');
Route::post('/secretaries/register', [SecretaryController::class, 'register'])->name('secretaries.register');

// ==================== PROTECTED ROUTES ====================
// Toutes les routes ci-dessous nécessitent une authentification via JWT/Sanctum

Route::middleware('auth:sanctum')->group(function () {
    
    // ==================== AUTH ====================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ==================== PATIENTS ====================
    /**
     * Patient Management
     */
    Route::apiResource('patients', PatientController::class);
    Route::get('/patients/search/patients', [PatientController::class, 'search'])->name('patients.search');
    Route::get('/patients/{id}/appointments', [PatientController::class, 'getAppointments'])->name('patients.appointments');
    Route::get('/patients/{id}/consultations', [PatientController::class, 'getConsultations'])->name('patients.consultations');
    Route::get('/patients/{id}/medical-info', [PatientController::class, 'getMedicalInfo'])->name('patients.medical-info');

    // ==================== DOCTORS ====================
    /**
     * Doctor Management
     */
    Route::apiResource('doctors', DoctorController::class);

    // ==================== ADMINS ====================
    /**
     * Admin Management
     */
    Route::apiResource('admins', AdminController::class);

    // ==================== SECRETARIES ====================
    /**
     * Secretary Management
     */
    Route::apiResource('secretaries', SecretaryController::class);

    // ==================== APPOINTMENTS ====================
    /**
     * Appointment Management
     */
    Route::apiResource('appointments', AppointmentController::class);
    Route::get('/appointments/doctor/{doctor_id}', [AppointmentController::class, 'getByDoctor'])->name('appointments.doctor');
    Route::get('/appointments/patient/{patient_id}', [AppointmentController::class, 'getByPatient'])->name('appointments.patient');

    // ==================== CONSULTATIONS ====================
    /**
     * Consultation Management
     */
    Route::apiResource('consultations', ConsultationController::class);
    Route::get('/consultations/patient/{patient_id}', [ConsultationController::class, 'getByPatient'])->name('consultations.patient');

    // ==================== ORDONNANCES ====================
    /**
     * Ordonnance (Prescription) Management
     */
    Route::apiResource('ordonnances', OrdonnanceController::class);
    Route::get('/consultations/{consultation_id}/ordonnances', [OrdonnanceController::class, 'getByConsultation'])->name('ordonnances.consultation');

    // ==================== DOSSIER MEDICAL ====================
    /**
     * Medical Record Management
     */
    Route::apiResource('dossiers-medicaux', DossierMedicalController::class);
    Route::get('/dossiers-medicaux/patient/{patient_id}', [DossierMedicalController::class, 'getByPatient'])->name('dossiers.patient');
    Route::get('/dossiers-medicaux/patient/{patient_id}/summary', [DossierMedicalController::class, 'getSummary'])->name('dossiers.summary');

    // ==================== CABINETS ====================
    /**
     * Cabinet Management
     */
    Route::apiResource('cabinets', CabinetController::class);
    Route::get('/cabinets/{id}/doctors', [CabinetController::class, 'getDoctors'])->name('cabinets.doctors');
    Route::get('/cabinets/search/name', [CabinetController::class, 'searchByName'])->name('cabinets.search');

    // ==================== NOTIFICATIONS ====================
    /**
     * Notification Management
     */
    Route::apiResource('notifications', NotificationController::class);
    Route::get('/notifications/user/{user_id}', [NotificationController::class, 'getUserNotifications'])->name('notifications.user');
    Route::get('/notifications/user/{user_id}/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::get('/notifications/user/{user_id}/stats', [NotificationController::class, 'getStats'])->name('notifications.stats');
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::put('/notifications/user/{user_id}/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/user/{user_id}/clear', [NotificationController::class, 'clearUserNotifications'])->name('notifications.clear');
});
