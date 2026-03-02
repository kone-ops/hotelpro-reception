<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiches de Police - Accompagnants enregistrement #{{ $reservation->id }}</title>
    <style>
        @page { margin: 10mm; size: A5 portrait; }
        body { font-family: 'Courier New', monospace; font-size: 9pt; line-height: 1.2; margin: 0; padding: 0; color: #000; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
    </style>
    </head>
<body>
@foreach($guests as $guest)
    @php($g = $guest)
    @include('reception.police-sheet.pdf', ['reservation' => $reservation, 'guest' => $g, 'showSignature' => false])
    <div class="page"></div>
@endforeach
</body>
</html>

