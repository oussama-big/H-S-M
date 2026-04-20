# 📍 ROUTES API - GUIDE COMPLET

## 🎯 RÔLE DES ROUTES

Les **routes** sont le point d'entrée de l'API. Elles mappent les URLs HTTP aux controllers appropriés. C'est comme un "gestionnaire de circulation" qui dit:
- "Quand tu reçois une requête POST sur `/api/login`, appelle le controller AuthController et sa méthode login()"
- "Quand tu reçois une requête GET sur `/api/patients`, appelle le controller PatientController et sa méthode index()"

### Notions Clés:

**1. Routing:** Le processus qui transforme une URL en appel de fonction
**2. Middleware:** Des filtres qui vérifient les permissions avant d'exécuter la route (ex: `auth:sanctum` vérifie le token)
**3. Resource Routes:** Une convention Laravel qui crée automatiquement 7 routes (GET, POST, PUT, DELETE, etc.)

---

## 📂 STRUCTURE DU FICHIER routes/api.php

```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, PatientController, DoctorController, ...};

// PUBLIC ROUTES (sans authentification)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/patients/register', [PatientController::class, 'register']);
// ... autres routes publiques

// PROTECTED ROUTES (avec authentification)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('doctors', DoctorController::class);
    // ... autres ressources
});
```

---

## 🔓 ROUTES PUBLIQUES (Sans Token)

### 1. POST /api/login
**Rôle:** Authentifier un utilisateur et retourner un token

**Code:**
```php
Route::post('/login', [AuthController::class, 'login']);
```

**Requête Postman:**
```
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse (200 OK):**
```json
{
  "message": "Connexion réussie",
  "access_token": "1|abc123def456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "role": "PATIENT"
  }
}
```

**Qu'est-ce qui se passe:**
1. Le controller reçoit email + password
2. Il vérifie dans la base de données si l'email existe
3. Il compare le password (hashé) avec la base
4. Si OK: crée un token unique avec Sanctum
5. Retourne le token au client

---

### 2. POST /api/patients/register
**Rôle:** Créer un nouveau patient

**Code:**
```php
Route::post('/patients/register', [PatientController::class, 'register'])->name('patients.register');
```

**Requête:**
```json
{
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean@example.com",
  "password": "Pass123!",
  "password_confirmation": "Pass123!",
  "date_of_birth": "1990-01-15",
  "gender": "M",
  "blood_type": "O+",
  "telephone": "+212612345670"
}
```

**Réponse (201 Created):**
```json
{
  "message": "Patient registered successfully",
  "data": {
    "patient_id": 1,
    "numDossier": "PAT-60A4B2C",
    "nom": "Dupont",
    "email": "jean@example.com"
  }
}
```

---

### 3. POST /api/doctors/register
**Rôle:** Créer un nouveau docteur

```php
Route::post('/doctors/register', [DoctorController::class, 'register'])->name('doctors.register');
```

---

### 4. POST /api/admins/register
**Rôle:** Créer un administrateur

```php
Route::post('/admins/register', [AdminController::class, 'register'])->name('admins.register');
```

---

### 5. POST /api/secretaries/register
**Rôle:** Créer une secrétaire

```php
Route::post('/secretaries/register', [SecretaryController::class, 'register'])->name('secretaries.register');
```

---

## 🔒 ROUTES PROTÉGÉES (Avec Token)

### ⚠️ MIDDLEWARE: auth:sanctum

Avant d'exécuter ces routes, Laravel vérifie:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Toutes les routes ici nécessitent un token valide
});
```

**Ce que le middleware fait:**
1. Cherche le header `Authorization: Bearer {token}` dans la requête
2. Extrait le token
3. Cherche dans `personal_access_tokens` si le token existe
4. Si OUI → Continue la route
5. Si NON → Retourne 401 "Unauthenticated."

---

### 📚 Resource Routes (Automatique)

```php
Route::apiResource('patients', PatientController::class);
```

Cette ligne crée **automatiquement 7 routes:**

| Méthode | URL | Contrôleur | Action | Rôle |
|---------|-----|-----------|--------|------|
| GET | `/api/patients` | PatientController | index() | Récupérer tous |
| POST | `/api/patients` | PatientController | store() | Créer un |
| GET | `/api/patients/{id}` | PatientController | show() | Récupérer un par ID |
| PUT | `/api/patients/{id}` | PatientController | update() | Modifier un |
| DELETE | `/api/patients/{id}` | PatientController | destroy() | Supprimer un |
| PATCH | `/api/patients/{id}` | PatientController | update() | Modifier (alternatif) |

