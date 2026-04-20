<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Inscription - HMS</title>
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4>Inscription au Système HMS</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="col"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control" required></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                        
                        <div class="mb-3">
                            <label class="form-label">Je suis un :</label>
                            <select name="role" class="form-select" required>
                                <option value="PATIENT">Patient</option>
                                <option value="MEDECIN">Médecin</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
                            <div class="col"><label class="form-label">Confirmation</label><input type="password" name="password_confirmation" class="form-control" required></div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 py-2">S'enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>