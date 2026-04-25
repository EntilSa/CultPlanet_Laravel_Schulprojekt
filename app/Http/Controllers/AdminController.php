<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    // prüfen ob eingeloggter nutzer admin ist, sonst 403
    private function nurAdmin(): void
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }

    // prüfen ob admin oder mitarbeiter (für verkaufsübersicht)
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

        $stats = [
            'produkte' => Product::count(),
            'nutzer' => User::count(),
            'bestellungen' => Order::count(),
            'umsatz' => Order::where('status', 'bezahlt')->sum('total'),
        ];

        // die letzten 5 bestellungen für die übersichtstabelle
        $letzteBestellungen = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'letzteBestellungen'));
    }

    // produkt-übersichtsseite im admin mit lagerbestand und verfügbarkeit
    public function products()
    {
        $this->nurAdmin();

        $products = Product::latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    // alle bestellungen anzeigen (mit paginierung)
    public function orders()
    {
        $this->nurAdmin();

        $orders = Order::with('user')->latest()->paginate(20);

        return view('admin.orders', compact('orders'));
    }

    // status einer bestellung ändern (offen, bezahlt, versendet, storniert)
    public function orderUpdate(Request $request, Order $order)
    {
        $this->nurAdmin();

        $request->validate([
            'status' => ['required', 'in:offen,bezahlt,versendet,storniert'],
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Bestellstatus wurde aktualisiert.');
    }

    // alle nutzer anzeigen mit ihren rollen
    public function users()
    {
        $this->nurAdmin();

        $users = User::with('roles')->latest()->paginate(20);
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    // rolle eines nutzers ändern (z.b. kunde → mitarbeiter)
    public function userRoleUpdate(Request $request, User $user)
    {
        $this->nurAdmin();

        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // alle alten rollen entfernen und nur die neue setzen
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Rolle wurde geändert.');
    }

    // verkaufsübersicht – bezahlte bestellungen pro tag (admin + mitarbeiter)
    public function sales()
    {
        $this->nurAdminOderMitarbeiter();

        // letzten 30 tage, gruppiert nach tag
        $verkaufe = Order::where('status', 'bezahlt')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as tag, COUNT(*) as anzahl, SUM(total) as umsatz')
            ->groupBy('tag')
            ->orderByDesc('tag')
            ->get();

        return view('admin.sales', compact('verkaufe'));
    }
}
