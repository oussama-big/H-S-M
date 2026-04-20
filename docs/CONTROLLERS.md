# 🎮 CONTROLLERS - GUIDE COMPLET

## 🎯 RÔLE DES CONTROLLERS

Les **Controllers** sont les **"orchestrateurs"** qui:
- Reçoivent les requêtes HTTP
- Valident les données
- Appellent les Services (logique métier)
- Retournent les réponses JSON

### Métaphore:
Si l'API était un restaurant:
- **Routes** = Serveur qui dirige le client à une table
- **Controller** = Chef qui prend la commande et dit à ses cuisiniers ce qu'il faut faire
- **Service** = Cuisiniers qui font le plat
- **Model** = Ingrédients

---

## 📂 STRUCTURE

```
app/Http/Controllers/
├── AuthController.php       → Authentification
├── PatientController.php     → Gestion patients
├── DoctorController.php      → Gestion docteurs
├── AdminController.php       → Gestion admins
├── SecretaryController.php   → Gestion secrétaires
└── Controller.php            → Classe parent
```

---

## 1️⃣ AuthController.php

**Rôle:** Gérer l'authentification (login, logout)

```php
<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Connexion utilisateur
     * 
     * @param Request $request
     * @return Response JSON
     */
    public function login(Request $request)
    {
        // 1. Valider les données
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 2. Appeler le service
        $response = $this->authService->login(
            $validated['email'],
            $validated['password']
        );

        // 3. Retourner selon succès/erreur
        if (!$response['success']) {
            return response()->json(
                ['message' => 'Identifiants invalides'],
                401
            );
        }

        // 4. Succès: Retourner token
        return response()->json([
            'message' => 'Connexion réussie',
            'access_token' => $response['token'],
            'token_type' => 'Bearer',
            'user' => $response['user'],
        ], 200);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Déconnecté'
        ], 200);
    }
}
```

### 💡 Notions Clés Expliquées:

**1. $validate():**
```php
$request->validate([
    'email' => 'required|email',
    'password' => 'required|string|min:6',
]);
```

**Règles de validation:**
- `required` = Ce champ est obligatoire
- `email` = Doit être un format email valide
- `string` = Doit être une chaîne de caractères
- `min:6` = Minimum 6 caractères

**Si validation échoue:** Laravel retourne automatiquement 422 (Unprocessable Entity)

**2. Dependency Injection:**
```php
public function __construct(AuthService $authService)
{
    $this->authService = $authService;
}
```

**Qu'est-ce que c'est?**
- Le service est "injecté" dans le controller
- Laravel crée automatiquement une instance du service
- On peut l'utiliser partout dans le controller
- Facilite les tests unitaires

**3. Response JSON:**
```php
return response()->json([
    'message' => 'Connexion réussie',
    'access_token' => $response['token'],
], 200);
```

**Structure:**
- Premier argument = données (array)
- Deuxième argument = status code HTTP
- Statut codes communs:
  - 200 = OK
  - 201 = Created
  - 400 = Bad Request
  - 401 = Unauthorized
  - 404 = Not Found
  - 422 = Validation Error
  - 500 = Server Error

---

## 2️⃣ PatientController.php

**Rôle:** CRUD (Create, Read, Update, Delete) des patients

```php
<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    private PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * POST /api/patients/register
     * Créer un nouveau patient
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:M,F',
            'blood_type' => 'required|string',
            'telephone' => 'required|string',
        ]);

        try {
            $patient = $this->patientService->registerPatient($validated);
            
            return response()->json([
                'message' => 'Patient created successfully',
                'data' => $patient,
            ], 201); // 201 = Created (nouveau ressource)
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/patients
     * Récupérer TOUS les patients
     */
    public function index()
    {
        try {
            $patients = $this->patientService->getAllPatients();
            
            return response()->json([
                'message' => 'Patients retrieved successfully',
                'count' => count($patients),
                'data' => $patients,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/patients/{id}
     * Récupérer UN patient par ID
     */
    public function show($id)
    {
        try {
            $patient = $this->patientService->getPatientById($id);
            
            if (!$patient) {
                return response()->json([
                    'message' => 'Patient not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Patient retrieved successfully',
                'data' => $patient,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/patients/{id}
     * Modifier UN patient
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'telephone' => 'sometimes|string',
            'blood_type' => 'sometimes|string',
            // Autres champs modifiables
        ]);

        try {
            $patient = $this->patientService->updatePatient($id, $validated);
            
            return response()->json([
                'message' => 'Patient updated successfully',
                'data' => $patient,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * DELETE /api/patients/{id}
     * Supprimer UN patient (cascade delete)
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->patientService->deletePatient($id);
            
            if (!$deleted) {
                return response()->json([
                    'message' => 'Patient not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Patient deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }
}
```

### 🔄 Flux PatientController:

```
Requête HTTP
    ↓
Route match (ex: POST /api/patients/register)
    ↓
PatientController->register()
    ↓
Valider: validate()
    ↓ Valide? OUI → continue
    ↓ Valide? NON → Retour 422
    ↓
Appeler: PatientService->registerPatient()
    ↓
Retourner: Response JSON + Status Code
```

---

## 3️⃣ DoctorController.php

**Rôle:** CRUD des docteurs

```php
<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'speciality' => 'required|string',
            'experience_years' => 'required|integer',
        ]);

        try {
            $doctor = $this->doctorService->registerDoctor($validated);
            return response()->json([
                'message' => 'Doctor registered successfully',
                'data' => $doctor,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function index()
    {
        try {
            $doctors = $this->doctorService->getAllDoctors();
            return response()->json([
                'message' => 'Doctors retrieved successfully',
                'count' => count($doctors),
                'data' => $doctors,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $doctor = $this->doctorService->getDoctorById($id);
            if (!$doctor) {
                return response()->json([
                    'message' => 'Doctor not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Doctor retrieved successfully',
                'data' => $doctor,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'speciality' => 'sometimes|string',
            'experience_years' => 'sometimes|integer',
        ]);

        try {
            $doctor = $this->doctorService->updateDoctor($id, $validated);
            return response()->json([
                'message' => 'Doctor updated successfully',
                'data' => $doctor,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->doctorService->deleteDoctor($id);
            if (!$deleted) {
                return response()->json([
                    'message' => 'Doctor not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Doctor deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }
}
```

---

## 4️⃣ AdminController.php & SecretaryController.php

Structure identique à DoctorController, avec champs spécifiques:

```php
// AdminController
public function register(Request $request)
{
    $validated = $request->validate([
        'nom' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'department' => 'required|string',
        'permissions' => 'required|string',
    ]);
    // ...
}

// SecretaryController
public function register(Request $request)
{
    $validated = $request->validate([
        'nom' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'office_number' => 'required|string',
        'assignment' => 'required|string',
    ]);
    // ...
}
```

---

## 📋 TABLEAU RÉCAPITULATIF

| Méthode | HTTP | Route | Action | Status |
|---------|------|-------|--------|--------|
| register() | POST | /api/patients/register | Créer | 201 |
| index() | GET | /api/patients | Lister tous | 200 |
| show() | GET | /api/patients/{id} | Récupérer 1 | 200/404 |
| update() | PUT | /api/patients/{id} | Modifier 1 | 200/404 |
| destroy() | DELETE | /api/patients/{id} | Supprimer 1 | 200/404 |

---

## 🎯 PATTERN STANDARD

Tous les controllers suivent ce pattern:

```php
1. VALIDER (validate)
   ↓
2. TRY-CATCH
   ├─ Appeler Service
   ├─ Retourner Success JSON
   └─ CATCH → Retourner Error JSON
   
3. STATUS CODES
   ├─ 201 = Créé (POST)
   ├─ 200 = OK (GET, PUT, DELETE)
   ├─ 400 = Bad request
   ├─ 404 = Not found
   ├─ 422 = Validation error
   └─ 500 = Server error
```

---

## ✅ CHECKLIST CONTROLLERS

- [ ] Comprendre le rôle d'un controller
- [ ] Comprendre la validation (validate)
- [ ] Comprendre Dependency Injection
- [ ] Comprendre les status codes HTTP
- [ ] Savoir créer un controller avec CRUD
- [ ] Comprendre try-catch pour gestion erreurs

---

**Les Controllers sont la porte d'entrée de l'API!** 🚀
