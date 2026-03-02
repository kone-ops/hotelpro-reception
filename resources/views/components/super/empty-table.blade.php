@props([
    'icon' => 'bi-inbox',
    'title' => 'Aucun enregistrement',
    'message' => 'Aucun élément ne correspond à votre recherche ou aux filtres.',
])
<div class="super-empty-table text-center py-5 px-3" role="status" aria-live="polite">
    <i class="bi {{ $icon }} text-muted" style="font-size: 4rem;" aria-hidden="true"></i>
    <h5 class="text-muted mt-3 mb-1">{{ $title }}</h5>
    <p class="text-muted small mb-0">{{ $message }}</p>
    @if(isset($action))
        <div class="mt-3">
            {{ $action }}
        </div>
    @endif
</div>
