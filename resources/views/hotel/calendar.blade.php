<x-app-layout>
    
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="bi bi-calendar3"></i> Calendrier des Réservations
            </h2>
            <div>
                <button class="btn btn-sm btn-secondary" id="prevMonth">
                    <i class="bi bi-chevron-left"></i> Mois précédent
                </button>
                <button class="btn btn-sm btn-primary" id="today">
                    <i class="bi bi-calendar-check"></i> Aujourd'hui
                </button>
                <button class="btn btn-sm btn-secondary" id="nextMonth">
                    Mois suivant <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

    <div class="py-4">
        <div class="container-fluid">
            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-primary-gradient">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="stat-total">{{ $stats['total'] }}</div>
                            <div class="stat-label">Réservations ce mois</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success-gradient">
                        <div class="stat-icon">
                            <i class="bi bi-door-open"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="stat-available">{{ $stats['available_rooms'] }}</div>
                            <div class="stat-label">Chambres disponibles</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning-gradient">
                        <div class="stat-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="stat-pending">{{ $stats['pending'] }}</div>
                            <div class="stat-label">En attente</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-danger-gradient">
                        <div class="stat-icon">
                            <i class="bi bi-door-closed"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="stat-occupied">{{ $stats['occupied_rooms'] }}</div>
                            <div class="stat-label">Chambres occupées</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendrier -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="h5 mb-0" id="currentMonth"></h3>
                </div>
                <div class="card-body p-0">
                    <div class="calendar-container">
                        <!-- En-têtes des jours -->
                        <div class="calendar-header">
                            <div class="calendar-day-header m-2">Lun</div>
                            <div class="calendar-day-header m-2">Mar</div>
                            <div class="calendar-day-header m-2">Mer</div>
                            <div class="calendar-day-header m-2">Jeu</div>
                            <div class="calendar-day-header m-2">Ven</div>
                            <div class="calendar-day-header m-2">Sam</div>
                            <div class="calendar-day-header m-2">Dim</div>
                        </div>
                        
                        <!-- Grille des jours -->
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Rempli par JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Légende -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Légende</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success me-2">●</span> Arrivée prévue
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2">●</span> Départ prévu
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning me-2">●</span> En cours de séjour
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-secondary me-2">●</span> Jour vide
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails Jour -->
    <div class="modal fade" id="dayDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dayModalTitle">Réservations du jour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="dayModalBody">
                    <!-- Rempli dynamiquement -->
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .calendar-container {
            background: var(--card-bg, #ffffff);
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e5e7eb;
            border-bottom: 2px solid #d1d5db;
        }

        .calendar-day-header {
            background: #f9fafb;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e5e7eb;
            min-height: 600px;
        }

        .calendar-day {
            background: var(--card-bg, #ffffff);
            padding: 8px;
            min-height: 100px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }

        .calendar-day:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .calendar-day.other-month {
            background: #f9fafb;
            color: #9ca3af;
        }

        .calendar-day.today {
            background: #eff6ff;
            border: 2px solid #3b82f6;
        }

        .day-number {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
            color: #111827;
        }

        .calendar-day.other-month .day-number {
            color: #9ca3af;
        }

        .day-events {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .event-badge {
            font-size: 11px;
            padding: 3px 6px;
            border-radius: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-checkin {
            background: #d1fae5;
            color: #065f46;
            border-left: 3px solid #10b981;
        }

        .event-checkout {
            background: #fee2e2;
            color: #991b1b;
            border-left: 3px solid #ef4444;
        }

        .event-ongoing {
            background: #fef3c7;
            color: #92400e;
            border-left: 3px solid #f59e0b;
        }

        .event-count {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .stat-card {
            padding: 20px;
            border-radius: 12px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            font-size: 40px;
            opacity: 0.8;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .bg-primary-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .bg-success-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .bg-warning-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .bg-danger-gradient {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        let currentDate = new Date();
        const reservations = @json($reservations);

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Mettre à jour le titre
            const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                              'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

            // Premier et dernier jour du mois
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            
            // Jour de la semaine du premier jour (0 = dimanche, 1 = lundi, etc.)
            let startDay = firstDay.getDay();
            startDay = startDay === 0 ? 7 : startDay; // Convertir dimanche (0) en 7

            // Jours du mois précédent à afficher
            const prevMonthDays = startDay - 1;
            const prevMonthLastDay = new Date(year, month, 0).getDate();

            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';

            // Jours du mois précédent
            for (let i = prevMonthDays; i > 0; i--) {
                const day = prevMonthLastDay - i + 1;
                const cell = createDayCell(day, month - 1, year, true);
                grid.appendChild(cell);
            }

            // Jours du mois actuel
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const cell = createDayCell(day, month, year, false);
                grid.appendChild(cell);
            }

            // Jours du mois suivant
            const cellsUsed = prevMonthDays + lastDay.getDate();
            const cellsNeeded = Math.ceil(cellsUsed / 7) * 7;
            const nextMonthDays = cellsNeeded - cellsUsed;

            for (let day = 1; day <= nextMonthDays; day++) {
                const cell = createDayCell(day, month + 1, year, true);
                grid.appendChild(cell);
            }
        }

        function createDayCell(day, month, year, isOtherMonth) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day';
            if (isOtherMonth) {
                cell.classList.add('other-month');
            }

            // Vérifier si c'est aujourd'hui
            const today = new Date();
            const cellDate = new Date(year, month, day);
            if (cellDate.toDateString() === today.toDateString()) {
                cell.classList.add('today');
            }

            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            // Trouver les réservations pour ce jour
            const dayReservations = reservations.filter(r => {
                const checkIn = r.check_in_date;
                const checkOut = r.check_out_date;
                return dateStr >= checkIn && dateStr <= checkOut;
            });

            let html = `<div class="day-number">${day}</div>`;
            
            if (dayReservations.length > 0) {
                html += '<div class="day-events">';
                
                // Limiter à 3 événements visibles
                const visibleCount = Math.min(3, dayReservations.length);
                for (let i = 0; i < visibleCount; i++) {
                    const res = dayReservations[i];
                    let eventClass = 'event-ongoing';
                    let icon = '';
                    
                    if (res.check_in_date === dateStr) {
                        eventClass = 'event-checkin';
                        icon = '→ ';
                    } else if (res.check_out_date === dateStr) {
                        eventClass = 'event-checkout';
                        icon = '← ';
                    }
                    
                    html += `<div class="event-badge ${eventClass}">${icon}${res.nom}</div>`;
                }
                
                html += '</div>';
                
                if (dayReservations.length > 3) {
                    html += `<div class="event-count">+${dayReservations.length - 3}</div>`;
                }
            }

            cell.innerHTML = html;

            // Click handler
            cell.addEventListener('click', () => showDayDetails(dateStr, dayReservations));

            return cell;
        }

        function showDayDetails(date, reservations) {
            const modal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
            const formattedDate = new Date(date).toLocaleDateString('fr-FR', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            document.getElementById('dayModalTitle').textContent = `Réservations du ${formattedDate}`;
            
            let html = '';
            if (reservations.length === 0) {
                html = '<p class="text-muted">Aucune réservation pour cette date.</p>';
            } else {
                html += '<div class="list-group">';
                reservations.forEach(res => {
                    let badgeClass = 'bg-warning';
                    let statusText = 'En cours';
                    
                    if (res.check_in_date === date) {
                        badgeClass = 'bg-success';
                        statusText = 'Arrivée';
                    } else if (res.check_out_date === date) {
                        badgeClass = 'bg-danger';
                        statusText = 'Départ';
                    }
                    
                    html += `
                        <a href="/hotel/pre-reservations/${res.id}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${res.nom} ${res.prenom}</h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-door-open"></i> ${res.room_type?.name || 'Non assigné'}
                                        ${res.room ? ' - Chambre ' + res.room.room_number : ''}
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="bi bi-calendar"></i> Du ${new Date(res.check_in_date).toLocaleDateString('fr-FR')} 
                                        au ${new Date(res.check_out_date).toLocaleDateString('fr-FR')}
                                    </p>
                                </div>
                                <span class="badge ${badgeClass}">${statusText}</span>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
            }
            
            document.getElementById('dayModalBody').innerHTML = html;
            modal.show();
        }

        // Navigation
        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        document.getElementById('today').addEventListener('click', () => {
            currentDate = new Date();
            renderCalendar();
        });

        // Initialiser
        renderCalendar();
    </script>
    @endpush
</x-app-layout>

