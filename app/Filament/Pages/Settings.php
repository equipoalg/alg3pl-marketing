<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

/**
 * /admin/settings — user-level UI preferences.
 *
 * Three sections: Apariencia (theme + density variant), Cuenta (read-only profile), Notificaciones.
 * Persists to users.preferences JSON column AND to session for instant effect on next page load.
 *
 * Theme switcher is wired but only Light is currently active — Dark is a placeholder for future work.
 * Variant A is kept accessible here so users who prefer the wide sidebar can opt in (B is the new default).
 */
class Settings extends Page
{
    protected string $view = 'filament.pages.settings';
    protected Width|string|null $maxContentWidth = Width::Full;

    public string $variant = 'b';
    public string $theme = 'light';
    public bool $notifyEmail = true;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationLabel(): string
    {
        return 'Opciones';
    }

    public static function getNavigationGroup(): ?string
    {
        return null; // bottom of sidebar, ungrouped
    }

    public static function getNavigationSort(): int
    {
        return 99;
    }

    public static function getSlug(): string
    {
        return 'settings';
    }

    public function getTitle(): string
    {
        return 'Opciones';
    }

    public function mount(): void
    {
        $u = auth()->user();
        $this->variant     = $u?->pref('variant', session('admin_variant', 'b')) ?? 'b';
        $this->theme       = $u?->pref('theme', 'light') ?? 'light';
        $this->notifyEmail = (bool) ($u?->pref('notify_email', true) ?? true);
    }

    public function selectVariant(string $value): void
    {
        if (! in_array($value, ['a', 'b'], true)) {
            return;
        }
        $this->variant = $value;
    }

    public function selectTheme(string $value): void
    {
        // Only 'light' is supported today. Block 'dark' even if someone wires it from the UI.
        if ($value !== 'light') {
            return;
        }
        $this->theme = $value;
    }

    public function save(): void
    {
        $u = auth()->user();
        if (! $u) {
            return;
        }

        $u->setPrefs([
            'variant'      => $this->variant,
            'theme'        => $this->theme,
            'notify_email' => $this->notifyEmail,
        ])->save();

        // Session takes immediate effect on next render — no need to wait for next login.
        session(['admin_variant' => $this->variant]);

        Notification::make()
            ->title('Preferencias guardadas')
            ->body('Tus opciones se aplicarán en cada página.')
            ->success()
            ->send();
    }
}
