{{-- Badge mit farbiger Kennzeichnung für den Bestellstatus --}}
@php
    $farbe = match($status) {
        'offen'      => 'bg-yellow-100 text-yellow-800',
        'bezahlt'    => 'bg-blue-100 text-blue-800',
        'versendet'  => 'bg-green-100 text-green-800',
        'storniert'  => 'bg-red-100 text-red-800',
        default      => 'bg-slate-100 text-slate-600',
    };
@endphp
<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $farbe }}">
    {{ ucfirst($status) }}
</span>
