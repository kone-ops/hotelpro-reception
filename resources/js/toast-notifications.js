/**
 * Système de notifications Toast professionnel
 * Utilise Toastr.js pour des notifications élégantes et responsives
 */

class ToastNotification {
    constructor() {
        this.initToastr();
    }

    initToastr() {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
        }
    }

    success(message, title = 'Succès') {
        toastr.success(message, title);
    }

    error(message, title = 'Erreur') {
        toastr.error(message, title);
    }

    warning(message, title = 'Attention') {
        toastr.warning(message, title);
    }

    info(message, title = 'Information') {
        toastr.info(message, title);
    }

    // Notification persistante (nécessite action utilisateur)
    persistent(message, title = 'Action requise', type = 'warning') {
        toastr.options.timeOut = 0;
        toastr.options.extendedTimeOut = 0;
        toastr[type](message, title);
        // Réinitialiser les options
        this.initToastr();
    }
}

// Instance globale
window.toast = new ToastNotification();

// Gestion automatique des messages Laravel
document.addEventListener('DOMContentLoaded', function() {
    // Messages de session Laravel
    @if(session('success'))
        window.toast.success("{{ session('success') }}");
    @endif

    @if(session('error'))
        window.toast.error("{{ session('error') }}");
    @endif

    @if(session('warning'))
        window.toast.warning("{{ session('warning') }}");
    @endif

    @if(session('info'))
        window.toast.info("{{ session('info') }}");
    @endif

    // Gestion des erreurs de validation
    @if($errors->any())
        @foreach($errors->all() as $error)
            window.toast.error("{{ $error }}", "Erreur de validation");
        @endforeach
    @endif
});





