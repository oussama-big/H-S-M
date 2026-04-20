<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class AppointmentController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Create a new appointment
     * POST /appointments
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $data = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:doctors,id',
                'appointment_date' => 'required|date|after:now',
                'reason' => 'nullable|string|max:500',
            ]);

            // Create appointment
            $appointment = $this->doctorService->createAppointment($data);

            return response()->json([
                'message' => 'Appointment created successfully',
                'data' => [
                    'id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'appointment_date' => $appointment->appointment_date,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'created_at' => $appointment->created_at,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create appointment',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all appointments for a doctor
     * GET /appointments/doctor/{doctor_id}
     */
    public function getByDoctor($doctorId)
    {
        try {
            $appointments = $this->doctorService->getAppointmentsByDoctor($doctorId);

            return response()->json([
                'message' => 'Doctor appointments retrieved successfully',
                'count' => $appointments->count(),
                'data' => $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'patient_name' => $appointment->patient->user->nom . ' ' . $appointment->patient->user->prenom,
                        'patient_id' => $appointment->patient_id,
                        'appointment_date' => $appointment->appointment_date,
                        'status' => $appointment->status,
                        'reason' => $appointment->reason,
                    ];
                })
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Doctor not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve appointments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all appointments for a patient
     * GET /appointments/patient/{patient_id}
     */
    public function getByPatient($patientId)
    {
        try {
            $appointments = $this->doctorService->getAppointmentsByPatient($patientId);

            return response()->json([
                'message' => 'Patient appointments retrieved successfully',
                'count' => $appointments->count(),
                'data' => $appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'doctor_name' => $appointment->doctor->user->nom . ' ' . $appointment->doctor->user->prenom,
                        'doctor_id' => $appointment->doctor_id,
                        'specialization' => $appointment->doctor->specialization,
                        'appointment_date' => $appointment->appointment_date,
                        'status' => $appointment->status,
                        'reason' => $appointment->reason,
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve appointments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appointment by ID
     * GET /appointments/{id}
     */
    public function show($id)
    {
        try {
            $appointment = $this->doctorService->getAppointmentById($id);

            return response()->json([
                'message' => 'Appointment retrieved successfully',
                'data' => [
                    'id' => $appointment->id,
                    'patient' => [
                        'id' => $appointment->patient->id,
                        'nom' => $appointment->patient->user->nom,
                        'prenom' => $appointment->patient->user->prenom,
                        'email' => $appointment->patient->user->email,
                    ],
                    'doctor' => [
                        'id' => $appointment->doctor->id,
                        'nom' => $appointment->doctor->user->nom,
                        'prenom' => $appointment->doctor->user->prenom,
                        'specialization' => $appointment->doctor->specialization,
                    ],
                    'appointment_date' => $appointment->appointment_date,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'consultation' => $appointment->consultation ? [
                        'id' => $appointment->consultation->id,
                        'date' => $appointment->consultation->date,
                        'observations' => $appointment->consultation->observations,
                    ] : null,
                    'created_at' => $appointment->created_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Appointment not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve appointment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update appointment
     * PUT /appointments/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation
            $data = $request->validate([
                'appointment_date' => 'nullable|date|after:now',
                'status' => 'nullable|in:PREVU,CONFIRME,COMPLETE,ANNULE',
                'reason' => 'nullable|string|max:500',
            ]);

            $appointment = $this->doctorService->updateAppointment($id, $data);

            return response()->json([
                'message' => 'Appointment updated successfully',
                'data' => [
                    'id' => $appointment->id,
                    'appointment_date' => $appointment->appointment_date,
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'updated_at' => $appointment->updated_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Appointment not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete appointment
     * DELETE /appointments/{id}
     */
    public function destroy($id)
    {
        try {
            $this->doctorService->deleteAppointment($id);

            return response()->json([
                'message' => 'Appointment deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Appointment not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Delete failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
