<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Startseite
Route::get('/', function () {
    return view('pages.startseite');
})->name('home');

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
    Route::get('/bestellung/{order}/danke', [OrderController::class, 'success'])->name('orders.success');
});

// TODO Reviews (folgt noch)
// Route::post('/shop/{product}/bewertung', [ReviewController::class, 'store'])->name('reviews.store');

// TODO Spezialisierung: Auktion
// Route::get('/auktion', [AuctionController::class, 'index'])->name('auction.index');

require __DIR__.'/auth.php';
