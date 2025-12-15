/**
 * Gestionnaire de QR Code pour les Accompagnants de Groupe
 * Système simplifié : Un QR code pour tous les accompagnants
 */
class GroupAccompanimentQRManager {
    constructor() {
        this.hotelId = window.hotelId;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        this.qrCodeContainer = document.getElementById('accompagnantsContainer');
        this.adultesInput = document.getElementById('adultes') || document.getElementById('nombreAdultes');
        this.accompagnantsFields = document.getElementById('accompagnantsFields');
        
        this.init();
    }

    init() {
        if (!this.adultesInput || !this.qrCodeContainer) {
            console.warn('Éléments requis non trouvés pour GroupAccompanimentQRManager');
            return;
        }

        this.setupEventListeners();
        this.checkInitialState();
    }

    setupEventListeners() {
        // Écouter les changements du nombre d'adultes
        this.adultesInput.addEventListener('change', () => {
            this.handleAdultesChange();
        });

        // Écouter les changements du nombre d'enfants
        const enfantsInput = document.getElementById('enfants') || document.querySelector('[name="nombre_enfants"]') || document.getElementById('nombre_enfants');
        if (enfantsInput) {
            enfantsInput.addEventListener('change', () => {
                this.handleAdultesChange();
            });
        }
    }

    checkInitialState() {
        const adultes = parseInt(this.adultesInput.value) || 0;
        if (adultes >= 2) {
            this.showQRCodeSection();
        }
    }

    handleAdultesChange() {
        const adultes = parseInt(this.adultesInput.value) || 0;
        
        if (adultes >= 2) {
            this.showQRCodeSection();
        } else {
            this.hideQRCodeSection();
        }
    }

    showQRCodeSection() {
        if (this.accompagnantsFields) {
            this.accompagnantsFields.style.display = 'block';
        }
        
        // Vérifier si un QR code existe déjà
        if (!this.hasExistingQRCode()) {
            this.generateGroupQRCode();
        }
    }

    hideQRCodeSection() {
        if (this.accompagnantsFields) {
            this.accompagnantsFields.style.display = 'none';
        }
        this.clearQRCodeContainer();
    }

    hasExistingQRCode() {
        return this.qrCodeContainer.querySelector('.qr-code-group') !== null;
    }

    async generateGroupQRCode() {
        try {
            this.showLoadingState();

            // Créer une réservation temporaire
            const reservation = await this.createTemporaryReservation();
            
            if (!reservation.success) {
                throw new Error(reservation.message || 'Erreur lors de la création de la réservation');
            }

            // Créer le groupe d'accompagnement
            const group = await this.createAccompanimentGroup(reservation.reservation_id);
            
            if (!group.success) {
                throw new Error(group.message || 'Erreur lors de la création du groupe');
            }

            // Afficher le QR code
            this.displayGroupQRCode(group.accompaniment);

        } catch (error) {
            console.error('Erreur lors de la génération du QR code:', error);
            this.showErrorState(error.message);
        }
    }

    async createTemporaryReservation() {
        const response = await fetch(`/f/${this.hotelId}/temporary-reservation`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        });

