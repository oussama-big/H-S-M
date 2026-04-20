<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Exception;

class DoctorController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Liste de tous les médecins (Vue)
     */
    public function index()
    {
        $doctors = $this->doctorService->getAllDoctors();
        return view('doctors.index', compact('doctors'));
    }

    /**
     * Formulaire d'ajout d'un médecin
     */
    public function create()
    {
        return view('doctors.create');
    }

    /**
     * Enregistrement du médecin
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'specialization' => 'required|string|max:255',
            'license_number' => 'required|string|unique:doctors,license_number',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->doctorService->registerDoctor($data);
            return redirect()->route('doctors.index')->with('success', 'Médecin enregistré avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de l\'inscription : ' . $e->getMessage());
        }
    }

    /**
     * Profil détaillé du médecin
     */
    public function show($id)
    {
        $doctor = $this->doctorService->getDoctorById($id);
        return view('doctors.show', compact('doctor'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $doctor = $this->doctorService->getDoctorById($id);
        return view('doctors.edit', compact('doctor'));
    }

    /**
     * Mise à jour des informations
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id, // Assurez-vous que l'ID est celui du User
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|unique:doctors,license_number,' . $id,
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->doctorService->updateDoctor($id, $data);
            return redirect()->route('doctors.index')->with('success', 'Informations du médecin mises à jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Échec de la mise à jour.');
        }
    }

    /**
     * Suppression d'un médecin
     */
    public function destroy($id)
    {
        try {
            $this->doctorService->deleteDoctor($id);
            return redirect()->route('doctors.index')->with('success', 'Médecin supprimé du système.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }
}