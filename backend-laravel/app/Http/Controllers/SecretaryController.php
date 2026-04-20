<?php

namespace App\Http\Controllers;

use App\Services\SecretaryService;
use Illuminate\Http\Request;
use Exception;

class SecretaryController extends Controller
{
    private SecretaryService $secretaryService;

    public function __construct(SecretaryService $secretaryService)
    {
        $this->secretaryService = $secretaryService;
    }

    /**
     * Liste des secrétaires (Vue)
     */
    public function index()
    {
        $secretaries = $this->secretaryService->getAllSecretaries();
        return view('secretaries.index', compact('secretaries'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('secretaries.create');
    }

    /**
     * Enregistrement d'une secrétaire
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'office_number' => 'nullable|string|max:50',
            'assignment' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->secretaryService->registerSecretary($data);
            return redirect()->route('secretaries.index')->with('success', 'Secrétaire enregistrée avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Profil d'une secrétaire
     */
    public function show($id)
    {
        $secretary = $this->secretaryService->getSecretaryById($id);
        return view('secretaries.show', compact('secretary'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $secretary = $this->secretaryService->getSecretaryById($id);
        return view('secretaries.edit', compact('secretary'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'office_number' => 'nullable|string|max:50',
            'assignment' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->secretaryService->updateSecretary($id, $data);
            return redirect()->route('secretaries.index')->with('success', 'Informations mises à jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Échec de la mise à jour.');
        }
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        try {
            $this->secretaryService->deleteSecretary($id);
            return redirect()->route('secretaries.index')->with('success', 'Secrétaire supprimée.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }
}