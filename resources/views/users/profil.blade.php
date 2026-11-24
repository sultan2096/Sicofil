@extends('layouts.app')

@section('title', 'Mon Profil - SICOSA')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-cog me-2"></i>Mon Profil</h4>
                    <small class="opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        Espace réservé aux gestionnaires et maires
                    </small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-shield fa-2x me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1">Profil : {{ ucfirst(auth()->user()->role) }}</h6>
                                <small class="text-muted">
                                    Vous pouvez modifier vos informations personnelles ci-dessous
                                </small>
                            </div>
                        </div>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('profil.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="identifiant" class="form-label">Identifiant</label>
                            <input type="text" class="form-control" id="identifiant" name="identifiant" value="{{ old('identifiant', $user->identifiant) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe <span class="text-muted small">(laisser vide pour ne pas changer)</span></label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 