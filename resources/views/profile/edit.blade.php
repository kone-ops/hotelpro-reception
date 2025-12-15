<x-app-layout>
    <x-slot name="header">Mon Profil</x-slot>

    @if(session('success') || session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') ?? 'Profil mis à jour avec succès' }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Informations de l'utilisateur -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                    <h2 class="mb-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</h2>
                </div>
                <div>
                    <h4 class="mb-1">{{ auth()->user()->name }}</h4>
                    <p class="text-muted mb-1">
                        <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email }}
                    </p>
                    <p class="mb-0">
                        @foreach(auth()->user()->roles as $role)
                            <span class="badge bg-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'hotel-admin' ? 'warning' : 'success') }}">
                                {{ ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
                            </span>
                        @endforeach
                        @if(auth()->user()->hotel)
                            <span class="badge" style="background-color: {{ auth()->user()->hotel->primary_color ?? '#1a4b8c' }}">
                                <i class="bi bi-building me-1"></i>{{ auth()->user()->hotel->name }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations du profil -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-black"><i class="bi bi-person-circle me-2"></i>Informations du profil</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <!-- Mise à jour du mot de passe -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-black "><i class="bi bi-shield-lock me-2"></i>Sécurité</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
.avatar-lg {
    width: 80px;
    height: 80px;
    font-size: 32px;
}

.card-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.form-control:disabled,
.form-control[readonly] {
    background-color: #e9ecef;
    opacity: 0.7;
}
</style>
