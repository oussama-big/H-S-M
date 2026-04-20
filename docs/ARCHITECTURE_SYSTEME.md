# 🏗️ ARCHITECTURE DU SYSTÈME - GUIDE COMPLET

## 🎯 OBJECTIF GLOBAL DU SYSTÈME

**Medical Cabinet System** (H-S-M) est une **plateforme médicale complète** pour:
- 🏥 **Patients:** S'enregistrer, consulter l'historique médical, prendre RDV
- 👨‍⚕️ **Docteurs:** Gérer patients, créer consultations, prescrire ordonnances
- 📋 **Admins:** Gérer docteurs, admins, configurations
- 📞 **Secrétaires:** Gérer RDV, notifications, dossiers

---

## 📐 ARCHITECTURE EN COUCHES (Layered Architecture)

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (POSTMAN/APP)                  │
│              (Frontend Mobile/Web - Futur)               │
└────────────────────┬────────────────────────────────────┘
                     │ HTTP Requests (JSON)
                     ↓
┌─────────────────────────────────────────────────────────┐
│                    API Layer (Routes)                    │
│                  routes/api.php                          │
│  - POST /api/login                                       │
│  - POST /api/patients/register                           │
│  - GET /api/patients (protected)                         │
│  - etc...                                                 │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│             CONTROLLER Layer (HTTP Handler)              │
│              app/Http/Controllers/                       │
│  - AuthController.php                                    │
│  - PatientController.php                                 │
│  - DoctorController.php                                  │
│  - AdminController.php                                   │
│  - SecretaryController.php                              │
│                                                           │
│  Rôle: Recevoir requête → Valider → Appeler Service     │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│              SERVICE Layer (Business Logic)              │
│                app/Services/                             │
│  - UserService.php (Créer utilisateurs)                 │
│  - AuthService.php (Authentification)                   │
│  - PatientService.php (Logique patient)                 │
│  - DoctorService.php (Logique docteur)                  │
│  - AdminService.php (Logique admin)                     │
│  - SecretaryService.php (Logique secrétaire)            │
│                                                           │
│  Rôle: Logique métier, validation, transactions         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│            REPOSITORY Layer (Data Access)                │
│              app/Repositories/                           │
│  - PatientRepository.php (Requêtes patients)            │
│  - DoctorRepository.php (Requêtes docteurs)             │
│  - etc...                                                 │
│                                                           │
│  Rôle: Encapsuler les requêtes à la BD                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│               MODEL Layer (Database Entities)            │
│                 app/Models/                              │
│  - User.php (Table: users)                              │
│  - Patient.php (Table: patients)                        │
│  - Doctor.php (Table: doctors)                          │
│  - Admin.php (Table: admins)                            │
│  - Secretary.php (Table: secretaries)                   │
│  - Appointment.php (Table: appointments)                │
│  - Consultation.php (Table: consultations)              │
│  - Ordonnance.php (Table: ordonnances)                  │
│  - DossierMedical.php (Table: dossier_medicals)        │
│  - Cabinet.php (Table: cabinets)                        │
│  - Notification.php (Table: notifications)              │
│                                                           │
│  Rôle: Représenter tables en objets PHP                 │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│            DATABASE Layer (Persistent Storage)           │
│              database/database.sqlite                    │
│                                                           │
│  Tables:                                                  │
│  - users (id, email, password, role, etc.)              │
│  - patients (id [FK users], numDossier, etc.)           │
│  - doctors (id [FK users], department, etc.)            │
│  - admins (id [FK users], permissions, etc.)            │
│  - secretaries (id [FK users], office, etc.)            │
│  - appointments, consultations, ordonnances...          │
│  - notifications, dossier_medicals...                   │
│                                                           │
└─────────────────────────────────────────────────────────┘
```

---

## 🔐 SYSTÈME D'AUTHENTIFICATION (Sanctum)

```
Utilisateur                      API                        Base de Données
    │                             │                              │
    │──1. POST /api/login ────→  │                              │
    │   (email, password)        │                              │
    │                            │──2. SELECT * FROM users──→  │
    │                            │   WHERE email=...            │
    │                            │←────────────────────────────│
    │                            │   (user trouvé)              │
    │                            │                              │
    │                            │─3. Vérifier password─────→  │
    │                            │  hash_check(pass, bd_pass)   │
    │                            │  Résultat: TRUE              │
    │                            │                              │
    │                            │──4. Créer token────────────→│
    │                            │ INSERT INTO                  │
    │                            │ personal_access_tokens       │
    │                            │ (user_id, token, name, ...)  │
    │                            │←────────────────────────────│
    │                            │  Token: 1|abcd1234...        │
    │←──5. Retourner token ──── │                              │
    │    + user data              │                              │
    │                             │                              │
    │──6. GET /api/patients ──→  │                              │
    │    Authorization:           │                              │
    │    Bearer 1|abcd1234...     │                              │
    │                            │──7. Vérifier token ────────→│
    │                            │ SELECT * FROM                │
    │                            │ personal_access_tokens       │
    │                            │ WHERE token=...              │
    │                            │←────────────────────────────│
    │                            │  Token valide? OUI           │
    │                            │                              │
    │←──8. Données patients ─── │                              │
    │    (Status 200 OK)          │                              │
    │                             │                              │
    │──9. DELETE token ────────→  │                              │
    │    (logout)                 │                              │
    │                            │──10. Supprimer token ─────→ │
    │                            │  DELETE FROM                 │
    │                            │  personal_access_tokens      │
    │                            │  WHERE token=...             │
    │                            │←────────────────────────────│
    │                             │                              │
```

---

## 👥 MODÈLE DE RÔLES (Role-Based Access Control - RBAC)

```
User (Table Principale)
├── Role: PATIENT
│   └── Patient (Hérite + spécialisation)
│       └── Peut: Voir RDV, Consulter dossier, Prendre RDV
│
├── Role: MEDECIN (DOCTOR)
│   └── Doctor (Hérite + spécialisation)
│       └── Peut: Voir patients, Créer consultations, Prescrire
│
├── Role: ADMIN
│   └── Admin (Hérite + spécialisation)
│       └── Peut: Gérer doctors, admins, configurations
│
└── Role: SECRETAIRE (SECRETARY)
    └── Secretary (Hérite + spécialisation)
        └── Peut: Gérer RDV, Notifications, Support
```

### 💡 Notions Clés - Héritage:

**Sans héritage:** On aurait besoin de 4 tables avec beaucoup de colonnes vides
```
admins:       id, nom, email, password, dept, permissions, ...
doctors:      id, nom, email, password, dept, specialty, ...
patients:     id, nom, email, password, age, blood_type, ...
```

**Avec héritage (Notre approche):**
```
users:        id, nom, email, password, role, ...
admins:       id (FK users), permissions, ...
doctors:      id (FK users), specialty, ...
patients:     id (FK users), blood_type, ...
```

→ **Résultat:** Moins de duplication, plus d'organisation

---

## 📊 DIAGRAMME DES RELATIONS

```
┌──────────────┐
│    USERS     │ (id, email, password, role, created_at)
└──────┬───────┘
       │ 1:1
       ├──────────────┬──────────────┬─────────────┐
       │              │              │             │
       ↓              ↓              ↓             ↓
   ┌───────┐    ┌────────┐    ┌─────────┐   ┌────────────┐
   │PATIENT│    │DOCTOR  │    │  ADMIN  │   │ SECRETARY  │
   └──┬────┘    └────┬───┘    └─────────┘   └────────────┘
      │              │
      │1:N           │1:N
      ├──────────┬───┼──────────┬───────────┐
      │          │   │          │           │
      ↓          ↓   ↓          ↓           ↓
  ┌────────┐ ┌──────────────┐ ┌────────────────┐ ┌─────────────┐
  │APPOINT-│ │ DOSSIER_     │ │ CONSULTATION   │ │ ORDONNANCE  │
  │MENT    │ │ MEDICAL      │ │                │ │             │
  └────────┘ └──────────────┘ └────────────────┘ └─────────────┘
      │              │              │                 │
      └──────────────┴──────────────┼─────────────────┘
                                    │
      ┌─────────────────────────────┴─────────────────────┐
      │                                                   │
      ↓                                                   ↓
┌──────────────┐                              ┌─────────────────┐
│NOTIFICATION  │                              │  CABINET        │
│ (Rappels RDV)│                              │  (Clinique)     │
└──────────────┘                              └─────────────────┘
```

### Légende:
- **1:1** = Un user a UN patient, UN docteur, UN admin OU UN secretary
- **1:N** = Un patient a PLUSIEURS appointments, consultations
- **FK** = Foreign Key (référence à une autre table)

---

## 🔄 FLUX DE DONNÉES - EXEMPLE: UN PATIENT PREND UN RDV

```
1. UTILISATEUR (Postman)
   POST /api/appointments
   {
     "patient_id": 1,
     "doctor_id": 2,
     "appointment_date": "2026-04-25 14:30"
   }
   ↓
   
2. ROUTE MATCHER (routes/api.php)
   POST /api/appointments → AppointmentController@store()
   ↓
   
3. MIDDLEWARE (auth:sanctum)
   Valider token du patient
   ✅ Token valide? Continuer
   ❌ Token invalide? Retourner 401
   ↓
   
4. CONTROLLER (AppointmentController@store)
   a) Valider les données
   b) Appeler: AppointmentService::createAppointment()
   ↓
   