---

### 1. GET /api/patients
**Rôle:** Récupérer la liste de TOUS les patients

**Code (généré automatiquement):**
```php
// Route::apiResource('patients', PatientController::class);
// Cette ligne génère automatiquement la route GET /api/patients vers index()
```

**Requête:**
```
GET http://localhost:8000/api/patients
Authorization: Bearer {{TOKEN}}
```

**Réponse (200 OK):**
```json
{
  "message": "Patients retrieved successfully",
  "count": 2,
  "data": [
    {
      "id": 1,
      "numDossier": "PAT-00001",
      "nom": "Dupont",
      "prenom": "Jean",
      "email": "jean@example.com"
    },
    {
      "id": 2,
      "numDossier": "PAT-00002",
      "nom": "Martin",
      "prenom": "Pierre",
      "email": "pierre@example.com"
    }
  ]
}
```

---

### 2. GET /api/patients/{id}
**Rôle:** Récupérer les détails d'UN patient spécifique

**Requête:**
```
GET http://localhost:8000/api/patients/1
Authorization: Bearer {{TOKEN}}
```

**Réponse (200 OK):**
```json
{
  "message": "Patient retrieved successfully",
  "data": {
    "id": 1,
    "numDossier": "PAT-00001",
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean@example.com",
    "date_of_birth": "1990-01-15",
    "gender": "M",
    "blood_type": "O+",
    "telephone": "+212612345670"
  }
}
```

---

### 3. POST /api/patients (Store)
**Rôle:** Créer un nouveau patient (alternative à /register)

**Code:**
```php
Route::apiResource('patients', PatientController::class);
// Crée aussi la route POST /api/patients vers store()
```

**Requête:**
```json
{
  "nom": "Petit",
  "prenom": "Marc",
  "email": "marc@example.com",
  "password": "Pass123!",
  "password_confirmation": "Pass123!",
  "date_of_birth": "1985-03-20",
  "gender": "M",
  "blood_type": "AB+",
  "telephone": "+212698765432"
}
```

**Réponse (201 Created):**
```json
{
  "message": "Patient created successfully",
  "data": {
    "id": 3,
    "numDossier": "PAT-00003",
    "nom": "Petit",
    "email": "marc@example.com"
  }
}
```

---

### 4. PUT /api/patients/{id}
**Rôle:** Modifier les données d'un patient

**Requête:**
```
PUT http://localhost:8000/api/patients/1
Authorization: Bearer {{TOKEN}}
Content-Type: application/json

{
  "telephone": "+212612345671",
  "blood_type": "A++"
}
```

**Réponse (200 OK):**
```json
{
  "message": "Patient updated successfully",
  "data": {
    "id": 1,
    "nom": "Dupont",
    "telephone": "+212612345671",
    "blood_type": "A+"
  }
}
```

---

### 5. DELETE /api/patients/{id}
**Rôle:** Supprimer un patient (avec cascade delete des données liées)

**Requête:**
```
DELETE http://localhost:8000/api/patients/1
Authorization: Bearer {{TOKEN}}
```

**Réponse (200 OK):**
```json
{
  "message": "Patient deleted successfully"
}
```

**Important:** Cela supprime aussi:
- Le dossier médical du patient
- Toutes les consultations
- Toutes les ordonnances

---

## 🏥 ROUTES POUR DOCTORS, ADMINS, SECRETARIES

La même structure se répète pour doctors, admins et secretaries:

```php
// Public
Route::post('/doctors/register', [DoctorController::class, 'register']);
Route::post('/admins/register', [AdminController::class, 'register']);
Route::post('/secretaries/register', [SecretaryController::class, 'register']);

// Protected (avec auth:sanctum)
Route::apiResource('doctors', DoctorController::class);
Route::apiResource('admins', AdminController::class);
Route::apiResource('secretaries', SecretaryController::class);
```

---

## 📝 ROUTES PERSONNALISÉES (Customs)

