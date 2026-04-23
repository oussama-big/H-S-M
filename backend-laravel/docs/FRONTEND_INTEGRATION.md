# Frontend Integration

## Goal

Le frontend finalise de `medicare-app` est maintenant integre directement dans `backend-laravel` afin d'utiliser un seul serveur Laravel (`php artisan serve`) avec les routes API existantes sous `/api/...`.

## Structure retenue

```text
backend-laravel/
├── app/
│   └── Http/
│       └── Controllers/
│           ├── Frontend/
│           │   └── PageController.php
│           ├── AuthController.php
│           ├── PatientController.php
│           └── DossierMedicalController.php
├── public/
│   └── frontend/
│       ├── css/
│       └── js/
├── resources/
│   └── views/
│       └── frontend/
│           ├── auth/
│           ├── doctor/
│           ├── layouts/
│           ├── pages/
│           └── dashboard.blade.php
└── routes/
    ├── web.php
    └── api.php
```

## Routing

- Routes frontend SSR:
  - `/`
  - `/a-propos`
  - `/services`
  - `/equipe`
  - `/temoignages`
  - `/rendez-vous`
  - `/connexion`
  - `/inscription`
  - `/dashboard`
  - `/profil`
  - `/doctor/*`

- Routes API conservees:
  - `/api/login`
  - `/api/patients/register`
  - `/api/user`
  - `/api/dossiers-medicaux/patient/{patient_id}/summary`
  - ainsi que les autres routes deja definies dans `routes/api.php`

## Integration technique

- Les assets frontend sont servis depuis `public/frontend/...`.
- Les vues frontend sont namespacées sous `resources/views/frontend/...` pour ne pas ecraser les vues backoffice existantes.
- Le JavaScript frontend ne cible plus une URL fixe; il utilise automatiquement le meme serveur Laravel via `window.location.origin` / `meta[name="api-base-url"]`.
- Le flux d'inscription patient cree maintenant:
  - `users`
  - `patients`
  - `dossier_medicals`
  dans une transaction unique.

## Notes

- `backoffice/dashboard` reste disponible pour le dashboard web legacy.
- Le frontend patient integre utilise l'authentification API Bearer token.
