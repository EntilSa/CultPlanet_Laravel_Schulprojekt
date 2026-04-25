<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; }
        .header { background: #1a2e4a; color: white; padding: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0 0; font-size: 12px; opacity: 0.8; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; border-bottom: 2px solid #1a2e4a; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; border-top: 2px solid #1a2e4a; border-bottom: none; }
        .info { margin-bottom: 20px; }
        .info p { margin: 4px 0; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CultPlanet</h1>
        <p>Ihr Spielzeug-Onlineshop</p>
    </div>

    <h2>Rechnung / Auftragsbestätigung</h2>

    <div class="info">
        <p><span class="label">Bestellnummer:</span> #{{ $order->id }}</p>
        <p><span class="label">Datum:</span> {{ $order->created_at->format('d.m.Y H:i') }} Uhr</p>
        <p><span class="label">Kunde:</span> {{ $order->vorname }} {{ $order->nachname }}</p>
        <p><span class="label">Zahlungsmethode:</span> {{ ucfirst($order->zahlungsmethode) }}</p>
        <p><span class="label">Status:</span> {{ ucfirst($order->status) }}</p>
    </div>

    <table>
        <tr>
            <th>Artikel</th>
            <th>Menge</th>
            <th>Einzelpreis</th>
            <th>Gesamt</th>
        </tr>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->price, 2, ',', '.') }} €</td>
            <td>{{ number_format($item->price * $item->quantity, 2, ',', '.') }} €</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3">Gesamtbetrag</td>
            <td>{{ number_format($order->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    <p style="margin-top: 40px; font-size: 11px; color: #666;">
        Vielen Dank für Ihren Einkauf bei CultPlanet!<br>
        Dies ist eine automatisch generierte Rechnung.
    </p>
</body>
</html>
