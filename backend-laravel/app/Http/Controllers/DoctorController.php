<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DoctorController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Register a new doctor
     * POST /doctors
     */
    public function register(Request $request)
    {
        try {
            // Validation
            $data = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'specialization' => 'required|string|max:255',
                'license_number' => 'required|string|unique:doctors,license_number',
                'telephone' => 'nullable|string|max:20',
            ]);

            // Create doctor
            $doctor = $this->doctorService->registerDoctor($data);

            return response()->json([
                'message' => 'Doctor registered successfully',
                'data' => [
                    'doctor_id' => $doctor->id,
                    'nom' => $doctor->user->nom,
                    'prenom' => $doctor->user->prenom,
                    'email' => $doctor->user->email,
                    'specialization' => $doctor->specialization,
                    'license_number' => $doctor->license_number,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all doctors
     * GET /doctors
     */
    public function index()
    {
        try {
            $doctors = $this->doctorService->getAllDoctors();

            return response()->json([
                'message' => 'Doctors retrieved successfully',
                'count' => $doctors->count(),
                'data' => $doctors->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'nom' => $doctor->user->nom,
                        'prenom' => $doctor->user->prenom,
                        'email' => $doctor->user->email,
                        'specialization' => $doctor->specialization,
                        'license_number' => $doctor->license_number,
                        'telephone' => $doctor->user->telephone,
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve doctors',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor by ID with all information
     * GET /doctors/{id}
     */
    public function show($id)
    {
        try {
            $doctor = $this->doctorService->getDoctorById($id);

            return response()->json([
                'message' => 'Doctor retrieved successfully',
                'data' => [
                    'id' => $doctor->id,
                    'nom' => $doctor->user->nom,
                    'prenom' => $doctor->user->prenom,
                    'email' => $doctor->user->email,
                    'specialization' => $doctor->specialization,
                    'license_number' => $doctor->license_number,
                    'telephone' => $doctor->user->telephone,
                    'role' => $doctor->user->role,
                    'created_at' => $doctor->created_at,
                    'appointments_count' => $doctor->appointments->count(),
                    'consultations_count' => $doctor->consultations->count(),
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Doctor not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve doctor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update doctor information
     * PUT /doctors/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation
            $data = $request->validate([
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'specialization' => 'nullable|string|max:255',
                'license_number' => 'nullable|string|unique:doctors,license_number,' . $id,
                'telephone' => 'nullable|string|max:20',
            ]);

            $doctor = $this->doctorService->updateDoctor($id, $data);

            return response()->json([
                'message' => 'Doctor updated successfully',
                'data' => [
                    'id' => $doctor->id,
                    'nom' => $doctor->user->nom,
                    'prenom' => $doctor->user->prenom,
                    'email' => $doctor->user->email,
                    'specialization' => $doctor->specialization,
                    'license_number' => $doctor->license_number,
                    'telephone' => $doctor->user->telephone,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Doctor not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete doctor
     * DELETE /doctors/{id}
     */
    public function destroy($id)
    {
        try {
            $this->doctorService->deleteDoctor($id);

            return response()->json([
                'message' => 'Doctor deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Doctor not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Delete failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
