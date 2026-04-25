<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; padding: 20px;">
    <h2 style="color: #1a2e4a;">Vielen Dank für deine Bestellung!</h2>
    <p>Hallo {{ $order->vorname }},</p>
    <p>deine Bestellung <strong>#{{ $order->id }}</strong> ist bei uns eingegangen.</p>
    <p><strong>Gesamtbetrag:</strong> {{ number_format($order->total, 2, ',', '.') }} €</p>
    <p>Im Anhang findest du deine Rechnung als PDF.</p>
    <hr>
    <p style="font-size: 12px; color: #666;">CultPlanet – Dein Spielzeug-Onlineshop</p>
</body>
</html>
