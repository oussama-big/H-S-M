<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class AdminController extends Controller
{
    private AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Register a new admin
     * POST /admins/register
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'department' => 'nullable|string|max:255',
                'permissions' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
            ]);

            $admin = $this->adminService->registerAdmin($data);

            return response()->json([
                'message' => 'Admin registered successfully',
                'data' => [
                    'admin_id' => $admin->id,
                    'nom' => $admin->user->nom,
                    'email' => $admin->user->email,
                    'department' => $admin->department,
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
     * Get all admins
     * GET /admins
     */
    public function index()
    {
        try {
            $admins = $this->adminService->getAllAdmins();

            return response()->json([
                'message' => 'Admins retrieved successfully',
                'count' => $admins->count(),
                'data' => $admins->map(function ($admin) {
                    return [
                        'id' => $admin->id,
                        'nom' => $admin->user->nom,
                        'prenom' => $admin->user->prenom,
                        'email' => $admin->user->email,
                        'department' => $admin->department,
                        'telephone' => $admin->user->telephone,
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve admins',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin by ID
     * GET /admins/{id}
     */
    public function show($id)
    {
        try {
            $admin = $this->adminService->getAdminById($id);

            return response()->json([
                'message' => 'Admin retrieved successfully',
                'data' => [
                    'id' => $admin->id,
                    'nom' => $admin->user->nom,
                    'prenom' => $admin->user->prenom,
                    'email' => $admin->user->email,
                    'department' => $admin->department,
                    'permissions' => $admin->permissions,
                    'telephone' => $admin->user->telephone,
                    'created_at' => $admin->created_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Admin not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update admin
     * PUT /admins/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'department' => 'nullable|string|max:255',
                'permissions' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
            ]);

            $admin = $this->adminService->updateAdmin($id, $data);

            return response()->json([
                'message' => 'Admin updated successfully',
                'data' => [
                    'id' => $admin->id,
                    'nom' => $admin->user->nom,
                    'email' => $admin->user->email,
                    'department' => $admin->department,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Admin not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete admin
     * DELETE /admins/{id}
     */
    public function destroy($id)
    {
        try {
            $this->adminService->deleteAdmin($id);

            return response()->json([
                'message' => 'Admin deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Admin not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
