# 🗂️ MODELS - GUIDE COMPLET

## 🎯 RÔLE DES MODELS

Les **Models** représentent les **tables de la base de données** comme des objets PHP.

### Métaphore:
Si la base de données était une armoire:
- **Table** = Tiroir physique avec des fiches
- **Model** = Classe qui représente ce tiroir en code
- **Record** = Une fiche dans le tiroir

### Avantages:
```php
// ❌ SANS Model (SQL brut)
$patient = DB::table('patients')->where('id', 1)->first();

// ✅ AVEC Model (Orienté objet)
$patient = Patient::find(1);
$patient->nom;
$patient->update(['telephone' => '+212...']);
```

---

## 📂 STRUCTURE

```
app/Models/
├── User.php              → Utilisateur (table principale)
├── Patient.php           → Patient (hérite de User)
├── Doctor.php            → Docteur (hérite de User)
├── Admin.php             → Admin (hérite de User)
├── Secretary.php         → Secrétaire (hérite de User)
├── Appointment.php       → Rendez-vous
├── Consultation.php      → Visite médicale
├── Ordonnance.php        → Prescription
├── DossierMedical.php    → Dossier patient
├── Cabinet.php           → Clinique
└── Notification.php      → Alertes
```

---

## 1️⃣ User.php - Le Modèle Parent

**Rôle:** Représenter tous les utilisateurs du système

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    // Les colonnes qu'on peut remplir en masse
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
    ];

    // Les colonnes à cacher lors de la sérialisation (JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casting automatique de certaines colonnes
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Hasher automatiquement
    ];

    /**
     * Relation: Un User peut avoir UN Patient
     * (si son role est PATIENT)
     */
    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * Relation: Un User peut avoir UN Doctor
     * (si son role est MEDECIN)
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Relation: Un User peut avoir UN Admin
     * (si son role est ADMIN)
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Relation: Un User peut avoir UNE Secretary
     * (si son role est SECRETAIRE)
     */
    public function secretary()
    {
        return $this->hasOne(Secretary::class);
    }

    /**
     * Accéder au nom complet (propriété calculée)
     * Usage: $user->full_name
     */
    public function getFullNameAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }
}
```

### 💡 Notions Clés Expliquées:

**1. $fillable:**
```php
protected $fillable = ['nom', 'prenom', 'email', 'password', 'role'];

// ✅ Autorisé (mass assignment)
User::create([
    'nom' => 'Dupont',
    'email' => 'dupont@test.com',
    'password' => 'pass123',
]);

// ❌ Non autorisé
User::create(['is_admin' => true]); // Erreur!
```

**2. $hidden:**
```php
protected $hidden = ['password', 'remember_token'];

// Quand on retourne l'utilisateur en JSON:
return $user; // Password n'apparaît PAS
```

**3. $casts:**
```php
protected $casts = [
    'password' => 'hashed', // Hasher automatiquement
    'email_verified_at' => 'datetime', // Convertir en objet DateTime
];

// Avant de sauvegarder:
$user->password = 'plaintext123';
$user->save();
// → Password sera automatiquement hashedizé en BD!
```

**4. Relationships (Relations):**
```php
public function patient()
{
    return $this->hasOne(Patient::class);
}

// Usage:
$user = User::find(1);
$patient = $user->patient; // Récupère son patient associé
```

---

## 2️⃣ Patient.php - Modèle Enfant (Héritage)

**Rôle:** Représenter les patients (spécialisation de User)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // Id = clé primaire ET clé étrangère vers users
    protected $primaryKey = 'id';
    public $incrementing = false; // L'ID n'est pas auto-incrémenté

    protected $fillable = [
        'id',
        'numDossier',
        'date_of_birth',
        'gender',
        'blood_type',
        'telephone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Relation: Un Patient a UN User (parent)
     * C'est l'inverse de $user->patient()
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    /**
     * Relation: Un Patient a PLUSIEURS Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relation: Un Patient a UN DossierMedical
     */
    public function dossier()
    {
        return $this->hasOne(DossierMedical::class);
    }

    /**
     * Relation: Un Patient a PLUSIEURS Consultations (via DossierMedical)
     */
    public function consultations()
    {
        return $this->hasManyThrough(
            Consultation::class,
            DossierMedical::class
        );
    }
}
```

### 🔄 Relations Expliquées:

```
USER                    PATIENT                  APPOINTMENT
id=1 ─────────────→    id=1 (FK users) ────────→  appointment#1
     1:1 hasOne         numDossier=PAT-1           patient_id=1
                        ...                        
                                                   appointment#2
                                                   patient_id=1
                                                   
                                                   1:N hasMany
```

