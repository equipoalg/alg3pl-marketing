<?php

use App\Http\Controllers\CampaignArchiveController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\SmartlinkController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\WebhookInboundController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing page — redirige a /admin si ya está autenticado
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }
    return view('welcome');
});

// Login real desde la landing page
Route::post('/portal-login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/admin');
    }

    return back()
        ->withErrors(['email' => 'Credenciales incorrectas. Verifica tu email y contraseña.'])
        ->withInput($request->only('email'));
})->middleware('throttle:10,1')->name('portal.login');

// Smartlinks (auto-tag on click)
Route::get('/sl/{slug}', [SmartlinkController::class, 'redirect'])->name('smartlink.redirect');

// Email verification (double opt-in)
Route::get('/verify-email/{token}', [VerifyEmailController::class, 'verify'])->name('email.verify');
Route::get('/unsubscribe', [VerifyEmailController::class, 'unsubscribe'])->name('email.unsubscribe');

// Campaign archives (public)
Route::get('/newsletters', [CampaignArchiveController::class, 'index'])->name('campaigns.archive');
Route::get('/newsletters/{campaign}', [CampaignArchiveController::class, 'show'])->name('campaigns.show');

// Inbound webhooks
Route::post('/webhook/{webhookId}', [WebhookInboundController::class, 'handle'])->name('webhook.inbound');

// Print / PDF export (auth-gated, no CSRF needed for GET)
Route::get('/reports/{report}/print', [PrintController::class, 'countryReport'])->name('country-report.print');
