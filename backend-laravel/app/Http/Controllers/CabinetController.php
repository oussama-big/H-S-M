<?php

namespace App\Http\Controllers;

use App\Services\CabinetService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CabinetController extends Controller
{
    private CabinetService $cabinetService;

    public function __construct(CabinetService $cabinetService)
    {
        $this->cabinetService = $cabinetService;
    }

    /**
     * Create a new cabinet
     * POST /cabinets
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:cabinets,name',
                'address' => 'required|string|max:500',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $cabinet = $this->cabinetService->createCabinet($data);

            return response()->json([
                'message' => 'Cabinet created successfully',
                'data' => [
                    'id' => $cabinet->id,
                    'name' => $cabinet->name,
                    'address' => $cabinet->address,
                    'telephone' => $cabinet->telephone,
                    'email' => $cabinet->email,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create cabinet',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all cabinets
     * GET /cabinets
     */
    public function index()
    {
        try {
            $cabinets = $this->cabinetService->getAllCabinets();

            return response()->json([
                'message' => 'Cabinets retrieved successfully',
                'count' => $cabinets->count(),
                'data' => $cabinets->map(function ($cabinet) {
                    return [
                        'id' => $cabinet->id,
                        'name' => $cabinet->name,
                        'address' => $cabinet->address,
                        'telephone' => $cabinet->telephone,
                        'email' => $cabinet->email,
                        'doctors_count' => $cabinet->doctors->count(),
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve cabinets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cabinet by ID
     * GET /cabinets/{id}
     */
    public function show($id)
    {
        try {
            $cabinetInfo = $this->cabinetService->getCabinetInfo($id);

            return response()->json([
                'message' => 'Cabinet retrieved successfully',
                'data' => $cabinetInfo
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Cabinet not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update cabinet
     * PUT /cabinets/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'name' => 'nullable|string|max:255|unique:cabinets,name,' . $id,
                'address' => 'nullable|string|max:500',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $cabinet = $this->cabinetService->updateCabinet($id, $data);

            return response()->json([
                'message' => 'Cabinet updated successfully',
                'data' => [
                    'id' => $cabinet->id,
                    'name' => $cabinet->name,
                    'address' => $cabinet->address,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Cabinet not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete cabinet
     * DELETE /cabinets/{id}
     */
    public function destroy($id)
    {
        try {
            $this->cabinetService->deleteCabinet($id);

            return response()->json([
                'message' => 'Cabinet deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Cabinet not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get cabinet doctors
     * GET /cabinets/{id}/doctors
     */
    public function getDoctors($id)
    {
        try {
            $doctors = $this->cabinetService->getCabinetDoctors($id);

            return response()->json([
                'message' => 'Cabinet doctors retrieved',
                'count' => $doctors->count(),
                'data' => $doctors->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'nom' => $doctor->user->nom,
                        'prenom' => $doctor->user->prenom,
                        'specialization' => $doctor->specialization,
                        'license_number' => $doctor->license_number,
                    ];
                })
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Cabinet not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search cabinets by name
     * GET /cabinets/search/name?q=query
     */
    public function searchByName(Request $request)
    {
        try {
            $query = $request->query('q');
            
            if (!$query) {
                return response()->json(['error' => 'Search query required'], 400);
            }

            $cabinets = $this->cabinetService->getCabinetsByName($query);

            return response()->json([
                'message' => 'Cabinets found',
                'count' => $cabinets->count(),
                'data' => $cabinets
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
