@props(['content' => '', 'position' => 'top'])

<span class="help-tooltip" data-bs-toggle="tooltip" data-bs-placement="{{ $position }}" title="{{ $content }}">
    <i class="bi bi-question-circle text-primary"></i>
</span>

@once
    @push('scripts')
    <script>
        // Initialiser tous les tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
@endonce