---

## 3️⃣ Doctor.php

**Rôle:** Représenter les docteurs

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'speciality',
        'experience_years',
        'department',
    ];

    /**
     * Relation: Un Doctor a UN User (parent)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    /**
     * Relation: Un Doctor a PLUSIEURS Consultations
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Relation: Un Doctor a PLUSIEURS Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
```

---

## 4️⃣ Admin.php & Secretary.php

Structure similaire:

```php
// Admin.php
class Admin extends Model
{
    protected $fillable = ['id', 'department', 'permissions'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}

// Secretary.php
class Secretary extends Model
{
    protected $fillable = ['id', 'office_number', 'assignment'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
```

---

## 5️⃣ Appointment.php & Consultation.php

```php
// Appointment.php
class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'status',
        'reason',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    /**
     * Un Appointment appartient à UN Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Un Appointment appartient à UN Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}

// Consultation.php
class Consultation extends Model
{
    protected $fillable = [
        'dossier_medical_id',
        'doctor_id',
        'patient_id',
        'consultation_date',
        'diagnosis',
        'treatment',
        'notes',
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierMedical::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
```

---

## 6️⃣ DossierMedical.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierMedical extends Model
{
    protected $table = 'dossier_medicals'; // Nom correct (pluriel)
    
    protected $fillable = [
        'patient_id',
        'numDossier',
        'medical_history',
        'allergies',
        'chronic_diseases',
    ];

    /**
     * Un Dossier appartient à UN Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Un Dossier a PLUSIEURS Consultations
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Un Dossier a PLUSIEURS Ordonnances
     */
    public function ordonnances()
    {
        return $this->hasMany(Ordonnance::class);
    }
}
```

---

## 🔍 UTILISATION DES MODELS

### Créer:
```php
// Créer et sauvegarder en 1 ligne
$patient = Patient::create([
    'id' => 10,
    'numDossier' => 'PAT-00010',
    'date_of_birth' => '1990-01-15',
    'gender' => 'M',
    'blood_type' => 'O+',
]);
```

### Lire:
```php
// Récupérer par ID
$patient = Patient::find(1);

// Récupérer tous
$patients = Patient::all();

// Récupérer avec condition
$malePatientsOver18 = Patient::where('gender', 'M')
    ->where('date_of_birth', '<', now()->subYears(18))
    ->get();

// Récupérer avec relation
$patient = Patient::find(1);
$user = $patient->user; // Accéder au User associé
```

### Modifier:
```php
$patient = Patient::find(1);
$patient->blood_type = 'AB+';
$patient->save();

// OU
Patient::find(1)->update(['blood_type' => 'AB+']);
```

### Supprimer:
```php
Patient::find(1)->delete();

// OU
Patient::destroy(1); // Avec cascade delete!
```

### Relations:
```php
$patient = Patient::find(1);

// Accéder au user
$user = $patient->user;

// Accéder aux appointments
$appointments = $patient->appointments;

// Accéder au dossier médical
$dossier = $patient->dossier;

// Accéder aux consultations
$consultations = $patient->consultations;
```

---

## 📊 TABLEAU RELATIONS

| Model | Table | Relation | Inverse |
|-------|-------|----------|---------|
| Patient | patients | belongs_to User | User has_one Patient |
| Patient | patients | has_many Appointment | Appointment belongs_to Patient |
| Doctor | doctors | belongs_to User | User has_one Doctor |
| Doctor | doctors | has_many Consultation | Consultation belongs_to Doctor |
| Consultation | consultations | belongs_to Patient | Patient has_many Consultation |

---

## ✅ CHECKLIST MODELS

- [ ] Comprendre la relation Model ↔ Table
- [ ] Comprendre $fillable et mass assignment
- [ ] Comprendre $hidden et $casts
- [ ] Comprendre les relations (hasOne, hasMany, belongsTo)
- [ ] Savoir accéder aux relations
- [ ] Comprendre l'héritage (User → Patient/Doctor/etc.)

---

## 🎯 RÉSUMÉ

| Concept | Exemple |
|---------|---------|
| Créer | Patient::create([...]) |
| Lire 1 | Patient::find(1) |
| Lire tous | Patient::all() |
| Modifier | $patient->update([...]) |
| Supprimer | $patient->delete() |
| Relation | $patient->user |
| Condition | Patient::where(...)->get() |

---

**Les Models transforment les tables en objets vivants!** 🚀
