<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .email-container { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .email-header { background: {{ $hotel->primary_color ?? '#1a4b8c' }}; color: white; padding: 20px; text-align: center; }
        .email-body { padding: 25px; }
        .email-footer { background: #f9fafb; padding: 15px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="email-container">
        @if($hotel->logo_url)
        <div class="email-header">
            <img src="{{ $hotel->logo_url }}" alt="{{ $hotel->name }}" style="max-height: 60px; max-width: 180px;">
            <div style="margin-top: 10px; font-weight: bold;">{{ $hotel->name }}</div>
        </div>
        @endif
        <div class="email-body">
            {!! $bodyHtml !!}
        </div>
        <div class="email-footer">
            <p style="margin: 0;"><strong>{{ $hotel->name }}</strong></p>
            @if($hotel->address)<p style="margin: 5px 0;">{{ $hotel->address }}@if($hotel->city), {{ $hotel->city }}@endif @if($hotel->country) - {{ $hotel->country }}@endif</p>@endif
            @if($hotel->phone)<p style="margin: 5px 0;">Tél. : {{ $hotel->phone }}</p>@endif
            @if($hotel->email)<p style="margin: 5px 0;">{{ $hotel->email }}</p>@endif
            <p style="margin-top: 10px; margin-bottom: 0;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
