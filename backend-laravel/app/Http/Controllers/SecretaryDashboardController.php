<?php

namespace App\Http\Controllers;

use App\Services\SecretaryDashboardService;
use Illuminate\Http\Request;

class SecretaryDashboardController extends Controller
{
    public function __construct(private readonly SecretaryDashboardService $secretaryDashboardService)
    {
    }

    public function data(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->secretaryDashboardService->getDashboardData($request->user()),
        ]);
    }

    public function availableSlots(Request $request, int $doctor)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
        ], [
            'date.required' => 'Veuillez choisir une date pour afficher les creneaux disponibles.',
            'date.date' => 'Le format de la date est invalide.',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->secretaryDashboardService->getAvailableSlots($request->user(), $doctor, $data['date']),
        ]);
    }

    public function storePatient(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:M,F,Autre',
            'blood_type' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'Cet email est deja utilise.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'gender.required' => 'Le genre est obligatoire.',
            'telephone.required' => 'Le telephone est obligatoire.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient enregistre avec succes.',
            'data' => $this->secretaryDashboardService->createPatient($request->user(), $data),
        ], 201);
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ], [
            'patient_id.required' => 'Veuillez choisir un patient.',
            'patient_id.exists' => 'Le patient selectionne est introuvable.',
            'doctor_id.required' => 'Veuillez choisir un medecin.',
            'doctor_id.exists' => 'Le medecin selectionne est introuvable.',
            'appointment_date.required' => 'Veuillez choisir un creneau horaire.',
            'appointment_date.date' => 'Le format du creneau selectionne est invalide.',
            'appointment_date.after' => 'Le rendez-vous doit etre programme dans le futur.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous cree avec succes.',
            'data' => $this->secretaryDashboardService->createAppointment($request->user(), $data),
        ], 201);
    }
}
