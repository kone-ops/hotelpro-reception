<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-puzzle me-2"></i>{{ __('super.hotels.modules_activation') }}
        </h2>
        <a href="{{ route('super.hotels.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>{{ __('super.hotels.list') }}
        </a>
    </div>

    <p class="text-muted">
        {{ __('super.hotels.modules_activation_description') }}
    </p>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($hotels->isEmpty())
                <p class="text-muted mb-0">{{ __('super.hotels.no_hotels') }} <a href="{{ route('super.hotels.index') }}">{{ __('super.hotels.create') }}</a> {{ __('super.hotels.no_hotels_create') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Activation des modules par hôtel">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-building me-1 text-primary"></i>{{ __('super.hotels.hotel') }}</th>
                                <th scope="col"><i class="bi bi-geo-alt me-1 text-primary"></i>{{ __('super.hotels.city_country') }}</th>
                                <th scope="col"><i class="bi bi-puzzle me-1 text-primary"></i>{{ __('super.hotels.enabled_modules') }}</th>
                                <th scope="col" class="text-end">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hotels as $hotel)
                                @php
                                    $modules = $hotel->settings['modules'] ?? [];
                                    $housekeeping = (bool) ($modules['housekeeping'] ?? true);
                                @endphp
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
                                    <td>
                                        @if($housekeeping)
                                            <span class="badge bg-success">{{ __('modules.housekeeping.label') }}</span>
                                        @else
                                            <span class="text-muted">{{ __('super.hotels.none') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('super.hotels.show', $hotel) }}#modules" class="btn btn-sm btn-primary">
                                            <i class="bi bi-gear me-1"></i>{{ __('super.hotels.configure') }}
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
