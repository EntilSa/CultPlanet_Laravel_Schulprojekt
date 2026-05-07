<?php

namespace App\Http\Controllers;

use App\Mail\BestellstatusGeaendertMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    // hilfsmethode: prüfen ob eingeloggter nutzer admin ist
    // abort(403) bricht die anfrage ab und zeigt "Zugriff verweigert" – 403 ist der http-fehlercode dafür
    private function nurAdmin(): void
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }

    // hilfsmethode: prüfen ob admin oder mitarbeiter
    // die verkaufsübersicht ist für beide rollen zugänglich
    private function nurAdminOderMitarbeiter(): void
    {
        if (! auth()->user()->hasRole('admin') && ! auth()->user()->hasRole('mitarbeiter')) {
            abort(403);
        }
    }

    // admin-dashboard – kennzahlen auf einen blick
    public function dashboard()
    {
        $this->nurAdmin();

        // alle wichtigen zahlen in einem array sammeln und an die view übergeben
        // count() zählt datensätze, sum() addiert alle werte einer spalte
        $stats = [
            'produkte'     => Product::count(),
            'nutzer'       => User::count(),
            'bestellungen' => Order::count(),
            // nur bestellungen mit status 'bezahlt' zählen zum echten umsatz
            'umsatz'       => Order::where('status', 'bezahlt')->sum('total'),
        ];

        // die letzten 5 bestellungen für die tabelle – with('user') lädt den nutzer gleich mit (kein n+1-problem)
        $letzteBestellungen = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'letzteBestellungen'));
    }

    // produkt-übersichtsseite im admin mit lagerbestand und verfügbarkeit
    public function products()
    {
        $this->nurAdmin();

        // paginate(20) zeigt 20 produkte pro seite und erstellt automatisch die seitennavigation
        $products = Product::latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    // alle bestellungen anzeigen (mit paginierung)
    public function orders()
    {
        $this->nurAdmin();

        // with('user') lädt den zugehörigen nutzer in einer einzigen zusätzlichen abfrage mit
        // ohne with() würde für jede bestellung eine eigene db-anfrage entstehen
        $orders = Order::with('user')->latest()->paginate(20);

        return view('admin.orders', compact('orders'));
    }

    // status einer bestellung ändern (offen → bezahlt → versendet → storniert)
    public function orderUpdate(Request $request, Order $order)
    {
        $this->nurAdmin();

        // 'in:offen,bezahlt,versendet,storniert' – nur diese vier werte sind erlaubt
        // verhindert dass jemand einen beliebigen status-wert einschleust
        $request->validate([
            'status' => ['required', 'in:offen,bezahlt,versendet,storniert'],
        ]);

        // status in der datenbank aktualisieren
        $order->update(['status' => $request->status]);

        // kunde per mail über den neuen status informieren
        Mail::to($order->user->email)->send(new BestellstatusGeaendertMail($order));

        return back()->with('success', 'Bestellstatus wurde aktualisiert.');
    }

    // alle nutzer anzeigen mit ihren rollen
    public function users()
    {
        $this->nurAdmin();

        // with('roles') lädt die spatie-rollen für jeden nutzer mit – ohne n+1-problem
        $users = User::with('roles')->latest()->paginate(20);
        // alle verfügbaren rollen laden damit das dropdown im formular befüllt werden kann
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    // rolle eines nutzers ändern (z.b. kunde → mitarbeiter)
    public function userRoleUpdate(Request $request, User $user)
    {
        $this->nurAdmin();

        // 'exists:roles,name' prüft ob die eingegebene rolle wirklich in der roles-tabelle existiert
        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // syncRoles() entfernt alle alten rollen und setzt nur die neue
        // ein nutzer hat bei uns immer genau eine rolle – deshalb kein einfaches addRole()
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Rolle wurde geändert.');
    }

    // verkaufsübersicht – bezahlte bestellungen pro tag (admin + mitarbeiter)
    public function sales()
    {
        $this->nurAdminOderMitarbeiter();

        // raw-sql mit selectRaw: datum extrahieren, bestellungen zählen, umsatz summieren
        // groupBy('tag') fasst alle bestellungen desselben tages zusammen
        $verkaufe = Order::where('status', 'bezahlt')
            ->where('created_at', '>=', now()->subDays(30)) // nur die letzten 30 tage
            ->selectRaw('DATE(created_at) as tag, COUNT(*) as anzahl, SUM(total) as umsatz')
            ->groupBy('tag')
            ->orderByDesc('tag') // neuester tag zuerst
            ->get();

        return view('admin.sales', compact('verkaufe'));
    }
}
