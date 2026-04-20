@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0">
            <div class="card-header bg-info text-white py-3 text-center">
                <h5 class="mb-0">Ajouter un Membre au Secrétariat</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('secretaries.store') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
                        <div class="col"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control" required></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Professionnel</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col"><label class="form-label">N° de Bureau</label><input type="text" name="office_number" class="form-control"></div>
                        <div class="col"><label class="form-label">Affectation (Service)</label><input type="text" name="assignment" class="form-control" placeholder="ex: Pédiatrie"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
                        <div class="col"><label class="form-label">Confirmation</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-info text-white">Créer le compte secrétaire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection