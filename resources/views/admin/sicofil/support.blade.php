@extends('layouts.app')

@section('title', 'Admin SICOFIL - Support')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-headset"></i> Support centralisé</h3>
        <a href="{{ route('admin.sicofil.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4"><div class="card border-primary"><div class="card-body"><p class="text-muted mb-1">Tickets ouverts</p><h3>{{ $statsTickets['ouverts'] }}</h3></div></div></div>
        <div class="col-md-4"><div class="card border-danger"><div class="card-body"><p class="text-muted mb-1">Critiques</p><h3>{{ $statsTickets['critiques'] }}</h3></div></div></div>
        <div class="col-md-4"><div class="card border-success"><div class="card-body"><p class="text-muted mb-1">Résolus</p><h3>{{ $statsTickets['resolus'] }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Créer un ticket</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.sicofil.support.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Commune</label>
                            <select name="commune_id" class="form-select">
                                <option value="">SICOFIL / Global</option>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}">{{ $commune->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Titre</label><input type="text" name="titre" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" required></textarea></div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priorité</label>
                                <select name="priorite" class="form-select" required>
                                    <option value="normale">Normale</option>
                                    <option value="basse">Basse</option>
                                    <option value="haute">Haute</option>
                                    <option value="critique">Critique</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie</label>
                                <select name="categorie" class="form-select" required>
                                    <option value="technique">Technique</option>
                                    <option value="facturation">Facturation</option>
                                    <option value="compte">Compte</option>
                                    <option value="paiement">Paiement</option>
                                    <option value="donnees">Données</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4"><label class="form-label">Statut</label><select name="statut" class="form-select"><option value="">Tous</option>@foreach(['ouvert','en_cours','resolu','ferme'] as $s)<option value="{{ $s }}" {{ request('statut') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Priorité</label><select name="priorite" class="form-select"><option value="">Toutes</option>@foreach(['basse','normale','haute','critique'] as $p)<option value="{{ $p }}" {{ request('priorite') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>@endforeach</select></div>
                        <div class="col-md-4"><button type="submit" class="btn btn-outline-primary"><i class="fas fa-filter"></i> Filtrer</button><a href="{{ route('admin.sicofil.support') }}" class="btn btn-outline-secondary">Reset</a></div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Ticket</th><th>Commune</th><th>Priorité</th><th>Statut</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                            <tr>
                                <td><strong>#{{ $ticket->id }} - {{ $ticket->titre }}</strong><br><small class="text-muted">{{ Str::limit($ticket->description, 90) }}</small></td>
                                <td>{{ $ticket->commune->nom ?? 'Global' }}</td>
                                <td><span class="badge bg-{{ $ticket->priorite === 'critique' ? 'danger' : ($ticket->priorite === 'haute' ? 'warning text-dark' : 'secondary') }}">{{ ucfirst($ticket->priorite) }}</span></td>
                                <td><span class="badge bg-{{ in_array($ticket->statut, ['resolu','ferme']) ? 'success' : 'primary' }}">{{ ucfirst(str_replace('_', ' ', $ticket->statut)) }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.sicofil.support.update', $ticket) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PUT')
                                        <select name="statut" class="form-select form-select-sm">
                                            @foreach(['ouvert','en_cours','resolu','ferme'] as $s)<option value="{{ $s }}" {{ $ticket->statut === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
                                        </select>
                                        <input type="hidden" name="priorite" value="{{ $ticket->priorite }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucun ticket</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $tickets->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
