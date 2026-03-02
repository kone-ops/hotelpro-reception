@php
    $position = $position ?? null;
    $fields = $customFields->where('section', $section)->where('active', true);
    if ($position !== null) {
        $fields = $fields->filter(function ($field) use ($position) {
            $fieldPos = (float) ($field->position ?? 0);
            return abs($fieldPos - $position) < 0.1;
        });
    }
    $fields = $fields->sortBy('position')->values();
@endphp
@if($fields->isNotEmpty())
    <div class="row">
        @foreach($fields as $field)
            <x-public.custom-field :field="$field" :formConfig="$formConfig" />
        @endforeach
    </div>
@endif
