@if(count($customFields) > 0)
    @if($displayStyle === 'police-sheet')
        {{-- Style pour fiche de police --}}
        @foreach($customFields as $item)
            @php
                $field = $item['field'];
                $value = $item['formatted_value'];
            @endphp
            <div class="d-flex mb-1" style="font-size: 8pt;">
                <div style="width: 35%; font-weight: bold; padding-right: 5px;">{{ $field->label }}:</div>
                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $value }}</div>
            </div>
        @endforeach
    @elseif($displayStyle === 'table')
        {{-- Style tableau --}}
        <table class="table table-sm">
            <tbody>
                @foreach($customFields as $item)
                    @php
                        $field = $item['field'];
                        $value = $item['formatted_value'];
                    @endphp
                    <tr>
                        <td style="width: 40%; font-weight: bold;">{{ $field->label }}:</td>
                        <td>{!! $value !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($displayStyle === 'inline')
        {{-- Style inline (pour les vues show) --}}
        <div class="row">
            @foreach($customFields as $item)
                @php
                    $field = $item['field'];
                    $value = $item['formatted_value'];
                @endphp
                <div class="col-md-6 mb-3">
                    <strong>{{ $field->label }}:</strong><br>
                    <span class="text-muted">{!! $value !!}</span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Style liste par défaut --}}
        <div class="list-group">
            @foreach($customFields as $item)
                @php
                    $field = $item['field'];
                    $value = $item['formatted_value'];
                @endphp
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">{{ $field->label }}</h6>
                    </div>
                    <p class="mb-1">{!! $value !!}</p>
                </div>
            @endforeach
        </div>
    @endif
@endif

