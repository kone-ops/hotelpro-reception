<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="name" class="form-label">
            <i class="bi bi-person me-1"></i>Nom complet
        </label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control @error('name') is-invalid @enderror" 
            value="{{ old('name', $user->name) }}" 
            required 
            autofocus
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-1"></i>Adresse email
        </label>
        <input 
            type="email" 
            id="email" 
            class="form-control" 
            value="{{ $user->email }}" 
            disabled 
            readonly
        >
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>L'adresse email ne peut pas être modifiée
        </small>
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-2"></i>Enregistrer
        </button>
        
        @if (session('status') === 'profile-updated')
            <span class="text-success">
                <i class="bi bi-check-circle me-1"></i>Sauvegardé !
            </span>
        @endif
    </div>
</form>
