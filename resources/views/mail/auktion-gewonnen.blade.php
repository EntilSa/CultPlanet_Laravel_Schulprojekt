<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; padding: 20px;">
    <h2 style="color: #f97316;">Glückwunsch – du hast gewonnen!</h2>
    <p>Hallo {{ $order->vorname }},</p>
    <p>du hast die Auktion für <strong>{{ $order->items->first()?->name }}</strong> gewonnen!</p>
    <p><strong>Gewinngebot:</strong> {{ number_format($order->total, 2, ',', '.') }} €</p>
    <p>Deine Bestellnummer lautet: <strong>#{{ $order->id }}</strong></p>
    <p>Bitte ergänze deine Lieferadresse in deinem Konto. Im Anhang findest du deine Rechnung als PDF.</p>
    <hr>
    <p style="font-size: 12px; color: #666;">CultPlanet – Dein Spielzeug-Onlineshop</p>
</body>
</html>