5. SERVICE (AppointmentService)
   a) Vérifier si patient existe
   b) Vérifier si doctor existe
   c) Vérifier si créneau disponible
   d) Appeler: AppointmentRepository::store()
   ↓
   
6. REPOSITORY (AppointmentRepository)
   $appointment = Appointment::create([
     'patient_id' => 1,
     'doctor_id' => 2,
     'appointment_date' => '2026-04-25 14:30',
     'status' => 'SCHEDULED'
   ]);
   return $appointment;
   ↓
   
7. MODEL (Appointment Model)
   Appointment::create() → Envoyer INSERT à la BD
   ↓
   
8. DATABASE (SQLite)
   INSERT INTO appointments 
   (patient_id, doctor_id, appointment_date, status, created_at)
   VALUES (1, 2, '2026-04-25 14:30', 'SCHEDULED', now())
   ↓
   
9. RETOUR EN ARRIÈRE
   Repository → Service → Controller → Route → Client
   JSON Response (201 Created):
   {
     "message": "Appointment created",
     "appointment_id": 15,
     "status": "SCHEDULED"
   }
```

---

## 🗃️ DÉTAIL DES FICHIERS CLÉS

### 1️⃣ CONFIGURATION GÉNÉRALE
```
config/
├── app.php          → Configuration App (timezone, etc.)
├── auth.php         → Configuration authentification
├── database.php     → Configuration BD (SQLite)
├── services.php     → Configuration services externes
└── queue.php        → Configuration jobs en queue
```

### 2️⃣ MIGRATIONS (Schéma Base de Données)
```
database/migrations/
├── 0001_01_01_000000_create_users_table.php
│   └── Crée table USERS (tous les rôles)
├── 2026_04_06_122731_create_patients_table.php
│   └── Crée table PATIENTS (hérite de users)
├── 2026_04_06_123007_create_doctors_table.php
│   └── Crée table DOCTORS
├── 2026_04_20_000001_create_admins_table.php
│   └── Crée table ADMINS
├── 2026_04_20_000002_create_secretaries_table.php
│   └── Crée table SECRETARIES
└── ... (appointments, consultations, ordonnances, etc.)
```

### 3️⃣ MODELS (Représentation Objet)
```
app/Models/
├── User.php              → Utilisateur principal
├── Patient.php           → Spécialisation Patient
├── Doctor.php            → Spécialisation Doctor
├── Admin.php             → Spécialisation Admin
├── Secretary.php         → Spécialisation Secretary
├── Appointment.php       → Rendez-vous
├── Consultation.php      → Visite médicale
├── Ordonnance.php        → Prescription médicale
├── DossierMedical.php    → Dossier patient
├── Notification.php      → Alertes/Rappels
└── Cabinet.php           → Clinique/Cabinet
```

### 4️⃣ SERVICES (Logique Métier)
```
app/Services/
├── UserService.php       → Créer/Modifier utilisateurs
├── AuthService.php       → Authentification
├── PatientService.php    → Logique métier patient
├── DoctorService.php     → Logique métier docteur
├── AdminService.php      → Logique métier admin
└── SecretaryService.php  → Logique métier secrétaire
```

### 5️⃣ CONTROLLERS (Gestion Requêtes)
```
app/Http/Controllers/
├── AuthController.php
├── PatientController.php
├── DoctorController.php
├── AdminController.php
└── SecretaryController.php
```

### 6️⃣ ROUTES (Points d'Entrée)
```
routes/
├── api.php              → Toutes les routes API
├── web.php              → Routes web (peu utilisées)
└── console.php          → Commandes CLI
```

---

## 🔐 SÉCURITÉ

### 1. Authentification (Sanctum)
```
✅ Chaque requête protégée vérifie: Authorization: Bearer {token}
✅ Tokens stockés dans: personal_access_tokens
✅ Tokens expirables et révocables
```

### 2. Validation des Données
```
✅ Chaque contrôleur valide les données:
   - Email unique
   - Password fort (8+ caractères)
   - Types corrects (email, date, etc.)
```

### 3. Hash des Passwords
```
✅ Passwords hasées avec bcrypt (hash_make)
✅ Pas de plaintext dans la BD
✅ Vérification: hash_check(password, hashed)
```

### 4. Cascade Delete
```
✅ Quand patient est supprimé:
   - Son dossier médical est supprimé
   - Ses consultations sont supprimées
   - Ses ordonnances sont supprimées
```

---

## 📋 CHECKLIST ARCHITECTURE

- [ ] Comprendre les 6 couches du système
- [ ] Comprendre le rôle de chaque fichier
- [ ] Comprendre l'authentification Sanctum
- [ ] Comprendre le modèle de rôles (RBAC)
- [ ] Comprendre les relations entre tables
- [ ] Savoir tracer un flux requête de bout en bout

---

**L'architecture est pensée pour être scalable, maintenable et sécurisée!** 🚀
