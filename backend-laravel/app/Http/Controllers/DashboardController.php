<?php

namespace App\Http\Controllers;

use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private AdminDashboardService $adminDashboardService;

    public function __construct(AdminDashboardService $adminDashboardService)
    {
        $this->adminDashboardService = $adminDashboardService;
    }

    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'ADMIN' => redirect()->route('admin.dashboard'),
            'SECRETAIRE' => redirect()->route('secretary.dashboard'),
            'MEDECIN' => redirect()->route('doctor.dashboard'),
            'PATIENT' => redirect()->route('patient.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }

    public function adminData(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Acces reserve au backoffice.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->adminDashboardService->getDashboardData($user->id),
        ]);
    }
}
