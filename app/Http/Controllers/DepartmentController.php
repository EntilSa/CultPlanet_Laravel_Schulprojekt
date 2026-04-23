<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // alle bereiche anzeigen + warnung wenn ein bereich unbesetzt ist
    public function index()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // bereiche mit ihren mitarbeitern laden
        $bereiche = Department::with('users')->orderBy('name')->get();

        // alle nutzer mit der rolle "mitarbeiter" für die zuweisungs-dropdown
        $mitarbeiter = User::role('mitarbeiter')->orderBy('name')->get();

        return view('admin.departments.index', compact('bereiche', 'mitarbeiter'));
    }

    // neuen bereich anlegen
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
        ], [
            'name.required' => 'Bitte einen Bereichsnamen eingeben.',
            'name.unique'   => 'Dieser Bereichsname existiert bereits.',
            'name.max'      => 'Der Name darf maximal 100 Zeichen lang sein.',
        ]);

        Department::create(['name' => $request->name]);

        return back()->with('success', "Bereich \"{$request->name}\" wurde angelegt.");
    }

    // bereich löschen (nur wenn keine mitarbeiter mehr drin)
    public function destroy(Department $department)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $name = $department->name;
        // pivot-einträge werden durch cascadeOnDelete automatisch gelöscht
        $department->delete();

        return back()->with('success', "Bereich \"{$name}\" wurde gelöscht.");
    }

    // mitarbeiter einem bereich zuweisen
    public function addUser(Request $request, Department $department)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);

        // prüfen ob dieser nutzer wirklich mitarbeiter ist
        if (!$user->hasRole('mitarbeiter')) {
            return back()->withErrors(['user_id' => 'Nur Mitarbeiter können Bereichen zugewiesen werden.']);
        }

        // attach ignoriert wenn der nutzer schon drin ist (wegen primary key constraint)
        $department->users()->syncWithoutDetaching([$user->id]);

        return back()->with('success', "{$user->name} wurde dem Bereich \"{$department->name}\" zugewiesen.");
    }

    // mitarbeiter aus einem bereich entfernen
    public function removeUser(Department $department, User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $department->users()->detach($user->id);

        return back()->with('success', "{$user->name} wurde aus dem Bereich \"{$department->name}\" entfernt.");
    }
}
