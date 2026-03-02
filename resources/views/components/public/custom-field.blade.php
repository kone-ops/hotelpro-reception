@props(['field', 'formConfig'])

@php
    $colClass = $field->type === 'textarea' ? 'col-md-12' : 'col-md-6';
@endphp
<div class="{{ $colClass }} mb-3">
    <label class="form-label">
        {{ $field->label }}
        {!! $formConfig->getRequiredStar($field->key) !!}
    </label>
    @if($field->type === 'textarea')
        <textarea name="{{ $field->key }}" class="form-control" rows="3" {{ $formConfig->getRequiredAttribute($field->key) }}></textarea>
    @elseif($field->type === 'select')
        <select name="{{ $field->key }}" class="form-select" {{ $formConfig->getRequiredAttribute($field->key) }}>
            <option value="">-- Sélectionner --</option>
            @if($field->options)
                @foreach($field->options as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            @endif
        </select>
    @elseif($field->type === 'radio')
        <div>
            @if($field->options)
                @foreach($field->options as $index => $option)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="{{ $field->key }}" id="{{ $field->key }}_{{ $index }}" value="{{ $option }}" {{ $formConfig->getRequiredAttribute($field->key) }}>
                        <label class="form-check-label" for="{{ $field->key }}_{{ $index }}">{{ $option }}</label>
                    </div>
                @endforeach
            @endif
        </div>
    @elseif($field->type === 'checkbox')
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="{{ $field->key }}" id="{{ $field->key }}" value="1" {{ $formConfig->getRequiredAttribute($field->key) }}>
            <label class="form-check-label" for="{{ $field->key }}">{{ $field->label }}</label>
        </div>
    @else
        <input type="{{ $field->type }}" name="{{ $field->key }}" class="form-control" {{ $formConfig->getRequiredAttribute($field->key) }}>
    @endif
</div>
