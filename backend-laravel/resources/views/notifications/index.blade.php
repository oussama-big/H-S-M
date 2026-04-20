@extends('layouts.app')

@section('title', 'Mes Notifications')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🔔 Centre de Notifications</h2>
        <div class="btn-group">
            <form action="{{ route('notifications.markAllRead') }}" method="POST" class="me-2">
                @csrf @method('PUT')
                <button class="btn btn-outline-primary btn-sm">Tout marquer comme lu</button>
            </form>
            <form action="{{ route('notifications.clearAll') }}" method="POST" onsubmit="return confirm('Vider toutes vos notifications ?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Tout effacer</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted">Non lues</h6>
                <span class="display-6 fw-bold text-primary">{{ $stats['unread_count'] ?? 0 }}</span>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notif)
                        <div class="list-group-item p-3 {{ $notif->is_read ? 'bg-light' : 'border-start border-primary border-4' }}">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 {{ $notif->is_read ? 'text-muted' : 'fw-bold' }}">
                                    {{ $notif->content }}
                                </h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                            </div>
                            
                            <div class="mt-2 d-flex justify-content-end">
                                @if(!$notif->is_read)
                                    <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="me-2">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm btn-link text-primary p-0 text-decoration-none">Marquer comme lu</button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0 text-decoration-none">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/107/107831.png" width="80" class="opacity-25 mb-3">
                            <p class="text-muted">Vous n'avez aucune notification pour le moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection