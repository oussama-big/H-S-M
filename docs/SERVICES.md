# 💼 SERVICES - GUIDE COMPLET

## 🎯 RÔLE DES SERVICES

Les **Services** contiennent la **logique métier** (business logic) du système.

### Métaphore:
Si une application était un restaurant:
- **Controller** = Serveur (reçoit commandes)
- **Service** = Cuisine (fait le plat)
- **Model** = Ingrédients
- **Repository** = Cave (récupère ingrédients)

### Pourquoi Services?
```php
// ❌ MAUVAISE façon (logique dans le controller)
public function register(Request $request)
{
    $user = User::create([...]);
    $patient = Patient::create(['id' => $user->id, ...]);
    $dossier = DossierMedical::create(['patient_id' => $patient->id, ...]);
    Notification::create(['type' => 'welcome', ...]);
    // ... trop de code dans le controller
}

// ✅ BONNE façon (logique dans le service)
public function register(Request $request)
{
    $patient = $this->patientService->registerPatient($request->validated());
    return response()->json(['data' => $patient], 201);
}
```

---

## 📂 STRUCTURE

```
app/Services/
├── UserService.php          → Créer/Modifier utilisateurs
├── AuthService.php          → Authentification
├── PatientService.php       → Logique patient complète
├── DoctorService.php        → Logique docteur complète
├── AdminService.php         → Logique admin complète
└── SecretaryService.php     → Logique secrétaire complète
```

---

## 1️⃣ AuthService.php

**Rôle:** Gérer l'authentification (login, vérifier password, créer tokens)

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Authentifier un utilisateur
     * 
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login(string $email, string $password): array
    {
        try {
            // 1. Chercher l'utilisateur par email
            $user = User::where('email', $email)->first();

            // 2. Utilisateur n'existe pas?
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found',
                ];
            }

            // 3. Vérifier le password
            // Hash::check('plaintext', 'hashed') = compare
            if (!Hash::check($password, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Invalid password',
                ];
            }

            // 4. Créer un token Sanctum
            // createToken(name) = crée un token unique pour cet user
            $token = $user->createToken('api-token')->plainTextToken;

            // 5. Retourner succès
            return [
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nom' => $user->nom,
                    'role' => $user->role,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier si un email existe
     */
    public function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Vérifier si un password est fort
     * Règles: 8+ caractères, au moins 1 majuscule, 1 chiffre, 1 spécial
     */
    public function isStrongPassword(string $password): bool
    {
        // Regex: 8+ chars, avec majuscule, chiffre, caractère spécial
        return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password) === 1;
    }
}
```

### 💡 Notions Clés:

**1. Hash::check():**
```php
Hash::check('plaintext', '$2y$10$abcd...');
// Compare le texte brut avec le hash
// Retourne true/false
```

**2. createToken():**
```php
$token = $user->createToken('api-token')->plainTextToken;
// Crée un token Sanctum unique
// Retourne le token en plaintext (qu'une fois!)
```

**3. Regex pour Password Fort:**
```
^(?=.*[A-Z])    → Au moins 1 majuscule
(?=.*\d)        → Au moins 1 chiffre
(?=.*[@$!%*?&]) → Au moins 1 caractère spécial
[A-Za-z\d@$!%*?&]{8,}  → 8+ caractères
```

---

## 2️⃣ UserService.php

**Rôle:** Créer et gérer les utilisateurs

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Créer un nouvel utilisateur
     */
    public function createUser(array $data): User
    {
        // 1. Hasher le password
        $data['password'] = Hash::make($data['password']);

        // 2. Créer l'utilisateur
        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'PATIENT', // Rôle par défaut
        ]);

        return $user;
    }

    /**
     * Récupérer un utilisateur par ID
     */
    public function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Récupérer tous les utilisateurs
     */
    public function getAllUsers(): array
    {
        return User::all()->toArray();
    }

    /**
     * Modifier un utilisateur
     */
    public function updateUser(int $id, array $data): ?User
    {
        $user = User::find($id);

        if (!$user) {
            return null;
        }

        // Si password modifié, le hasher
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user;
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser(int $id): bool
    {
        $user = User::find($id);
        
        if (!$user) {
            return false;
        }

        $user->delete();
        return true;
    }
}
```

---

## 3️⃣ PatientService.php

**Rôle:** Logique complète des patients

```php
<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Models\DossierMedical;
use Illuminate\Support\Str;

class PatientService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Enregistrer un nouveau patient
     * 
     * @param array $data
     * @return array
     */
    public function registerPatient(array $data): array
    {
        try {
            // 1. Créer l'utilisateur
            $user = $this->userService->createUser([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'PATIENT',
            ]);

            // 2. Créer le patient (spécialisation)
            $numDossier = 'PAT-' . Str::padLeft($user->id, 5, '0');
            
            $patient = Patient::create([
                'id' => $user->id,
                'numDossier' => $numDossier,
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'blood_type' => $data['blood_type'],
                'telephone' => $data['telephone'],
            ]);

            // 3. Créer le dossier médical
            DossierMedical::create([
                'patient_id' => $patient->id,
                'numDossier' => $numDossier,
                'medical_history' => '',
                'allergies' => '',
                'chronic_diseases' => '',
            ]);

            // 4. Retourner les données formatées
            return [
                'patient_id' => $patient->id,
                'numDossier' => $numDossier,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Patient registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer tous les patients
     */
    public function getAllPatients(): array
    {
        return Patient::with('user')->get()->map(function ($patient) {
            return [
                'id' => $patient->id,
                'numDossier' => $patient->numDossier,
                'nom' => $patient->user->nom,
                'prenom' => $patient->user->prenom,
                'email' => $patient->user->email,
                'date_of_birth' => $patient->date_of_birth,
                'gender' => $patient->gender,
                'blood_type' => $patient->blood_type,
                'telephone' => $patient->telephone,
            ];
        })->toArray();
    }

    /**
     * Récupérer un patient par ID
     */
    public function getPatientById(int $id): ?array
    {
        $patient = Patient::with('user')->find($id);

        if (!$patient) {
            return null;
        }

        return [
            'id' => $patient->id,
            'numDossier' => $patient->numDossier,
            'nom' => $patient->user->nom,
            'prenom' => $patient->user->prenom,
            'email' => $patient->user->email,
            'date_of_birth' => $patient->date_of_birth,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'telephone' => $patient->telephone,
            'appointments' => $patient->appointments->count(),
            'consultations' => $patient->consultations->count(),
        ];
    }

    /**
     * Modifier un patient
     */
    public function updatePatient(int $id, array $data): ?array
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return null;
        }

        $patient->update($data);

        return [
            'id' => $patient->id,
            'numDossier' => $patient->numDossier,
            'telephone' => $patient->telephone,
            'blood_type' => $patient->blood_type,
        ];
    }

    /**
     * Supprimer un patient (avec cascade delete)
     */
    public function deletePatient(int $id): bool
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return false;
        }

        // Supprimer le dossier médical (cascade)
        $patient->dossier?->delete();

        // Supprimer les appointments
        $patient->appointments()->delete();

        // Supprimer le patient
        $patient->delete();

        // Supprimer l'utilisateur
        User::find($id)->delete();

        return true;
    }
}
```

### 💡 Notions Clés:

**1. Str::padLeft():**
```php
Str::padLeft(5, 5, '0');
// Remplit avec des 0 à gauche
// Résultat: "00005"
```

**2. with() - Eager Loading:**
```php
Patient::with('user')->find($id);
// Charge AUSSI la relation user en même temps
// Évite N+1 queries problem
```

**3. map() - Transformer collection:**
```php
$patients->map(function ($patient) {
    return ['id' => $patient->id, ...];
})->toArray();
// Transforme chaque patient avant de retourner
```

**4. Cascade Delete:**
```php
$patient->dossier?->delete();      // Avec ? = si existe
$patient->appointments()->delete();
$patient->delete();
User::find($id)->delete();
```

---

## 4️⃣ DoctorService.php & AdminService.php

Structure similaire à PatientService:

```php
class DoctorService
{
    public function registerDoctor(array $data): array { ... }
    public function getAllDoctors(): array { ... }
    public function getDoctorById(int $id): ?array { ... }
    public function updateDoctor(int $id, array $data): ?array { ... }
    public function deleteDoctor(int $id): bool { ... }
}

class AdminService
{
    public function registerAdmin(array $data): array { ... }
    public function getAllAdmins(): array { ... }
    public function getAdminById(int $id): ?array { ... }
    public function updateAdmin(int $id, array $data): ?array { ... }
    public function deleteAdmin(int $id): bool { ... }
}
```

---

## 📋 PATTERN STANDARD D'UN SERVICE

```php
1. Injection de dépendances (Constructor)
   private UserService $userService;

2. Méthodes publiques pour chaque action
   - register/create    → Créer
   - getAll             → Lister
   - getById            → Détail
   - update             → Modifier
   - delete             → Supprimer

3. Try-catch pour gestion erreurs
   try {
       // Code
   } catch (\Exception $e) {
       throw new \Exception('Message: ' . $e->getMessage());
   }

4. Retourner des arrays formatés (pas d'objets)
   return ['id' => ..., 'nom' => ...];
```

---

## 🔄 FLUX COMPLET: Register Patient

```
POST /api/patients/register
    ↓
PatientController->register()
    └─ validate()
    └─ PatientService->registerPatient()
       ├─ UserService->createUser()
       │  └─ User::create() [INSERT users]
       ├─ Patient::create() [INSERT patients]
       └─ DossierMedical::create() [INSERT dossier_medicals]
    └─ Response JSON 201
```

---

## ✅ CHECKLIST SERVICES

- [ ] Comprendre la séparation Controller ↔ Service
- [ ] Comprendre Hash::make() et Hash::check()
- [ ] Comprendre createToken() (Sanctum)
- [ ] Comprendre eager loading (with())
- [ ] Comprendre map() et toArray()
- [ ] Comprendre cascade delete
- [ ] Savoir créer un service personnalisé

---

## 🎯 RÉSUMÉ

| Action | Service |
|--------|---------|
| Créer user | UserService::createUser() |
| Login | AuthService::login() |
| Register patient | PatientService::registerPatient() |
| Lister patients | PatientService::getAllPatients() |
| Détail patient | PatientService::getPatientById() |
| Modifier patient | PatientService::updatePatient() |
| Supprimer patient | PatientService::deletePatient() |

---

**Les Services sont le cœur de la logique métier!** 🚀
