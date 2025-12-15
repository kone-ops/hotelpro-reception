@props(['role' => 'receptionist'])

<!-- Système de tour guidé pour nouveaux utilisateurs -->
<div id="onboardingTour" x-data="{ show: false }" x-init="show = !localStorage.getItem('tour_completed_{{ $role }}')">
    <div x-show="show" class="onboarding-overlay" @click="show = false">
        <div class="onboarding-card" @click.stop>
            <button type="button" class="btn-close float-end" @click="show = false"></button>
            
            <div class="text-center mb-4">
                <i class="bi bi-lightbulb text-warning" style="font-size: 3rem;"></i>
                <h4 class="mt-3">Bienvenue dans votre espace !</h4>
                <p class="text-muted">Découvrez les fonctionnalités principales en quelques étapes</p>
            </div>

            @if($role === 'receptionist')
                <div class="tour-steps">
                    <div class="tour-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h6>Tableau de bord</h6>
                            <p>Consultez les statistiques et les dernières pré-réservations</p>
                        </div>
                    </div>
                    <div class="tour-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h6>Pré-réservations</h6>
                            <p>Validez, modifiez ou rejetez les pré-réservations en attente</p>
                        </div>
                    </div>
                    <div class="tour-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h6>Feuilles de police</h6>
                            <p>Générez automatiquement les feuilles de police pour les autorités</p>
                        </div>
                    </div>
                    <div class="tour-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h6>Recherche avancée</h6>
                            <p>Utilisez les filtres pour trouver rapidement une réservation</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" @click="localStorage.setItem('tour_completed_{{ $role }}', 'true'); show = false">
                    <i class="bi bi-check-lg me-2"></i>Compris !
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .onboarding-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .onboarding-card {
        background: var(--card-bg, #ffffff);
        border-radius: 15px;
        padding: 2rem;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .tour-steps {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .tour-step {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .tour-step:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .step-number {
        width: 40px;
        height: 40px;
        background: #0d6efd;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        flex-shrink: 0;
    }

    .step-content h6 {
        margin-bottom: 0.25rem;
        color: #212529;
    }

    .step-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>





