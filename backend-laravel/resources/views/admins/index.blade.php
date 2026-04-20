@extends('layouts.app')

@section('title', 'Gestion des Administrateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Équipe Administrative</h2>
    <a href="{{ route('admins.create') }}" class="btn btn-dark">Ajouter un Admin</a>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Département</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr class="align-middle">
                    <td>{{ $admin->user->nom }} {{ $admin->user->prenom }}</td>
                    <td>{{ $admin->user->email }}</td>
                    <td><span class="badge bg-secondary">{{ $admin->department ?? 'N/A' }}</span></td>
                    <td>{{ $admin->user->telephone ?? '---' }}</td>
                    <td>
                        <div class="d-flex">
                            <a href="{{ route('admins.edit', $admin->id) }}" class="btn btn-sm btn-outline-primary me-2">Modifier</a>
                            <form action="{{ route('admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Supprimer cet admin ?')">
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