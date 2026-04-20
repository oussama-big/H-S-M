@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h5 class="mb-0">Nouvel Administrateur</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admins.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
                            <div class="col"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control" required></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Département</label>
                            <input type="text" name="department" class="form-control" placeholder="ex: Ressources Humaines">
                        </div>
                        <div class="row mb-3">
                            <div class="col"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
                            <div class="col"><label class="form-label">Confirmation</label><input type="password" name="password_confirmation" class="form-control" required></div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark">Enregistrer l'Admin</button>
                            <a href="{{ route('admins.index') }}" class="btn btn-link text-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection