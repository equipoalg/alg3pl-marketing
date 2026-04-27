{{--
    ALG layout override — replaces Filament's default sidebar+topbar with the
    Claude Design bundle's chrome. Sidebar variant (a/b) resolved from query
    string, falls back to session, defaults to 'a'. Filament's page content
    renders inside the {{ $slot }}.
--}}
@php
    use App\Support\DashboardMockData;

    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
    $navSections = DashboardMockData::navSections();

    // Variant resolution: query param wins (and persists to session), then session, then 'a'.
    $variantQuery = request()->query('variant');
    if (in_array($variantQuery, ['a', 'b'], true)) {
        session(['admin_variant' => $variantQuery]);
        $variant = $variantQuery;
    } else {
        $variant = session('admin_variant', 'a');
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div style="display:flex;height:100vh;background:var(--bg);overflow:hidden;">

        {{-- Sidebar (variant A wide / variant B icon-rail-expandable) --}}
        @if($variant === 'b')
            @include('alg-dashboard.sidebar-b', ['navSections' => $navSections])
        @else
            @include('alg-dashboard.sidebar-a', ['navSections' => $navSections])
        @endif

        <div style="flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden;">

            {{-- Topbar (breadcrumb + buttons + A/B switch) --}}
            @include('alg-dashboard.topbar', ['variant' => $variant])

            {{-- Main content — Filament page renders here --}}
            <main class="alg-main" style="flex:1;overflow:auto;padding:24px 28px;background:var(--bg);">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Filament's livewire/notification/modal infrastructure stays as-is --}}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::LAYOUT_END, scopes: $renderHookScopes) }}
</x-filament-panels::layout.base>
