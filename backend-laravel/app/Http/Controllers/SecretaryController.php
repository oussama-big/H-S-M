<?php

namespace App\Http\Controllers;

use App\Services\SecretaryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecretaryController extends Controller
{
    private SecretaryService $secretaryService;

    public function __construct(SecretaryService $secretaryService)
    {
        $this->secretaryService = $secretaryService;
    }

    public function index()
    {
        $secretaries = $this->secretaryService->getAllSecretaries();

        return view('secretaries.index', compact('secretaries'));
    }

    public function create()
    {
        return view('secretaries.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'nullable|string|max:255|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'office_number' => 'nullable|string|max:50',
            'assignment' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $secretary = $this->secretaryService->registerSecretary($data);
            $secretary->load('user');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Secretaire enregistree avec succes.',
                    'secretary' => $secretary,
                ], 201);
            }

            return redirect()->route('secretaries.index')->with('success', 'Secretaire enregistree avec succes.');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur : ' . $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $secretary = $this->secretaryService->getSecretaryById($id);

        return view('secretaries.show', compact('secretary'));
    }

    public function edit($id)
    {
        $secretary = $this->secretaryService->getSecretaryById($id);

        return view('secretaries.edit', compact('secretary'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'login' => 'nullable|string|max:255|unique:users,login,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'office_number' => 'nullable|string|max:50',
            'assignment' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $secretary = $this->secretaryService->updateSecretary($id, $data);
            $secretary->load('user');

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Informations mises a jour.',
                    'secretary' => $secretary,
                ]);
            }

            return redirect()->route('secretaries.index')->with('success', 'Informations mises a jour.');
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
            $this->secretaryService->deleteSecretary($id);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Secretaire supprimee.',
                ]);
            }

            return redirect()->route('secretaries.index')->with('success', 'Secretaire supprimee.');
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

        $secretary = $this->secretaryService->getSecretaryById($id);
        $secretary->user->update([
            'password' => Hash::make('00000000'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le mot de passe de la secretaire a ete reinitialise a 00000000.',
        ]);
    }
}
