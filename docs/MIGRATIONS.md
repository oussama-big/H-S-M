# 🔧 MIGRATIONS - GUIDE COMPLET

## 🎯 RÔLE DES MIGRATIONS

Les **Migrations** sont des **scripts de contrôle de version pour la base de données**.

### Métaphore:
Si le code était versionné avec Git:
- **Git commits** = Historique du code
- **Migrations** = Historique de la base de données

### Pourquoi?
```
❌ MAUVAISE façon (pas de migrations):
- Dev 1: Crée table patients manuellement
- Dev 2: Crée table doctors manuellement
- Production: Oublie une colonne
- Désastre! 😱

✅ BONNE façon (avec migrations):
- Écrire code pour créer tables
- Version control automatique
- Reproductibilité 100%
- Rollback possible
- Déploiement garanti 🎉
```

---

## 📂 STRUCTURE

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php       ← Table principale
├── 0001_01_01_000001_create_cache_table.php
├── 0001_01_01_000002_create_jobs_table.php
├── 2026_04_06_122731_create_patients_table.php    ← Table spécialisée
├── 2026_04_06_123007_create_doctors_table.php
├── 2026_04_06_123016_create_appointments_table.php
├── 2026_04_06_152715_create_dossier_medicals_table.php
├── 2026_04_06_152722_create_consultations_table.php
├── 2026_04_06_152730_create_ordonnances_table.php
├── 2026_04_06_152738_create_notifications_table.php
├── 2026_04_06_152746_create_cabinets_table.php
├── 2026_04_20_000001_create_admins_table.php      ← NOUVELLES
└── 2026_04_20_000002_create_secretaries_table.php ← NOUVELLES
```

---

## 1️⃣ USERS TABLE - La Table Principale

**Rôle:** Contenir TOUS les utilisateurs (PATIENT, MEDECIN, ADMIN, SECRETAIRE)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * up() = Créer la table (quand on lance migrate)
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Clé primaire
            $table->id();

            // Colonnes informations
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Enum du rôle (IMPORTANT!)
            $table->enum('role', ['ADMIN', 'MEDECIN', 'SECRETAIRE', 'PATIENT'])
                  ->default('PATIENT');

            $table->rememberToken();
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * down() = Supprimer la table (quand on lance migrate:rollback)
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 💡 Notions Clés:

**1. enum():**
```php
$table->enum('role', ['ADMIN', 'MEDECIN', 'SECRETAIRE', 'PATIENT'])
      ->default('PATIENT');

// Colonnes possibles: SEULEMENT ces 4 valeurs
// Défaut: PATIENT
// Avantage: BD vérifie la valeur (pas de garbage!)
```

**2. unique():**
```php
$table->string('email')->unique();

// Garantit: Pas 2 emails identiques dans la table
// Exception 409 Conflict si duplication
```

**3. nullable():**
```php
$table->timestamp('email_verified_at')->nullable();

// Cette colonne peut être NULL
// Par défaut sans nullable(), elle est obligatoire
```

**4. timestamps():**
```php
$table->timestamps();

// Crée automatiquement 2 colonnes:
// - created_at (quand l'enregistrement a été créé)
// - updated_at (quand modifié la dernière fois)
```

---

## 2️⃣ PATIENTS TABLE - Table Spécialisée

**Rôle:** Etendre la table users pour les patients

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            // 🔑 Foreign Key vers users (héritage)
            $table->foreignId('id')
                  ->primary()                    // C'est aussi la clé primaire
                  ->constrained('users')          // FK vers table users
                  ->onDelete('cascade');          // Si user supprimé → patient supprimé aussi

            // Colonnes spécifiques au patient
            $table->string('numDossier')->unique();
            $table->date('date_of_birth');
            $table->enum('gender', ['M', 'F']);
            $table->string('blood_type');
            $table->string('telephone');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
```

### 💡 Notions Clés:

**1. foreignId() + primary():**
```php
$table->foreignId('id')
      ->primary()
      ->constrained('users')
      ->onDelete('cascade');

// foreignId('id') = Crée une colonne 'id' qui est FK
// ->primary() = C'est aussi la clé primaire (1:1 avec users)
// ->constrained('users') = La FK pointe vers users.id
// ->onDelete('cascade') = Si user supprimé, patient supprimé aussi
```

