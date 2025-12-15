/**
 * ============================================
 * ROOM AVAILABILITY MANAGEMENT - PUBLIC FORM
 * ============================================
 * Gestion de la disponibilité des chambres en temps réel
 * @version 1.0
 */

(function() {
    'use strict';
    
    // Configuration
    const hotelId = document.body.dataset.hotelId || window.hotelId;
    
    // Elements DOM
    let checkInInput, checkOutInput, roomTypeSelect, roomSelect, roomSelectContainer, noRoomsMessage;
    
    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        initializeElements();
        attachEventListeners();
    });
    
    /**
     * Initialiser les éléments DOM
     */
    function initializeElements() {
        // Essayer différents IDs pour compatibilité
        checkInInput = document.getElementById('dateArrivee') || document.getElementById('check_in_date');
        checkOutInput = document.getElementById('dateDepart') || document.getElementById('check_out_date');
        roomTypeSelect = document.getElementById('roomTypeSelect');
        roomSelect = document.getElementById('roomSelect');
        roomSelectContainer = document.getElementById('roomSelectContainer');
        noRoomsMessage = document.getElementById('noRoomsMessage');
        
        // Définir la date minimum (aujourd'hui)
        if (checkInInput) {
            const today = new Date().toISOString().split('T')[0];
            checkInInput.setAttribute('min', today);
        }
    }
    
    /**
     * Attacher les event listeners
     */
    function attachEventListeners() {
        // Quand les dates changent, recharger les types de chambres
        if (checkInInput) checkInInput.addEventListener('change', onDatesChange);
        if (checkOutInput) checkOutInput.addEventListener('change', onDatesChange);
        
        // Quand le type de chambre change, charger les chambres disponibles
        if (roomTypeSelect) {
            roomTypeSelect.addEventListener('change', onRoomTypeChange);
        }
    }
    
    /**
     * Gérer le changement des dates
     */
    function onDatesChange() {
        const checkIn = checkInInput?.value;
        const checkOut = checkOutInput?.value;
        
        if (!checkIn || !checkOut) {
            return;
        }
        
        // Vérifier que check-out est après check-in
        if (checkOut <= checkIn) {
            showError('La date de départ doit être après la date d\'arrivée');
            return;
        }
        
        // Recharger les types de chambres disponibles
        loadRoomTypes();
    }
    
    /**
     * Charger les types de chambres disponibles
     */
    function loadRoomTypes() {
        if (!roomTypeSelect) return;
        
        // Réinitialiser les sélecteurs
        roomTypeSelect.innerHTML = '<option value="">-- Sélectionnez un type de chambre --</option>';
        resetRoomSelect();
        
        // Charger les types via API
        fetch(`/api/hotels/${hotelId}/room-types`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.room_types) {
                    data.room_types.forEach(roomType => {
                        const option = document.createElement('option');
                        option.value = roomType.id;
                        option.textContent = `${roomType.name} (${roomType.available_rooms} disponible(s))`;
                        
                        if (roomType.available_rooms === 0) {
                            option.disabled = true;
                            option.textContent = `${roomType.name} (Complet)`;
                        }
                        
                        roomTypeSelect.appendChild(option);
                    });
                    
                    roomTypeSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des types:', error);
                showError('Erreur lors du chargement des types de chambres');
            });
    }
    
    /**
     * Gérer le changement du type de chambre
     */
    function onRoomTypeChange() {
        const roomTypeId = roomTypeSelect?.value;
        
        if (!roomTypeId) {
            resetRoomSelect();
            return;
        }
        
        loadAvailableRooms(roomTypeId);
    }
    
    /**
     * Charger les chambres disponibles pour un type
     */
    function loadAvailableRooms(roomTypeId) {
        const checkIn = checkInInput?.value;
        const checkOut = checkOutInput?.value;
        
        // Si les dates ne sont pas sélectionnées, ne rien faire (pas d'alerte)
        if (!checkIn || !checkOut) {
            console.log('Dates not selected yet, skipping room loading');
            return;
        }
        
        // Réinitialiser le sélecteur de chambres
        if (roomSelect) {
            roomSelect.innerHTML = '<option value="">Chargement...</option>';
            roomSelect.disabled = true;
        }
        
        // Charger via API
        const url = `/api/hotels/${hotelId}/available-rooms?room_type_id=${roomTypeId}&check_in=${checkIn}&check_out=${checkOut}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (!data.success || data.rooms.length === 0) {
                    // Aucune chambre disponible
                    showNoRoomsMessage();
                    return;
                }
                
                // Afficher les chambres disponibles
                roomSelect.innerHTML = '<option value="">-- Sélectionnez une chambre --</option>';
                
                data.rooms.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `Chambre ${room.room_number}`;
                    
                    if (room.floor) {
                        option.textContent += ` - Étage ${room.floor}`;
                    }
                    
                    roomSelect.appendChild(option);
                });
                
                roomSelect.disabled = false;
                
                // Afficher le conteneur et cacher le message d'erreur
                if (roomSelectContainer) {
                    roomSelectContainer.style.display = 'block';
                }
                if (noRoomsMessage) {
                    noRoomsMessage.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des chambres:', error);
                showError('Erreur lors du chargement des chambres disponibles');
            });
    }
    
    /**
     * Afficher le message "aucune chambre disponible"
     */
    function showNoRoomsMessage() {
        if (roomSelectContainer) {
            roomSelectContainer.style.display = 'none';
        }
        
        if (noRoomsMessage) {
            noRoomsMessage.style.display = 'block';
            noRoomsMessage.innerHTML = `
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Aucune chambre disponible</strong> pour ce type et ces dates.
                <br><small>Veuillez essayer d'autres dates ou un autre type de chambre.</small>
            `;
        }
        
        if (roomSelect) {
            roomSelect.innerHTML = '<option value="">Aucune chambre disponible</option>';
            roomSelect.disabled = true;
        }
    }
    
    /**
     * Réinitialiser le sélecteur de chambres
     */
    function resetRoomSelect() {
        if (roomSelect) {
            roomSelect.innerHTML = '<option value="">-- Sélectionnez d\'abord un type de chambre --</option>';
            roomSelect.disabled = true;
        }
        
        if (roomSelectContainer) {
            roomSelectContainer.style.display = 'none';
        }
        
        if (noRoomsMessage) {
            noRoomsMessage.style.display = 'none';
        }
    }
    
    /**
     * Afficher un message d'erreur
     */
    function showError(message) {
        console.error(message);
        
        // Créer une alerte Bootstrap si elle n'existe pas
        let alertDiv = document.getElementById('roomAvailabilityError');
        
        if (!alertDiv) {
            alertDiv = document.createElement('div');
            alertDiv.id = 'roomAvailabilityError';
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.maxWidth = '400px';
            document.body.appendChild(alertDiv);
        }
        
        alertDiv.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <i class="bi bi-exclamation-triangle"></i> ${message}
        `;
        
        // Auto-fermer après 5 secondes
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Exposer les fonctions nécessaires globalement
    window.RoomAvailability = {
        loadRoomTypes,
        loadAvailableRooms,
        resetRoomSelect
    };
    
})();







