<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use Illuminate\Http\Request;
use Exception;

class AdminController extends Controller
{
    private AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Liste de tous les administrateurs
     */
    public function index()
    {
        $admins = $this->adminService->getAllAdmins();
        return view('admins.index', compact('admins'));
    }

    /**
     * Formulaire d'ajout d'un nouvel admin
     */
    public function create()
    {
        return view('admins.create');
    }

    /**
     * Enregistrement d'un admin
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->adminService->registerAdmin($data);
            return redirect()->route('admins.index')->with('success', 'Administrateur ajouté avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $admin = $this->adminService->getAdminById($id);
        return view('admins.edit', compact('admin'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id, // Attention: il faut l'ID du User ici
            'department' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->adminService->updateAdmin($id, $data);
            return redirect()->route('admins.index')->with('success', 'Profil admin mis à jour.');
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
            $this->adminService->deleteAdmin($id);
            return redirect()->route('admins.index')->with('success', 'Admin supprimé.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }
}