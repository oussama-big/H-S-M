@extends('layouts.app')

@section('title', 'Gestion des Cabinets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Nos Cabinets & Cliniques</h2>
    <a href="{{ route('cabinets.create') }}" class="btn btn-primary">Ajouter un Cabinet</a>
</div>

<div class="row">
    @foreach($cabinets as $cabinet)
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-body">
                <h5 class="card-title text-primary">{{ $cabinet->name }}</h5>
                <p class="card-text text-muted">
                    📍 {{ $cabinet->address }}<br>
                    📞 {{ $cabinet->telephone ?? 'Non renseigné' }}
                </p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="badge bg-light text-dark">{{ $cabinet->doctors->count() }} Médecins</span>
                    <div class="btn-group">
                        <a href="{{ route('cabinets.show', $cabinet->id) }}" class="btn btn-sm btn-outline-info">Détails</a>
                        <a href="{{ route('cabinets.edit', $cabinet->id) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection