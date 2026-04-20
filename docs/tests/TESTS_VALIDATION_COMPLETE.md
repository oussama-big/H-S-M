# ✅ VALIDATION COMPLÈTE DU SYSTÈME

## 📊 MATRICE DE VÉRIFICATION COMPLÈTE

Ce document vous aide à vérifier que CHAQUE endpoint fonctionne correctement.

---

## PHASE 1: VÉRIFIER LES ROUTES PUBLIQUES

### ✅ Test Public Route 1: POST /api/patients/register

**Description:** Créer un nouveau patient

**URL:** `http://localhost:8000/api/patients/register`

**Méthode:** `POST`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "nom": "Patient",
  "prenom": "Test1",
  "email": "patient.test1@example.com",
  "password": "TestPass123!",
  "password_confirmation": "TestPass123!",
  "date_of_birth": "1990-01-01",
  "gender": "M",
  "blood_type": "O+",
  "telephone": "+212600000001"
}
```

**Statut Attendu:** `201 Created`

**Réponse Attendue:**
```json
{
  "message": "Patient registered successfully",
  "data": {
    "patient_id": 1,
    "numDossier": "PAT-...",
    "nom": "Patient",
    "email": "patient.test1@example.com"
  }
}
```

✅ **À faire:** Envoie cette requête et vérifie que tu reçois un Status 201

---

### ✅ Test Public Route 2: POST /api/doctors/register

**Description:** Créer un nouveau docteur

**URL:** `http://localhost:8000/api/doctors/register`

**Méthode:** `POST`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "nom": "Doctor",
  "prenom": "Test2",
  "email": "doctor.test2@example.com",
  "password": "DoctorPass123!",
  "password_confirmation": "DoctorPass123!",
  "specialization": "Cardiologie",
  "license_number": "LICENSE2024001",
  "telephone": "+212600000002"
}
```

**Statut Attendu:** `201 Created`

**Réponse Attendue:**
```json
{
  "message": "Doctor registered successfully",
  "data": {
    "doctor_id": 2,
    "nom": "Doctor",
    "email": "doctor.test2@example.com",
    "specialization": "Cardiologie",
    "license_number": "LICENSE2024001"
  }
}
```

✅ **À faire:** Envoie cette requête et vérifie Status 201

---

### ✅ Test Public Route 3: POST /api/admins/register

**Description:** Créer un administrateur

**URL:** `http://localhost:8000/api/admins/register`

**Méthode:** `POST`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "nom": "Admin",
  "prenom": "Test3",
  "email": "admin.test3@example.com",
  "password": "AdminPass123!",
  "password_confirmation": "AdminPass123!",
  "department": "Management",
  "permissions": "full",
  "telephone": "+212600000003"
}
```

**Statut Attendu:** `201 Created`

✅ **À faire:** Envoie et vérifie Status 201

---

### ✅ Test Public Route 4: POST /api/secretaries/register

**Description:** Créer une secrétaire

**URL:** `http://localhost:8000/api/secretaries/register`

**Méthode:** `POST`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "nom": "Secretary",
  "prenom": "Test4",
  "email": "secretary.test4@example.com",
  "password": "SecretaryPass123!",
  "password_confirmation": "SecretaryPass123!",
  "office_number": "Room-001",
  "assignment": "Appointments",
  "telephone": "+212600000004"
}
```

**Statut Attendu:** `201 Created`

✅ **À faire:** Envoie et vérifie Status 201

---

### ✅ Test Public Route 5: POST /api/login (PATIENT)

**Description:** Se connecter avec un patient pour obtenir le token

**URL:** `http://localhost:8000/api/login`

**Méthode:** `POST`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "email": "patient.test1@example.com",
  "password": "TestPass123!"
}
```

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Connexion réussie",
  "access_token": "1|VeryLongTokenString",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "email": "patient.test1@example.com",
    "nom": "Patient",
    "role": "PATIENT"
  }
}
```

✅ **À faire:**
1. Envoie cette requête
2. Vérifie Status 200
3. **Copie le `access_token`**
4. Ajoute en environment: `PATIENT_TOKEN = {ta valeur copiée}`

---

### ✅ Test Public Route 6: POST /api/login (DOCTOR)

**URL:** `http://localhost:8000/api/login`

**Body:**
```json
{
  "email": "doctor.test2@example.com",
  "password": "DoctorPass123!"
}
```

**Statut Attendu:** `200 OK`

✅ **À faire:**
1. Envoie
2. Copie le token
3. Ajoute en environment: `DOCTOR_TOKEN`

---

### ✅ Test Public Route 7: POST /api/login (ADMIN)

**URL:** `http://localhost:8000/api/login`

**Body:**
```json
{
  "email": "admin.test3@example.com",
  "password": "AdminPass123!"
}
```

✅ **À faire:**
1. Envoie
2. Copie le token
3. Ajoute en environment: `ADMIN_TOKEN`

---

### ✅ Test Public Route 8: POST /api/login (SECRETARY)

**URL:** `http://localhost:8000/api/login`

**Body:**
```json
{
  "email": "secretary.test4@example.com",
  "password": "SecretaryPass123!"
}
```

✅ **À faire:**
1. Envoie
2. Copie le token
3. Ajoute en environment: `SECRETARY_TOKEN`

---

## PHASE 2: VÉRIFIER LES ROUTES PROTÉGÉES - GET ALL

### ⚠️ IMPORTANT: Chaque requête ci-dessous DOIT avoir le header:
```
Authorization: Bearer {{TOKEN_VARIABLE}}
```

---

### ✅ Test Protected Route 1: GET /api/patients

**Description:** Récupérer TOUS les patients

**URL:** `http://localhost:8000/api/patients`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{PATIENT_TOKEN}}
Accept: application/json
```

**Body:** (vide pour GET)

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Patients retrieved successfully",
  "count": 1,
  "data": [
    {
      "id": 1,
      "numDossier": "PAT-...",
      "nom": "Patient",
      "prenom": "Test1",
      "email": "patient.test1@example.com",
      "date_of_birth": "1990-01-01",
      "gender": "M",
      "blood_type": "O+",
      "telephone": "+212600000001"
    }
  ]
}
```

✅ **À faire:**
1. Crée la requête GET
2. Ajoute le header `Authorization: Bearer {{PATIENT_TOKEN}}`
3. Clique Send
4. **Vérifie que tu reçois Status 200 (pas 401 "Unauthenticated.")**

---

### ✅ Test Protected Route 2: GET /api/doctors

**Description:** Récupérer TOUS les doctors

**URL:** `http://localhost:8000/api/doctors`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{DOCTOR_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Doctors retrieved successfully",
  "count": 1,
  "data": [
    {
      "id": 2,
      "nom": "Doctor",
      "prenom": "Test2",
      "email": "doctor.test2@example.com",
      "specialization": "Cardiologie",
      "license_number": "LICENSE2024001",
      "telephone": "+212600000002"
    }
  ]
}
```

✅ **À faire:**
1. GET `/api/doctors`
2. Header: `Authorization: Bearer {{DOCTOR_TOKEN}}`
3. Send
4. **Doit être Status 200**

---

### ✅ Test Protected Route 3: GET /api/admins

**URL:** `http://localhost:8000/api/admins`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{ADMIN_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Même processus

---

### ✅ Test Protected Route 4: GET /api/secretaries

**URL:** `http://localhost:8000/api/secretaries`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{SECRETARY_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Même processus

---

## PHASE 3: VÉRIFIER LES ROUTES PROTÉGÉES - GET BY ID

### ✅ Test Protected Route 5: GET /api/patients/1

**Description:** Récupérer un patient spécifique par ID

**URL:** `http://localhost:8000/api/patients/1`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{PATIENT_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Patient retrieved successfully",
  "data": {
    "id": 1,
    "numDossier": "PAT-00001",
    "nom": "Patient",
    "prenom": "Test1",
    "email": "patient.test1@example.com",
    "date_of_birth": "1990-01-01",
    "gender": "M",
    "blood_type": "O+",
    "telephone": "+212600000001"
  }
}
```

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 6: GET /api/doctors/2

**URL:** `http://localhost:8000/api/doctors/2`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{DOCTOR_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 7: GET /api/admins/3

**URL:** `http://localhost:8000/api/admins/3`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{ADMIN_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 8: GET /api/secretaries/4

**URL:** `http://localhost:8000/api/secretaries/4`

**Méthode:** `GET`

**Headers:**
```
Authorization: Bearer {{SECRETARY_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

## PHASE 4: VÉRIFIER LES ROUTES PROTÉGÉES - UPDATE

### ✅ Test Protected Route 9: PUT /api/patients/1

**Description:** Modifier un patient

**URL:** `http://localhost:8000/api/patients/1`

**Méthode:** `PUT`

**Headers:**
```
Authorization: Bearer {{PATIENT_TOKEN}}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "telephone": "+212611111111",
  "blood_type": "A+"
}
```

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Patient updated successfully",
  "data": {
    "id": 1,
    "nom": "Patient",
    "telephone": "+212611111111",
    "blood_type": "A+"
  }
}
```

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 10: PUT /api/doctors/2

**URL:** `http://localhost:8000/api/doctors/2`

**Méthode:** `PUT`

**Headers:**
```
Authorization: Bearer {{DOCTOR_TOKEN}}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "specialization": "Neurologie",
  "telephone": "+212622222222"
}
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 11: PUT /api/admins/3

**URL:** `http://localhost:8000/api/admins/3`

**Méthode:** `PUT`

**Headers:**
```
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "department": "IT Management",
  "permissions": "restricted"
}
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 12: PUT /api/secretaries/4

**URL:** `http://localhost:8000/api/secretaries/4`

**Méthode:** `PUT`

**Headers:**
```
Authorization: Bearer {{SECRETARY_TOKEN}}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "office_number": "Room-002",
  "assignment": "Calls Manager"
}
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

## PHASE 5: VÉRIFIER LES ROUTES PROTÉGÉES - DELETE

### ✅ Test Protected Route 13: DELETE /api/secretaries/4

**Description:** Supprimer une secrétaire

**URL:** `http://localhost:8000/api/secretaries/4`

**Méthode:** `DELETE`

**Headers:**
```
Authorization: Bearer {{SECRETARY_TOKEN}}
Accept: application/json
```

**Body:** (vide)

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Secretary deleted successfully"
}
```

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 14: DELETE /api/admins/3

**URL:** `http://localhost:8000/api/admins/3`

**Méthode:** `DELETE`

**Headers:**
```
Authorization: Bearer {{ADMIN_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 15: DELETE /api/doctors/2

**URL:** `http://localhost:8000/api/doctors/2`

**Méthode:** `DELETE`

**Headers:**
```
Authorization: Bearer {{DOCTOR_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

✅ **À faire:** Envoie et vérifie Status 200

---

### ✅ Test Protected Route 16: DELETE /api/patients/1

**Description:** Supprimer un patient (AVEC CASCADE DELETE)

**URL:** `http://localhost:8000/api/patients/1`

**Méthode:** `DELETE`

**Headers:**
```
Authorization: Bearer {{PATIENT_TOKEN}}
Accept: application/json
```

**Statut Attendu:** `200 OK`

**Réponse Attendue:**
```json
{
  "message": "Patient deleted successfully"
}
```

⚠️ **Important:** Cela supprime aussi:
- Tous les dossiers médicaux du patient
- Toutes les consultations
- Toutes les ordonnances

✅ **À faire:** Envoie et vérifie Status 200

---

## 📊 TABLEAU RÉCAPITULATIF DE TOUS LES TESTS

| # | Type | Endpoint | Méthode | Statut | Token |
|---|------|----------|---------|--------|-------|
| 1 | Public | /api/patients/register | POST | 201 | - |
| 2 | Public | /api/doctors/register | POST | 201 | - |
| 3 | Public | /api/admins/register | POST | 201 | - |
| 4 | Public | /api/secretaries/register | POST | 201 | - |
| 5 | Public | /api/login | POST | 200 | - |
| 6 | Public | /api/login | POST | 200 | - |
| 7 | Public | /api/login | POST | 200 | - |
| 8 | Public | /api/login | POST | 200 | - |
| 9 | Protected | /api/patients | GET | 200 | PATIENT |
| 10 | Protected | /api/doctors | GET | 200 | DOCTOR |
| 11 | Protected | /api/admins | GET | 200 | ADMIN |
| 12 | Protected | /api/secretaries | GET | 200 | SECRETARY |
| 13 | Protected | /api/patients/1 | GET | 200 | PATIENT |
| 14 | Protected | /api/doctors/2 | GET | 200 | DOCTOR |
| 15 | Protected | /api/admins/3 | GET | 200 | ADMIN |
| 16 | Protected | /api/secretaries/4 | GET | 200 | SECRETARY |
| 17 | Protected | /api/patients/1 | PUT | 200 | PATIENT |
| 18 | Protected | /api/doctors/2 | PUT | 200 | DOCTOR |
| 19 | Protected | /api/admins/3 | PUT | 200 | ADMIN |
| 20 | Protected | /api/secretaries/4 | PUT | 200 | SECRETARY |
| 21 | Protected | /api/secretaries/4 | DELETE | 200 | SECRETARY |
| 22 | Protected | /api/admins/3 | DELETE | 200 | ADMIN |
| 23 | Protected | /api/doctors/2 | DELETE | 200 | DOCTOR |
| 24 | Protected | /api/patients/1 | DELETE | 200 | PATIENT |

---

## ✅ CHECKLIST FINALE

- [ ] Routes publiques (1-8): TOUS les Status sont OK ✅
- [ ] GET All (9-12): Tous retournent des données ✅
- [ ] GET By ID (13-16): Tous retournent des détails ✅
- [ ] PUT/UPDATE (17-20): Tous retournent updated data ✅
- [ ] DELETE (21-24): Tous retournent success message ✅

**Si TOUS les checkboxes sont cochés: LE SYSTÈME EST 100% FONCTIONNEL! 🎉**

---

**Bonne chance! Suis ces étapes et tu testeras le système complet.** 🚀