En plus des 7 routes automatiques, on peut créer des routes personnalisées:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Routes personnalisées
    Route::get('/patients/search/patients', [PatientController::class, 'search']);
    Route::get('/patients/{id}/appointments', [PatientController::class, 'getAppointments']);
    Route::get('/patients/{id}/consultations', [PatientController::class, 'getConsultations']);
    Route::get('/patients/{id}/medical-info', [PatientController::class, 'getMedicalInfo']);
});
```

### Exemples:

**GET /api/patients/search/patients?q=dupont**
- Cherche les patients contenant "dupont" dans le nom

**GET /api/patients/1/appointments**
- Récupère tous les rendez-vous du patient 1

**GET /api/patients/1/consultations**
- Récupère toutes les consultations du patient 1

---

## 🔄 FLUX COMPLET: Une Requête de Bout en Bout

### Utilisateur fait une requête GET /api/patients:

```
1. Client Postman
   ↓ Envoie: GET http://localhost:8000/api/patients
     Headers: Authorization: Bearer 1|abc123...
   
2. Web Server (Apache/Nginx)
   ↓ Reçoit la requête HTTP

3. Laravel Router (routes/api.php)
   ↓ Reconnaît: GET /api/patients
   ↓ Match avec: Route::apiResource('patients', PatientController::class)
   ↓ Détermine: Appeller PatientController@index()

4. Middleware auth:sanctum
   ↓ Vérifie le header Authorization
   ↓ Extrait le token: 1|abc123...
   ↓ Cherche dans personal_access_tokens
   ↓ Valide? OUI → Continue
   ↓ Valide? NON → Retourne 401 "Unauthenticated."

5. PatientController@index()
   ↓ Appelle: PatientService::getAllPatients()

6. PatientService
   ↓ Exécute: Patient::all()
   ↓ Récupère les patients de la BD

7. PatientModel
   ↓ Requête SQL: SELECT * FROM patients

8. Database (SQLite)
   ↓ Retourne les enregistrements

9. Service formatte la réponse
   ↓ Message + Count + Data

10. Controller retourne JSON
    ↓ Status 200 OK

11. Client Postman
    ↓ Affiche la réponse JSON
```

---

## 📊 TABLEAU RÉCAPITULATIF COMPLET

| # | Type | Endpoint | Méthode | Authentification | Rôle |
|---|------|----------|---------|------------------|------|
| 1 | Public | /api/login | POST | ❌ Non | Connexion |
| 2 | Public | /api/patients/register | POST | ❌ Non | Créer patient |
| 3 | Public | /api/doctors/register | POST | ❌ Non | Créer docteur |
| 4 | Public | /api/admins/register | POST | ❌ Non | Créer admin |
| 5 | Public | /api/secretaries/register | POST | ❌ Non | Créer secrétaire |
| 6 | Protected | /api/patients | GET | ✅ Oui | Lister patients |
| 7 | Protected | /api/patients | POST | ✅ Oui | Créer patient (alt) |
| 8 | Protected | /api/patients/{id} | GET | ✅ Oui | Détails patient |
| 9 | Protected | /api/patients/{id} | PUT | ✅ Oui | Modifier patient |
| 10 | Protected | /api/patients/{id} | DELETE | ✅ Oui | Supprimer patient |
| 11 | Protected | /api/doctors | GET | ✅ Oui | Lister docteurs |
| 12 | Protected | /api/doctors/{id} | GET | ✅ Oui | Détails docteur |
| 13 | Protected | /api/doctors/{id} | PUT | ✅ Oui | Modifier docteur |
| 14 | Protected | /api/doctors/{id} | DELETE | ✅ Oui | Supprimer docteur |
| 15 | Protected | /api/admins | GET | ✅ Oui | Lister admins |
| 16 | Protected | /api/admins/{id} | GET | ✅ Oui | Détails admin |
| 17 | Protected | /api/secretaries | GET | ✅ Oui | Lister secrétaires |
| 18 | Protected | /api/secretaries/{id} | GET | ✅ Oui | Détails secrétaire |

---

## ✅ CHECKLIST ROUTES

- [ ] Comprendre la différence entre routes publiques et protégées
- [ ] Comprendre comment le middleware auth:sanctum fonctionne
- [ ] Comprendre apiResource() et les 7 routes qu'il crée
- [ ] Savoir tester chaque route avec Postman
- [ ] Savoir chercher les erreurs (401, 404, 422, 500)

---

**Les routes sont le cœur de l'API - elles décident qui a accès à quoi et comment!** 🚀
