<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-envelope-check me-2"></i>Notifications client (Email, SMS, WhatsApp)
        </h2>
        <a href="{{ route('super.hotels.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Liste des hôtels
        </a>
    </div>

    <p class="text-muted">
        Configurez pour chaque hôtel les canaux d'envoi (email, SMS, WhatsApp) et le contenu des messages envoyés au client (enregistrement enregistré, validé ou rejeté).
    </p>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($hotels->isEmpty())
                <p class="text-muted mb-0">Aucun hôtel. <a href="{{ route('super.hotels.index') }}">Créer un hôtel</a> puis configurer les notifications.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Notifications client par hôtel">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-building me-1 text-primary"></i>Hôtel</th>
                                <th scope="col"><i class="bi bi-geo-alt me-1 text-primary"></i>Ville / Pays</th>
                                <th scope="col" class="text-end" width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hotels as $hotel)
                                <tr>
                                    <td>
                                        @if($hotel->logo_url)
                                            <img src="{{ $hotel->logo_url }}" alt="" class="rounded me-2" style="width: 36px; height: 36px; object-fit: contain;">
                                        @else
                                            <span class="text-muted me-2"><i class="bi bi-building"></i></span>
                                        @endif
                                        <strong>{{ $hotel->name }}</strong>
                                    </td>
                                    <td class="text-muted">{{ $hotel->city ?? '—' }}@if($hotel->country){{ $hotel->city ? ', ' : '' }}{{ $hotel->country }}@endif</td>
                                    <td class="text-end">
                                        <a href="{{ route('super.hotels.notifications.show', $hotel) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-gear me-1"></i>Configurer
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
