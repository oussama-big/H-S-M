<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function index()
    {
        $doctors = $this->doctorService->getAllDoctors();

        return view('doctors.index', compact('doctors'));
    }

    public function create()
    {
        return view('doctors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'nullable|string|max:255|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'specialization' => 'required|string|max:255',
            'license_number' => 'required|string|unique:doctors,license_number',
            'telephone' => 'nullable|string|max:20',
            'cabinet_id' => 'nullable|exists:cabinets,id',
        ]);

        try {
            $doctor = $this->doctorService->registerDoctor($data);
            $doctor->load(['user', 'cabinet']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Medecin enregistre avec succes.',
                    'doctor' => $doctor,
                ], 201);
            }

            return redirect()->route('doctors.index')->with('success', 'Medecin enregistre avec succes.');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l inscription : ' . $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', 'Erreur lors de l inscription : ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $doctor = $this->doctorService->getDoctorById($id);

        return view('doctors.show', compact('doctor'));
    }

    public function edit($id)
    {
        $doctor = $this->doctorService->getDoctorById($id);

        return view('doctors.edit', compact('doctor'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'login' => 'nullable|string|max:255|unique:users,login,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|unique:doctors,license_number,' . $id,
            'telephone' => 'nullable|string|max:20',
            'cabinet_id' => 'nullable|exists:cabinets,id',
        ]);

        try {
            $doctor = $this->doctorService->updateDoctor($id, $data);
            $doctor->load(['user', 'cabinet']);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Informations du medecin mises a jour.',
                    'doctor' => $doctor,
                ]);
            }

            return redirect()->route('doctors.index')->with('success', 'Informations du medecin mises a jour.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Echec de la mise a jour.',
                ], 422);
            }

            return back()->with('error', 'Echec de la mise a jour.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->doctorService->deleteDoctor($id);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Medecin supprime du systeme.',
                ]);
            }

            return redirect()->route('doctors.index')->with('success', 'Medecin supprime du systeme.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suppression impossible.',
                ], 422);
            }

            return back()->with('error', 'Suppression impossible.');
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Acces reserve aux administrateurs.',
            ], 403);
        }

        $doctor = $this->doctorService->getDoctorById($id);
        $doctor->user->update([
            'password' => Hash::make('00000000'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le mot de passe du medecin a ete reinitialise a 00000000.',
        ]);
    }
}
