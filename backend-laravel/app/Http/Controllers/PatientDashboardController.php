<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\PatientDashboardService;
use Illuminate\Http\Request;

class PatientDashboardController extends Controller
{
    public function __construct(private readonly PatientDashboardService $patientDashboardService)
    {
    }

    public function data(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->patientDashboardService->getDashboardData($request->user()),
        ]);
    }

    public function availabilityCalendar(Request $request, int $doctor)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'ignore_appointment_id' => ['nullable', 'integer'],
        ], [
            'month.required' => 'Veuillez choisir un mois pour afficher la disponibilite.',
            'month.date_format' => 'Le format du mois est invalide.',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->patientDashboardService->getAvailabilityCalendar(
                $request->user(),
                $doctor,
                $data['month'],
                isset($data['ignore_appointment_id']) ? (int) $data['ignore_appointment_id'] : null
            ),
        ]);
    }

    public function availableSlots(Request $request, int $doctor)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'ignore_appointment_id' => ['nullable', 'integer'],
        ], [
            'date.required' => 'Veuillez choisir une date pour afficher les creneaux disponibles.',
            'date.date' => 'Le format de la date est invalide.',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->patientDashboardService->getAvailableSlots(
                $request->user(),
                $doctor,
                $data['date'],
                isset($data['ignore_appointment_id']) ? (int) $data['ignore_appointment_id'] : null
            ),
        ]);
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ], [
            'doctor_id.required' => 'Veuillez choisir un medecin.',
            'doctor_id.exists' => 'Le medecin selectionne est introuvable.',
            'appointment_date.required' => 'Veuillez choisir un creneau horaire.',
            'appointment_date.date' => 'Le format du creneau selectionne est invalide.',
            'appointment_date.after' => 'Le rendez-vous doit etre programme dans le futur.',
            'reason.max' => 'Le motif du rendez-vous est trop long.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous cree avec succes.',
            'data' => $this->patientDashboardService->createAppointment($request->user(), $data),
        ], 201);
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ], [
            'doctor_id.required' => 'Veuillez choisir un medecin.',
            'doctor_id.exists' => 'Le medecin selectionne est introuvable.',
            'appointment_date.required' => 'Veuillez choisir un nouveau creneau horaire.',
            'appointment_date.date' => 'Le format du nouveau creneau est invalide.',
            'appointment_date.after' => 'Le rendez-vous doit etre programme dans le futur.',
            'reason.max' => 'Le motif du rendez-vous est trop long.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous modifie avec succes.',
            'data' => $this->patientDashboardService->updateAppointment($request->user(), $appointment, $data),
        ]);
    }
}
