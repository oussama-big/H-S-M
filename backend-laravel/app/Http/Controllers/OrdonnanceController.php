<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class OrdonnanceController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Create a new ordonnance (prescription)
     * POST /ordonnances
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $data = $request->validate([
                'consultation_id' => 'required|exists:consultations,id',
                'details' => 'required|string|max:2000',
            ]);

            // Create ordonnance
            $ordonnance = $this->doctorService->createOrdonnance($data);

            return response()->json([
                'message' => 'Ordonnance created successfully',
                'data' => [
                    'id' => $ordonnance->id,
                    'consultation_id' => $ordonnance->consultation_id,
                    'details' => $ordonnance->details,
                    'date' => $ordonnance->date,
                    'created_at' => $ordonnance->created_at,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create ordonnance',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get ordonnance by ID
     * GET /ordonnances/{id}
     */
    public function show($id)
    {
        try {
            $ordonnance = $this->doctorService->getOrdonnanceById($id);

            return response()->json([
                'message' => 'Ordonnance retrieved successfully',
                'data' => [
                    'id' => $ordonnance->id,
                    'consultation' => [
                        'id' => $ordonnance->consultation->id,
                        'doctor_id' => $ordonnance->consultation->doctor_id,
                        'date' => $ordonnance->consultation->date,
                        'observations' => $ordonnance->consultation->observations,
                    ],
                    'details' => $ordonnance->details,
                    'date' => $ordonnance->date,
                    'created_at' => $ordonnance->created_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ordonnance not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve ordonnance',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update ordonnance
     * PUT /ordonnances/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation
            $data = $request->validate([
                'details' => 'required|string|max:2000',
            ]);

            $ordonnance = $this->doctorService->updateOrdonnance($id, $data);

            return response()->json([
                'message' => 'Ordonnance updated successfully',
                'data' => [
                    'id' => $ordonnance->id,
                    'details' => $ordonnance->details,
                    'updated_at' => $ordonnance->updated_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ordonnance not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete ordonnance
     * DELETE /ordonnances/{id}
     */
    public function destroy($id)
    {
        try {
            $this->doctorService->deleteOrdonnance($id);

            return response()->json([
                'message' => 'Ordonnance deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ordonnance not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Delete failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all ordonnances for a consultation
     * GET /consultations/{consultation_id}/ordonnances
     */
    public function getByConsultation($consultationId)
    {
        try {
            $consultation = $this->doctorService->getConsultationById($consultationId);
            $ordonnance = $consultation->ordonnance;

            if (!$ordonnance) {
                return response()->json([
                    'message' => 'No ordonnance found for this consultation'
                ], 404);
            }

            return response()->json([
                'message' => 'Ordonnance retrieved successfully',
                'data' => [
                    'id' => $ordonnance->id,
                    'consultation_id' => $ordonnance->consultation_id,
                    'details' => $ordonnance->details,
                    'date' => $ordonnance->date,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Consultation not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve ordonnance',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