**2. Héritage avec FK:**
```
users table:        patients table:
id=1 ————————→      id=1 (FK users.id)
id=2 ————————→      id=2 (FK users.id)

Résultat:
- Chaque patient a exactement UN user
- On n'a pas de colonnes dupliquées (nom, email, etc.)
```

**3. CASCADE DELETE:**
```
DELETE FROM users WHERE id=1;
↓
BD exécute automatiquement:
  DELETE FROM patients WHERE id=1;
  DELETE FROM dossier_medicals WHERE patient_id=1;
  DELETE FROM appointments WHERE patient_id=1;
  etc...

Résultat: Pas d'orphelins!
```

---

## 3️⃣ DOCTORS TABLE

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->foreignId('id')
                  ->primary()
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('speciality');           // Cardiologie, Neurologie, etc.
            $table->integer('experience_years');    // 15 ans, 20 ans, etc.
            $table->string('department')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
```

---

## 4️⃣ ADMINS TABLE (NOUVELLE)

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->foreignId('id')
                  ->primary()
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('department')->nullable();
            $table->string('permissions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
```

---

## 5️⃣ SECRETARIES TABLE (NOUVELLE)

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretaries', function (Blueprint $table) {
            $table->foreignId('id')
                  ->primary()
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('office_number')->nullable();
            $table->string('assignment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretaries');
    }
};
```

---

## 6️⃣ APPOINTMENTS TABLE (Relation Many-to-Many)

**Rôle:** Rendez-vous entre patient et docteur

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->onDelete('cascade');  // Si patient supprimé → RDV supprimé
            
            $table->foreignId('doctor_id')
                  ->constrained('doctors')
                  ->onDelete('cascade');  // Si doctor supprimé → RDV supprimé

            // Colonnes de données
            $table->dateTime('appointment_date');
            $table->enum('status', ['SCHEDULED', 'COMPLETED', 'CANCELLED'])
                  ->default('SCHEDULED');
            $table->text('reason')->nullable();

            $table->timestamps();

            // Index pour recherches rapides
            $table->index('patient_id');
            $table->index('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
```

### 💡 Relation Many-to-Many:
```
patients table:                 appointments table:         doctors table:
id=1 (Jean) ─────┐          patient_id=1, doctor_id=5     id=5 (Dupont)
            │    └──────→   appointment#1  ←──────┐        
            │                                      │
            │    ┌──────→   appointment#2          │
            │    │        patient_id=1, doctor_id=8│
            └────┤                                 ├──────→ id=8 (Martin)
                 │
id=2 (Pierre)    │
            ├────┴──────→   appointment#3
            │            patient_id=2, doctor_id=5
            └──────────→   appointment#4
                        patient_id=2, doctor_id=8

Résultat:
- 1 patient peut avoir PLUSIEURS appointments
- 1 doctor peut avoir PLUSIEURS appointments
- Tous les RDV sont enregistrés dans appointments
```

---

## 7️⃣ DOSSIER_MEDICALS TABLE

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossier_medicals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                  ->unique()  // Chaque patient a QU'1 dossier
                  ->constrained('patients')
                  ->onDelete('cascade');

            $table->string('numDossier')->unique();
            $table->text('medical_history')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_diseases')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_medicals');
    }
};
```

---

## 8️⃣ CONSULTATIONS TABLE

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dossier_medical_id')
                  ->constrained('dossier_medicals')
                  ->onDelete('cascade');

            $table->foreignId('doctor_id')
                  ->constrained('doctors')
                  ->onDelete('set null')->nullable();

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->onDelete('cascade');

            $table->dateTime('consultation_date');
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
```

---

## 💻 COMMENT LANCER LES MIGRATIONS?

### 1️⃣ Première fois (dev):
```bash
php artisan migrate
```

**Qu'est-ce qui se passe:**
1. Lit tous les fichiers de `database/migrations/`
2. Exécute les migrations qui n'ont jamais été lancées
3. Enregistre l'historique dans `migrations` table
4. Crée les tables dans la BD

**Résultat:**
```
✓ Migrated: 0001_01_01_000000_create_users_table
✓ Migrated: 2026_04_06_122731_create_patients_table
✓ Migrated: 2026_04_06_123007_create_doctors_table
✓ Migrated: 2026_04_20_000001_create_admins_table
... (toutes les migrations)
```

### 2️⃣ Ajouter de nouvelles migrations (après qu'elles existent):
```bash
php artisan migrate
```

**Seules** les migrations non-lancées seront exécutées

### 3️⃣ Rollback (annuler la dernière migration):
```bash
php artisan migrate:rollback

# Rollback 2 dernières
php artisan migrate:rollback --steps=2

# Rollback TOUT (attention!)
php artisan migrate:reset
```

### 4️⃣ Fresh + Seed (recommended pour dev):
```bash
php artisan migrate:fresh --seed
```

**Qu'est-ce qui se passe:**
1. ❌ Supprime TOUTES les tables
2. ✅ Recrée les tables (migrations)
3. ✅ Remplit les données (seeders)

---

## 🔍 TYPES DE COLONNES

| Type | Usage | Exemple |
|------|-------|---------|
| id() | Clé primaire auto-increment | $table->id() |
| string() | Texte court | $table->string('email') |
| text() | Texte long | $table->text('diagnosis') |
| integer() | Nombre entier | $table->integer('age') |
| enum() | Liste de valeurs | $table->enum('role', [...]) |
| date() | Date (YYYY-MM-DD) | $table->date('date_of_birth') |
| dateTime() | Date+Heure | $table->dateTime('created_at') |
| boolean() | Vrai/Faux | $table->boolean('active') |
| timestamps() | created_at + updated_at | $table->timestamps() |
| foreignId() | Clé étrangère | $table->foreignId('user_id') |

---

## 🔒 CONTRAINTES IMPORTANTES

| Contrainte | Rôle | Exemple |
|------------|------|---------|
| primary() | Clé primaire | ->primary() |
| unique() | Valeur unique | ->unique() |
| nullable() | Peut être NULL | ->nullable() |
| default() | Valeur par défaut | ->default('PATIENT') |
| constrained() | FK vers table | ->constrained('users') |
| onDelete() | Comportement suppression | ->onDelete('cascade') |

---

## 📊 DIAGRAMME COMPLET DES RELATIONS

```
users (Main Table)
├── id (PK)
├── role: PATIENT/MEDECIN/ADMIN/SECRETAIRE
└── ...

    ├─→ patients (1:1 via id FK)
    │   ├── id (PK, FK users)
    │   ├── numDossier
    │   └── ...
    │   
    │   ├─→ dossier_medicals (1:1)
    │   │   ├── id (PK)
    │   │   ├── patient_id (FK)
    │   │   └── ...
    │   │   
    │   │   ├─→ consultations (1:N)
    │   │   │   ├── id (PK)
    │   │   │   ├── dossier_medical_id (FK)
    │   │   │   └── ...
    │   │   │
    │   │   └─→ ordonnances (1:N)
    │   │       └── ...
    │   │
    │   └─→ appointments (1:N)
    │       ├── id (PK)
    │       ├── patient_id (FK)
    │       ├── doctor_id (FK)
    │       └── ...
    │
    ├─→ doctors (1:1 via id FK)
    │   ├── id (PK, FK users)
    │   ├── speciality
    │   └── ...
    │   
    │   ├─→ consultations (1:N)
    │   └─→ appointments (1:N)
    │
    ├─→ admins (1:1 via id FK)
    │   └── ...
    │
    └─→ secretaries (1:1 via id FK)
        └── ...
```

---

## ✅ CHECKLIST MIGRATIONS

- [ ] Comprendre le rôle des migrations
- [ ] Comprendre up() et down()
- [ ] Comprendre foreignId() et constrained()
- [ ] Comprendre cascade delete
- [ ] Comprendre enum()
- [ ] Savoir lancer `php artisan migrate`
- [ ] Savoir faire `php artisan migrate:rollback`
- [ ] Savoir créer une nouvelle migration

---

## 🎯 RÉSUMÉ

| Commande | Effet |
|----------|-------|
| migrate | Exécuter migrations |
| migrate:rollback | Annuler dernière migration |
| migrate:fresh | Supprimer tout et recommencer |
| migrate:fresh --seed | Fresh + remplir avec seeders |
| make:migration create_X_table | Créer migration |

---

**Les Migrations garantissent la cohérence et la reproductibilité de la BD!** 🚀
