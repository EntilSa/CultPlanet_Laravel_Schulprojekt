<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// startseite leitet direkt zum shop weiter – keine leere zwischenstation mehr
Route::redirect('/', '/shop')->name('home');

// Statische Seiten
Route::get('/impressum', function () {
    return view('pages.impressum');
})->name('impressum');

Route::get('/datenschutz', function () {
    return view('pages.datenschutz');
})->name('datenschutz');

// Profil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Shop – Produktliste und Detailseite (öffentlich)
Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ProductController::class, 'show'])->name('shop.show');

// Produkt-Verwaltung – nur für eingeloggte Admins
Route::middleware('auth')->group(function () {
    Route::get('/admin/produkte/neu', [ProductController::class, 'create'])->name('products.create');
    Route::post('/admin/produkte', [ProductController::class, 'store'])->name('products.store');
    Route::get('/admin/produkte/{product}/bearbeiten', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('/admin/produkte/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/admin/produkte/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// Warenkorb (Phase 2)
Route::get('/warenkorb', [CartController::class, 'index'])->name('cart.index');
Route::post('/warenkorb/{product}/hinzufuegen', [CartController::class, 'add'])->name('cart.add');
Route::delete('/warenkorb/{product}/entfernen', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/warenkorb/leeren', [CartController::class, 'clear'])->name('cart.clear');

// Checkout + Bestellbestätigung – nur für eingeloggte Nutzer
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [OrderController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/bestellung/{order}/zahlung', [OrderController::class, 'payment'])->name('orders.payment');
    Route::post('/bestellung/{order}/zahlung', [OrderController::class, 'completePayment'])->name('orders.complete_payment');
    Route::get('/bestellung/{order}/danke', [OrderController::class, 'success'])->name('orders.success');
    Route::get('/meine-bestellungen', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::get('/meine-bestellungen/{order}/pdf', [OrderController::class, 'downloadPdf'])->name('orders.pdf');
});

// Reviews – nur für eingeloggte Nutzer
Route::middleware('auth')->group(function () {
    Route::post('/shop/{product}/bewertung', [ReviewController::class, 'store'])->name('reviews.store');
});

// Admin-Bereich – Dashboard, Bestellungen, Nutzer, Verkaufsübersicht
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/produkte', [AdminController::class, 'products'])->name('products');
    Route::get('/bestellungen', [AdminController::class, 'orders'])->name('orders');
    Route::patch('/bestellungen/{order}/status', [AdminController::class, 'orderUpdate'])->name('orders.update');
    Route::get('/nutzer', [AdminController::class, 'users'])->name('users');
    Route::patch('/nutzer/{user}/rolle', [AdminController::class, 'userRoleUpdate'])->name('users.role');
    Route::get('/verkauf', [AdminController::class, 'sales'])->name('sales');

    // Mitarbeiterverwaltung – Bereiche anlegen, Mitarbeiter zuweisen/entfernen
    Route::get('/bereiche', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/bereiche', [DepartmentController::class, 'store'])->name('departments.store');
    Route::delete('/bereiche/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    Route::post('/bereiche/{department}/mitarbeiter', [DepartmentController::class, 'addUser'])->name('departments.addUser');
    Route::delete('/bereiche/{department}/mitarbeiter/{user}', [DepartmentController::class, 'removeUser'])->name('departments.removeUser');
});

// Auktion-Verwaltung (admin) – auktion für ein produkt planen oder löschen
Route::middleware('auth')->group(function () {
    Route::post('/admin/produkte/{product}/auktion', [AuctionController::class, 'store'])->name('auctions.store');
    Route::delete('/admin/auktionen/{auction}', [AuctionController::class, 'destroy'])->name('auctions.destroy');
});

// Auktions-Frontend – übersicht öffentlich, gebot nur eingeloggt
Route::get('/auktion', [AuctionController::class, 'index'])->name('auction.index');
Route::get('/auktion/{auction}', [AuctionController::class, 'show'])->name('auction.show');
Route::middleware('auth')->post('/auktion/{auction}/gebot', [AuctionController::class, 'bid'])->name('auction.bid');

require __DIR__.'/auth.php';
