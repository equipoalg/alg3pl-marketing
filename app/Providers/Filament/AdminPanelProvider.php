<?php

namespace App\Providers\Filament;

use App\Http\Middleware\TenantScope;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('ALG3PL')
            // Brand block injected via SIDEBAR_LOGO_BEFORE hook below;
            // Filament's default text-logo is hidden via CSS (.fi-sidebar-header > a > span).
            ->darkMode(false)
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Geist')
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => '
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#FAFAF9">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="ALG3PL">
<script>if(\'serviceWorker\' in navigator) navigator.serviceWorker.register(\'/sw.js\');</script>
',
            )
            ->renderHook(
                // Load AT THE END of <head> so we WIN cascade vs Filament's app.css
                // (Filament loads its CSS via assets() before HEAD_END fires).
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="' . asset('css/alg.css') . '?v=' . (file_exists(public_path('css/alg.css')) ? filemtime(public_path('css/alg.css')) : time()) . '">',
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                function (): string {
                    try {
                        return view('filament.login-helper')->render();
                    } catch (\Throwable $e) {
                        report($e);
                        return '';
                    }
                },
            )
            ->navigationGroups([
                'Marketing search',
                'CRM',
                'Marketing',
                'Analytics',
                'Settings',
            ])
            ->navigationItems([
                // Dashboard lives at /admin/dashboard via a custom web route
                // (not a Filament Page), so we add it here as a nav item.
                NavigationItem::make('Dashboard')
                    ->url('/admin/dashboard')
                    ->icon('heroicon-o-squares-2x2')
                    ->isActiveWhen(fn () => request()->is('admin/dashboard'))
                    ->sort(-2),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                TenantScope::class,
            ]);
    }
}
