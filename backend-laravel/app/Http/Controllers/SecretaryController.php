<?php

namespace App\Http\Controllers;

use App\Services\SecretaryService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SecretaryController extends Controller
{
    private SecretaryService $secretaryService;

    public function __construct(SecretaryService $secretaryService)
    {
        $this->secretaryService = $secretaryService;
    }

    /**
     * Register a new secretary
     * POST /secretaries/register
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'office_number' => 'nullable|string|max:50',
                'assignment' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
            ]);

            $secretary = $this->secretaryService->registerSecretary($data);

            return response()->json([
                'message' => 'Secretary registered successfully',
                'data' => [
                    'secretary_id' => $secretary->id,
                    'nom' => $secretary->user->nom,
                    'email' => $secretary->user->email,
                    'office_number' => $secretary->office_number,
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
     * Get all secretaries
     * GET /secretaries
     */
    public function index()
    {
        try {
            $secretaries = $this->secretaryService->getAllSecretaries();

            return response()->json([
                'message' => 'Secretaries retrieved successfully',
                'count' => $secretaries->count(),
                'data' => $secretaries->map(function ($secretary) {
                    return [
                        'id' => $secretary->id,
                        'nom' => $secretary->user->nom,
                        'prenom' => $secretary->user->prenom,
                        'email' => $secretary->user->email,
                        'office_number' => $secretary->office_number,
                        'assignment' => $secretary->assignment,
                        'telephone' => $secretary->user->telephone,
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve secretaries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get secretary by ID
     * GET /secretaries/{id}
     */
    public function show($id)
    {
        try {
            $secretary = $this->secretaryService->getSecretaryById($id);

            return response()->json([
                'message' => 'Secretary retrieved successfully',
                'data' => [
                    'id' => $secretary->id,
                    'nom' => $secretary->user->nom,
                    'prenom' => $secretary->user->prenom,
                    'email' => $secretary->user->email,
                    'office_number' => $secretary->office_number,
                    'assignment' => $secretary->assignment,
                    'telephone' => $secretary->user->telephone,
                    'created_at' => $secretary->created_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Secretary not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update secretary
     * PUT /secretaries/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'office_number' => 'nullable|string|max:50',
                'assignment' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
            ]);

            $secretary = $this->secretaryService->updateSecretary($id, $data);

            return response()->json([
                'message' => 'Secretary updated successfully',
                'data' => [
                    'id' => $secretary->id,
                    'nom' => $secretary->user->nom,
                    'email' => $secretary->user->email,
                    'office_number' => $secretary->office_number,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Secretary not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete secretary
     * DELETE /secretaries/{id}
     */
    public function destroy($id)
    {
        try {
            $this->secretaryService->deleteSecretary($id);

            return response()->json([
                'message' => 'Secretary deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Secretary not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
