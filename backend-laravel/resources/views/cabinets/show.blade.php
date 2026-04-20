@extends('layouts.app')

@section('content')
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('cabinets.index') }}">Cabinets</a></li>
    <li class="breadcrumb-item active">{{ $cabinet->name }}</li>
  </ol>
</nav>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">Infos Contact</div>
            <div class="card-body">
                <p><strong>Email :</strong> {{ $cabinet->email ?? 'N/A' }}</p>
                <p><strong>Tél :</strong> {{ $cabinet->telephone ?? 'N/A' }}</p>
                <p><strong>Adresse :</strong><br>{{ $cabinet->address }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">Médecins affectés à ce cabinet</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($cabinet->doctors as $doctor)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Dr. {{ $doctor->user->nom }} {{ $doctor->user->prenom }}
                            <span class="text-muted small">{{ $doctor->specialization }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center">Aucun médecin affecté pour le moment.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection