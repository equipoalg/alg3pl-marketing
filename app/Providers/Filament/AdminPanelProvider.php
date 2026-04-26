<?php

namespace App\Providers\Filament;

use App\Http\Middleware\TenantScope;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
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
            ->assets([
                // Cache-bust the design CSS via mtime so browser fetches the
                // latest after every deploy/edit (no manual hard-refresh needed).
                Css::make(
                    'alg-design-system',
                    asset('css/alg.css') . '?v=' . (file_exists(public_path('css/alg.css')) ? filemtime(public_path('css/alg.css')) : time())
                ),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => '
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#F7F5F0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="ALG3PL">
<script>if(\'serviceWorker\' in navigator) navigator.serviceWorker.register(\'/sw.js\');</script>
',
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => view('filament.topbar-start')->render(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => view('filament.topbar-end')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_LOGO_BEFORE,
                fn (): string => view('filament.sidebar.brand')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => view('filament.sidebar.workspace')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): string => view('filament.sidebar.footer')->render(),
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
