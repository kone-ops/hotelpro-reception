<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <p class="text-muted mb-4">
        <small>Assurez-vous d'utiliser un mot de passe long et sécurisé pour protéger votre compte.</small>
    </p>

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label">
            <i class="bi bi-lock me-1"></i>Mot de passe actuel
        </label>
        <input 
            type="password" 
            id="update_password_current_password" 
            name="current_password" 
            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
            autocomplete="current-password"
        >
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label">
            <i class="bi bi-key me-1"></i>Nouveau mot de passe
        </label>
        <input 
            type="password" 
            id="update_password_password" 
            name="password" 
            class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
            autocomplete="new-password"
            minlength="8"
        >
        @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Minimum 8 caractères</small>
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label">
            <i class="bi bi-key-fill me-1"></i>Confirmer le mot de passe
        </label>
        <input 
            type="password" 
            id="update_password_password_confirmation" 
            name="password_confirmation" 
            class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
            autocomplete="new-password"
            minlength="8"
        >
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-2"></i>Mettre à jour
        </button>
        
        @if (session('status') === 'password-updated')
            <span class="text-success">
                <i class="bi bi-check-circle me-1"></i>Mot de passe mis à jour !
            </span>
        @endif
    </div>
</form>

<script>
// Supprimer le message de succès après 3 secondes
setTimeout(function() {
    const successMsg = document.querySelector('.text-success');
    if (successMsg) {
        successMsg.style.transition = 'opacity 0.5s';
        successMsg.style.opacity = '0';
        setTimeout(() => successMsg.remove(), 500);
    }
}, 3000);
</script>
