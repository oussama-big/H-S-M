@extends('layouts.app')

@section('title', 'Gestion du Secrétariat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Équipe du Secrétariat</h2>
    <a href="{{ route('secretaries.create') }}" class="btn btn-info text-white">➕ Nouvelle Secrétaire</a>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-info">
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>N° Bureau</th>
                    <th>Affectation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($secretaries as $secretary)
                <tr class="align-middle">
                    <td><strong>{{ $secretary->user->nom }} {{ $secretary->user->prenom }}</strong></td>
                    <td>{{ $secretary->user->email }}</td>
                    <td><span class="badge bg-light text-dark">Bur. {{ $secretary->office_number ?? 'N/A' }}</span></td>
                    <td>{{ $secretary->assignment ?? 'Général' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('secretaries.edit', $secretary->id) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                            <form action="{{ route('secretaries.destroy', $secretary->id) }}" method="POST" onsubmit="return confirm('Supprimer ce compte ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection