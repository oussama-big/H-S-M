# 🌱 SEEDERS (DONNÉES DE TEST) - GUIDE COMPLET

## 🎯 RÔLE DES SEEDERS

Les **Seeders** sont des scripts qui **remplissent la base de données avec des données de test**. 

### 💡 Pourquoi?
- ✅ Tester l'application sans créer manuellement 100 utilisateurs
- ✅ Avoir une base de données "réaliste" avec données pré-remplies
- ✅ Reproductibilité (chaque fois qu'on run, même données)
- ✅ Development plus rapide
- ✅ Tests automatisés peuvent utiliser ces données

### Notions Clés:
- **Factory:** Générateur qui crée 1 enregistrement aléatoire
- **Seeder:** Script qui utilise des Factories pour remplir la BD
- **Fake Data:** Données générées aléatoirement (noms, emails, etc.)

---

## 📂 STRUCTURE DES SEEDERS

```
database/
├── factories/
│   └── UserFactory.php          → Factory pour créer utilisateurs
│
└── seeders/
    └── DatabaseSeeder.php       → Orchestrateur principal
```

---

## 1️⃣ FACTORY: UserFactory.php

**Rôle:** Créer UN utilisateur aléatoire avec données réalistes

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'role' => fake()->randomElement(['PATIENT', 'MEDECIN', 'ADMIN', 'SECRETAIRE']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
```

### 💡 Notions Clés Expliquées:

**1. fake():**
```php
fake()->lastName()           → "Dupont", "Martin", "Bernard"
fake()->firstName()          → "Jean", "Marie", "Pierre"
fake()->safeEmail()          → "john123@example.com"
fake()->randomElement([...]) → Choisir aléatoirement dans une liste
```

**2. Hash::make():**
```php
// Le password n'est PAS stocké en plaintext!
password: "password123"
↓
Hash::make("password123")
↓
$2y$10$abcd1234...xyz  (hash bcrypt)
```

**3. unique():**
```php
// Garantit que les emails sont uniques
fake()->unique()->safeEmail()
// Pas de doublons!
```

### 📝 Utilisation:

```php
// Créer 1 utilisateur
User::factory()->create();

// Créer 5 utilisateurs
User::factory()->count(5)->create();

// Créer 10 patients
User::factory()
    ->count(10)
    ->state(['role' => 'PATIENT'])
    ->create();
```

---

## 2️⃣ SEEDER: DatabaseSeeder.php

**Rôle:** Orchestrateur principal - dit ce qu'il faut créer et combien

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer 1 admin
        $admin = User::factory()
            ->state(['role' => 'ADMIN', 'email' => 'admin@cabinet.com'])
            ->create();

        // 2. Créer 5 docteurs
        $doctors = User::factory()
            ->count(5)
            ->state(['role' => 'MEDECIN'])
            ->afterCreating(function (User $user) {
                Doctor::create([
                    'id' => $user->id,
                    'speciality' => 'Cardiologie',
                    'experience_years' => rand(5, 30),
                ]);
            })
            ->create();

        // 3. Créer 20 patients
        $patients = User::factory()
            ->count(20)
            ->state(['role' => 'PATIENT'])
            ->afterCreating(function (User $user) {
                Patient::create([
                    'id' => $user->id,
                    'numDossier' => 'PAT-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'date_of_birth' => fake()->dateTimeBetween('-80 years', '-18 years'),
                    'gender' => fake()->randomElement(['M', 'F']),
                    'blood_type' => fake()->randomElement(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']),
                    'telephone' => fake()->phoneNumber(),
                ]);
            })
            ->create();

        // 4. Créer 2 secrétaires
        $secretaries = User::factory()
            ->count(2)
            ->state(['role' => 'SECRETAIRE'])
            ->afterCreating(function (User $user) {
                Secretary::create([
                    'id' => $user->id,
                    'office_number' => 'Bureau ' . rand(1, 10),
                ]);
            })
            ->create();
    }
}
```

### 🔄 Flux d'Exécution:

```
DatabaseSeeder::run()
│
├─ Créer 1 Admin
│  └─ User::factory()
│     → role='ADMIN'
│     → email='admin@cabinet.com'
│     → create() → INSERT dans users table
│
├─ Créer 5 Docteurs
│  └─ Pour chaque docteur:
│     1) User::factory() avec role='MEDECIN'
│     2) INSERT dans users
│     3) INSERT dans doctors (afterCreating)
│
├─ Créer 20 Patients
│  └─ Pour chaque patient:
│     1) User::factory() avec role='PATIENT'
│     2) INSERT dans users
│     3) INSERT dans patients (afterCreating)
│     4) Générer numDossier automatique
│     5) Choisir aléatoirement genre et groupe sanguin
│
└─ Créer 2 Secrétaires
   └─ Pour chaque secrétaire:
      1) User::factory() avec role='SECRETAIRE'
      2) INSERT dans users
      3) INSERT dans secretaries (afterCreating)
```

---

## 💻 COMMENT LANCER LES SEEDERS?

### Option 1: Fresh + Seed (RECOMMANDÉ pour développement)
```bash
php artisan migrate:fresh --seed
```

**Qu'est-ce qui se passe:**
1. ❌ Supprime TOUTES les tables
2. ✅ Recrée TOUTES les tables (migrations)
3. ✅ Remplit les tables (seeders)
4. ✨ Base complètement neuve et remplie

**Résultat:** 28 enregistrements (1 admin + 5 doctors + 20 patients + 2 secretaries)

### Option 2: Seed Seulement (sans supprimer)
```bash
php artisan db:seed
```

**Utiliser si:** Les tables existent déjà, on veut juste ajouter des données

### Option 3: Seed avec Seeder Spécifique
```bash
php artisan db:seed --class=DatabaseSeeder
```

---

## 📊 DONNÉES GÉNÉRÉES (Exemple)

### Users Table (Après Seed):
```
id | nom        | prenom | email                  | role      | password
1  | Lefebvre   | Sophie | sophie@example.com     | ADMIN     | $2y$10$...
2  | Dupont     | Jean   | jean.dupont@example... | MEDECIN   | $2y$10$...
3  | Martin     | Pierre | pierre.martin@exam...  | MEDECIN   | $2y$10$...
4  | Bernard    | Marie  | marie@example.com      | PATIENT   | $2y$10$...
5  | Petit      | Marc   | marc.petit@example...  | PATIENT   | $2y$10$...
...
```

### Doctors Table (Après Seed):
```
id | speciality  | experience_years | created_at
2  | Cardiologie | 15               | 2026-04-20 10:00:00
3  | Neurologie  | 8                | 2026-04-20 10:00:01
...
```

### Patients Table (Après Seed):
```
id | numDossier | date_of_birth | gender | blood_type | telephone
4  | PAT-00004  | 1985-03-20    | M      | O+         | +212612345...
5  | PAT-00005  | 1992-07-15    | F      | A-         | +212698765...
...
```

---

## 🚀 POUR TESTER L'APPLICATION

### Après seed, tu as:

**Credentials pour Tests:**

```json
{
  "admin": {
    "email": "admin@cabinet.com",
    "password": "password123"
  },
  "doctor": {
    "email": "jean.dupont@example.com (ou autre du seeder)",
    "password": "password123"
  },
  "patient": {
    "email": "marie@example.com (ou autre du seeder)",
    "password": "password123"
  }
}
```

### Test Postman:

```
1. POST /api/login
   Body: { "email": "admin@cabinet.com", "password": "password123" }
   
2. Copier le token retourné
   
3. GET /api/patients
   Headers: Authorization: Bearer {token}
   
4. Voir les 20 patients créés par le seeder!
```

---

## 🏭 CRÉER UN SEEDER PERSONNALISÉ

### Exemple: Seeder pour Appointments

```php
<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 50 rendez-vous aléatoires
        for ($i = 0; $i < 50; $i++) {
            Appointment::create([
                'patient_id' => Patient::inRandomOrder()->first()->id,
                'doctor_id' => Doctor::inRandomOrder()->first()->id,
                'appointment_date' => now()->addDays(rand(1, 30))->setTime(rand(9, 17), 0),
                'status' => 'SCHEDULED',
                'reason' => fake()->sentence(),
            ]);
        }
    }
}
```

### L'ajouter au DatabaseSeeder:

```php
public function run(): void
{
    // ... autres seeders ...
    
    // Ajouter à la fin:
    $this->call([
        AppointmentSeeder::class,
    ]);
}
```

---

## 🔄 NOTIONS AVANCÉES

### 1. afterCreating() - Hook

```php
->afterCreating(function (User $user) {
    // Exécuté APRÈS que l'utilisateur soit créé
    Doctor::create([
        'id' => $user->id,  // Utiliser l'ID qu'on vient de créer
        'speciality' => 'Cardiologie',
    ]);
})
```

### 2. state() - Surcharger des valeurs

```php
User::factory()
    ->state(['role' => 'ADMIN', 'email' => 'admin@cabinet.com'])
    ->create()
    // Crée un user avec role=ADMIN et email=admin@cabinet.com
    // Les autres champs (nom, prenom, etc.) sont aléatoires
```

### 3. count() - Créer plusieurs

```php
User::factory()->count(20)->create()
// Crée 20 utilisateurs
```

### 4. inRandomOrder() - Ordre aléatoire

```php
Patient::inRandomOrder()->first()
// Prendre UN patient aléatoire
```

---

## ✅ CHECKLIST SEEDERS

- [ ] Comprendre le rôle des Factories
- [ ] Comprendre le rôle des Seeders
- [ ] Savoir créer un seeder personnalisé
- [ ] Savoir lancer `php artisan migrate:fresh --seed`
- [ ] Savoir utiliser les données de test dans Postman
- [ ] Comprendre afterCreating() hook

---

## 🎯 RÉSUMÉ

| Concept | Rôle | Exemple |
|---------|------|---------|
| Factory | Générer 1 enregistrement aléatoire | User::factory()->create() |
| Seeder | Script qui crée plusieurs enregistrements | DatabaseSeeder::run() |
| fake() | Générer données aléatoires réalistes | fake()->name() |
| state() | Fixer certaines valeurs | ->state(['role' => 'ADMIN']) |
| afterCreating() | Hook après création | ->afterCreating(function...) |

---

**Les Seeders font gagner énormément de temps en développement!** 🚀
