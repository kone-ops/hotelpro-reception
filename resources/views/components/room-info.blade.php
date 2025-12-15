@props(['reservation', 'showDates' => true])

<div class="room-info">
    @if($reservation->room || $reservation->roomType)
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-door-open text-primary mt-1"></i>
            <div class="flex-grow-1">
                @if($reservation->roomType)
                    <div class="fw-bold">{{ $reservation->roomType->name }}</div>
                @endif
                
                @if($reservation->room)
                    <div class="text-muted small">
                        <i class="bi bi-hash"></i> Chambre {{ $reservation->room->room_number }}
                        @if($reservation->room->floor)
                            - Étage {{ $reservation->room->floor }}
                        @endif
                    </div>
                    <div class="mt-1">
                        <span class="badge bg-{{ $reservation->room->status === 'available' ? 'success' : ($reservation->room->status === 'occupied' ? 'danger' : 'warning') }}">
                            {{ ucfirst($reservation->room->status) }}
                        </span>
                    </div>
                @else
                    <div class="text-muted small">
                        <i class="bi bi-info-circle"></i> Chambre non assignée
                    </div>
                @endif
                
                @if($showDates && ($reservation->check_in_date || $reservation->check_out_date))
                    <div class="mt-2 text-muted small">
                        @if($reservation->check_in_date)
                            <i class="bi bi-calendar-check"></i> Arrivée : {{ $reservation->check_in_date->format('d/m/Y') }}
                        @endif
                        @if($reservation->check_out_date)
                            <br><i class="bi bi-calendar-x"></i> Départ : {{ $reservation->check_out_date->format('d/m/Y') }}
                        @endif
                        @if($reservation->check_in_date && $reservation->check_out_date)
                            <br><i class="bi bi-moon"></i> {{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }} nuit(s)
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="text-muted small">
            <i class="bi bi-info-circle"></i> Pas de chambre sélectionnée
        </div>
    @endif
</div>