        return await response.json();
    }

    async createAccompanimentGroup(reservationId) {
        const adultes = parseInt(this.adultesInput.value) || 0;
        const maxAccompaniments = Math.max(0, adultes - 1); // -1 car le client principal n'est pas un accompagnant

        const response = await fetch(`/f/${this.hotelId}/accompaniment-group`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                reservation_id: reservationId,
                max_accompaniments: maxAccompaniments
            })
        });

        return await response.json();
    }

    displayGroupQRCode(accompaniment) {
        const qrCodeHtml = `
            <div class="qr-code-group">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h6 class="mb-0">
                            <i class="bi bi-qr-code me-2"></i>
                            QR Code pour Accompagnants
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="/accompaniment/qr-png/${accompaniment.group_token}" 
                                 alt="QR Code Accompagnants" 
                                 class="img-fluid" 
                                 style="max-width: 200px;">
                        </div>
                        <p class="text-muted mb-3">
                            <strong>${accompaniment.max_accompaniments}</strong> accompagnant(s) maximum
                        </p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" 
                                    onclick="window.open('${accompaniment.form_url}', '_blank')">
                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                Ouvrir le Formulaire
                            </button>
                            <a class="btn btn-outline-success btn-sm" 
                               href="/accompaniment/qr-png/${accompaniment.group_token}" target="_blank">
                                <i class="bi bi-download me-1"></i>
                                Télécharger le QR (PNG)
                            </a>
                            <button class="btn btn-outline-secondary btn-sm" 
                                    onclick="navigator.clipboard.writeText('${accompaniment.form_url}')">
                                <i class="bi bi-clipboard me-1"></i>
                                Copier le Lien
                            </button>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Partagez ce QR code avec vos accompagnants
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Statut en temps réel -->
                <div class="mt-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Statut des Accompagnants</h6>
                                    <small class="text-muted">Mise à jour automatique</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary" id="groupStatus">0/${accompaniment.max_accompaniments}</span>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.qrCodeContainer.innerHTML = qrCodeHtml;
        
        // Démarrer le suivi en temps réel
        this.startRealTimeTracking(accompaniment.group_token);
    }

    async startRealTimeTracking(groupToken) {
        // Mise à jour immédiate
        await this.updateGroupStatus(groupToken);
        
        // Mise à jour toutes les 10 secondes
        setInterval(async () => {
            await this.updateGroupStatus(groupToken);
        }, 10000);
    }

    async updateGroupStatus(groupToken) {
        try {
            const response = await fetch(`/accompaniment/group-status/${groupToken}`);
            const data = await response.json();

            if (data.success) {
                this.updateStatusDisplay(data.group, data.accompaniments);
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour du statut:', error);
        }
    }

    updateStatusDisplay(group, accompaniments) {
        const statusBadge = document.getElementById('groupStatus');
        const progressBar = document.querySelector('.progress-bar');
        
        if (statusBadge) {
            statusBadge.textContent = `${group.completed_count}/${group.max_accompaniments}`;
            statusBadge.className = group.is_full ? 'badge bg-success' : 'badge bg-primary';
        }
        
        if (progressBar) {
            progressBar.style.width = `${group.completion_percentage}%`;
            progressBar.className = group.is_full ? 'progress-bar bg-success' : 'progress-bar bg-primary';
        }

        // Afficher la liste des accompagnants inscrits
        this.updateAccompanimentsList(accompaniments);
        this.updateNamesOnMainForm(accompaniments);
    }

    updateAccompanimentsList(accompaniments) {
        let listContainer = document.getElementById('accompanimentsList');
        
        if (!listContainer) {
            // Créer le conteneur s'il n'existe pas
            const qrGroup = document.querySelector('.qr-code-group');
            if (qrGroup) {
                listContainer = document.createElement('div');
                listContainer.id = 'accompanimentsList';
                listContainer.className = 'mt-3';
                qrGroup.appendChild(listContainer);
            }
        }

        if (listContainer && accompaniments.length > 0) {
            listContainer.innerHTML = `
                <div class="card border-0 bg-success bg-opacity-10">
                    <div class="card-body p-3">
                        <h6 class="mb-2 text-success">
                            <i class="bi bi-people-fill me-1"></i>
                            Accompagnants Inscrits (${accompaniments.length})
                        </h6>
                        <div class="row">
                            ${accompaniments.map(acc => `
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-check text-success me-2"></i>
                                        <div>
                                            <div class="fw-bold">${acc.name}</div>
                                            <small class="text-muted">${acc.completed_at}</small>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }
    }

    updateNamesOnMainForm(accompaniments) {
        const wrapper = document.getElementById('accompagnantsNamesRealtime');
        const list = document.getElementById('accompagnantsNamesList');
        if (!wrapper || !list) return;

        if (!accompaniments || accompaniments.length === 0) {
            wrapper.style.display = 'none';
            list.innerHTML = '';
            return;
        }

        wrapper.style.display = 'block';
        list.innerHTML = accompaniments.map(acc => `
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-check text-success me-2"></i>
                    <div>
                        <div class="fw-bold">${acc.name}</div>
                        <small class="text-muted">${acc.completed_at}</small>
                    </div>
                </div>
            </div>
        `).join('');
    }

    showLoadingState() {
        this.qrCodeContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2 text-muted">Génération du QR code...</p>
            </div>
        `;
    }

    showErrorState(message) {
        this.qrCodeContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Erreur:</strong> ${message}
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="location.reload()">
                    Réessayer
                </button>
            </div>
        `;
    }

    clearQRCodeContainer() {
        this.qrCodeContainer.innerHTML = '';
    }
}

// Initialiser le gestionnaire quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    const init = () => {
        if (typeof window.hotelId === 'undefined' || window.hotelId === null) {
            setTimeout(init, 100);
            return;
        }
        window.groupAccompanimentQRManager = new GroupAccompanimentQRManager();
    };
    init();
});
