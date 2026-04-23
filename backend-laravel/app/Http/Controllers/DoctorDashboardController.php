<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\DoctorDashboardService;
use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{
    public function __construct(private readonly DoctorDashboardService $doctorDashboardService)
    {
    }

    public function data(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->doctorDashboardService->getDashboardData($request->user()),
        ]);
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ], [
            'patient_id.required' => 'Veuillez selectionner un patient avant de creer le rendez-vous.',
            'patient_id.exists' => 'Le patient selectionne est introuvable.',
            'appointment_date.required' => 'Veuillez choisir une date et une heure pour le rendez-vous.',
            'appointment_date.date' => 'Le format de la date du rendez-vous est invalide.',
            'appointment_date.after' => 'La date du rendez-vous doit etre dans le futur.',
            'reason.max' => 'Le motif du rendez-vous est trop long.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous ajoute avec succes.',
            'data' => $this->doctorDashboardService->createAppointment($request->user(), $data),
        ], 201);
    }

    public function findPatientByEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $patient = $this->doctorDashboardService->findPatientByEmail($request->user(), $data['email']);

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun patient ne correspond a cet email.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient,
        ]);
    }

    public function saveNotes(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'diagnosis' => 'nullable|string|max:2000',
            'symptoms' => 'nullable|string|max:2000',
            'treatment' => 'nullable|string|max:4000',
            'next_visit' => 'nullable|date',
            'ordonnance_details' => 'nullable|string|max:8000',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Les notes de consultation ont ete enregistrees.',
            'data' => $this->doctorDashboardService->saveConsultationNotes($request->user(), $appointment, $data),
        ]);
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'diagnosis' => 'nullable|string|max:2000',
            'symptoms' => 'nullable|string|max:2000',
            'treatment' => 'nullable|string|max:4000',
            'next_visit' => 'nullable|date',
            'ordonnance_details' => 'nullable|string|max:8000',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'La consultation a ete cloturee avec succes.',
            'data' => $this->doctorDashboardService->completeConsultation($request->user(), $appointment, $data),
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ], [
            'appointment_date.required' => 'Veuillez choisir une nouvelle date et heure pour ce rendez-vous.',
            'appointment_date.date' => 'Le format de la nouvelle date est invalide.',
            'appointment_date.after' => 'La nouvelle date doit etre dans le futur.',
            'reason.max' => 'Le motif du rendez-vous est trop long.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le rendez-vous a ete reprogramme avec succes.',
            'data' => $this->doctorDashboardService->rescheduleAppointment($request->user(), $appointment, $data),
        ]);
    }
}
