<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; padding: 20px;">
    <h2 style="color: #1a2e4a;">Statusänderung Ihrer Bestellung</h2>
    <p>Hallo {{ $order->vorname }},</p>
    <p>der Status Ihrer Bestellung <strong>#{{ $order->id }}</strong> hat sich geändert.</p>
    <p><strong>Neuer Status:</strong> {{ ucfirst($order->status) }}</p>
    <p><strong>Bestellbetrag:</strong> {{ number_format($order->total, 2, ',', '.') }} €</p>
    <p>Bei Fragen können Sie sich jederzeit an uns wenden.</p>
    <hr>
    <p style="font-size: 12px; color: #666;">CultPlanet – Dein Spielzeug-Onlineshop</p>
</body>
</html>
