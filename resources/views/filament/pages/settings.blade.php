<x-filament-panels::page>
    @php
        $user = auth()->user();
        $roleLabel = $user?->role ? ucfirst($user->role) : 'usuario';
    @endphp

    <div style="display:flex;flex-direction:column;gap:16px;max-width:880px;">

        {{-- ── APARIENCIA ── --}}
        <section style="background:var(--alg-surface);border:1px solid var(--alg-line);">
            <div style="padding:18px 22px 14px;border-bottom:1px solid var(--alg-line);">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:var(--alg-ink-3);margin:0 0 4px;">Apariencia</p>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:16px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Tema y densidad</h2>
            </div>

            <div style="padding:18px 22px;display:flex;flex-direction:column;gap:18px;">

                {{-- Tema --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;">
                    <div style="flex:1;min-width:0;">
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">Tema visual</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-3);margin:0;line-height:1.55;">Modo claro u oscuro. <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);">El modo oscuro estará disponible próximamente.</span></p>
                    </div>
                    <div style="display:inline-flex;border:1px solid var(--alg-line);padding:2px;background:var(--alg-surface);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;letter-spacing:.04em;flex-shrink:0;">
                        <button type="button"
                            wire:click="selectTheme('light')"
                            style="padding:5px 12px;text-decoration:none;border:none;cursor:pointer;background:{{ $theme === 'light' ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $theme === 'light' ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                            Claro
                        </button>
                        <button type="button"
                            disabled
                            title="Próximamente"
                            style="padding:5px 12px;border:none;cursor:not-allowed;background:transparent;color:var(--alg-ink-5);font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;opacity:0.5;">
                            Oscuro
                        </button>
                    </div>
                </div>

                {{-- Variante / densidad --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;padding-top:14px;border-top:1px solid var(--alg-line);">
                    <div style="flex:1;min-width:0;">
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">Densidad del sidebar</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-3);margin:0;line-height:1.55;">
                            <strong style="color:var(--alg-ink-2);font-weight:500;">Compacto</strong> — sidebar de 56px que se expande al pasar el cursor (recomendado, más espacio para el contenido).<br>
                            <strong style="color:var(--alg-ink-2);font-weight:500;">Expandido</strong> — sidebar fijo de 224px siempre visible.
                        </p>
                    </div>
                    <div style="display:inline-flex;border:1px solid var(--alg-line);padding:2px;background:var(--alg-surface);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;letter-spacing:.04em;flex-shrink:0;">
                        <button type="button"
                            wire:click="selectVariant('b')"
                            style="padding:5px 12px;border:none;cursor:pointer;background:{{ $variant === 'b' ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $variant === 'b' ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                            Compacto
                        </button>
                        <button type="button"
                            wire:click="selectVariant('a')"
                            style="padding:5px 12px;border:none;cursor:pointer;background:{{ $variant === 'a' ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $variant === 'a' ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                            Expandido
                        </button>
                    </div>
                </div>

            </div>
        </section>

        {{-- ── CUENTA ── --}}
        <section style="background:var(--alg-surface);border:1px solid var(--alg-line);">
            <div style="padding:18px 22px 14px;border-bottom:1px solid var(--alg-line);">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:var(--alg-ink-3);margin:0 0 4px;">Cuenta</p>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:16px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Tu información</h2>
            </div>
            <div style="padding:18px 22px;display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid var(--alg-line);">
                <div style="padding:0 18px 0 0;border-right:1px solid var(--alg-line);">
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 6px;">Nombre</p>
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">{{ $user?->name ?? '—' }}</p>
                </div>
                <div style="padding:0 18px;border-right:1px solid var(--alg-line);">
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 6px;">Email</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;color:var(--alg-ink);margin:0;">{{ $user?->email ?? '—' }}</p>
                </div>
                <div style="padding:0 0 0 18px;">
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 6px;">Rol</p>
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">{{ $roleLabel }}</p>
                </div>
            </div>
            <div style="padding:14px 22px;background:var(--alg-surface-2);">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Para cambiar nombre, email o contraseña, contactá al administrador.</p>
            </div>
        </section>

        {{-- ── NOTIFICACIONES ── --}}
        <section style="background:var(--alg-surface);border:1px solid var(--alg-line);">
            <div style="padding:18px 22px 14px;border-bottom:1px solid var(--alg-line);">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:var(--alg-ink-3);margin:0 0 4px;">Notificaciones</p>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:16px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Cómo recibir alertas</h2>
            </div>
            <div style="padding:18px 22px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;">
                    <div style="flex:1;min-width:0;">
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">Recibir notificaciones por email</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-3);margin:0;line-height:1.55;">Nuevos leads, asignaciones de tareas y resumen semanal.</p>
                    </div>
                    <label style="display:inline-flex;align-items:center;cursor:pointer;flex-shrink:0;">
                        <input type="checkbox" wire:model.live="notifyEmail" style="appearance:none;width:36px;height:20px;background:{{ $notifyEmail ? 'var(--alg-accent)' : 'var(--alg-line-2)' }};border:none;border-radius:10px;position:relative;cursor:pointer;transition:background 120ms ease;">
                        <span style="position:relative;left:-32px;width:14px;height:14px;background:#FFFFFF;border-radius:50%;transition:transform 120ms ease;transform:translateX({{ $notifyEmail ? '16px' : '0' }});pointer-events:none;"></span>
                    </label>
                </div>
            </div>
        </section>

        {{-- ── SAVE ── --}}
        <div style="display:flex;justify-content:flex-end;padding-top:6px;">
            <button
                type="button"
                wire:click="save"
                style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--alg-ink);color:#FFFFFF;border:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;cursor:pointer;letter-spacing:-0.005em;"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                Guardar preferencias
            </button>
        </div>

    </div>
</x-filament-panels::page>
