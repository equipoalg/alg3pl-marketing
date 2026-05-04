{{-- Keyboard shortcuts cheat sheet — shown when Alpine `showHelp` is truthy.
     Expects: parent x-data exposes showHelp toggle. --}}
<div x-show="showHelp" x-cloak x-transition.opacity
     @click.self="showHelp = false"
     @keydown.escape.window="showHelp = false"
     style="position:fixed;inset:0;background:rgba(12,10,9,0.55);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);z-index:2000;display:flex;align-items:center;justify-content:center;">
    <div style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:8px;box-shadow:0 24px 48px rgba(0,0,0,0.30);padding:22px 26px;min-width:340px;max-width:480px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;">
        <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
            <h3 style="margin:0;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Atajos de teclado</h3>
            <button type="button" @click="showHelp = false"
                    style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;font-size:18px;line-height:1;padding:2px 6px;">×</button>
        </div>
        <div style="display:grid;grid-template-columns:auto 1fr;gap:10px 14px;font-size:12.5px;align-items:center;">
            @php
                $shortcuts = [
                    '/'        => 'Buscar tareas',
                    'c'        => 'Crear nueva tarea',
                    'l'        => 'Cambiar a vista Lista',
                    'k'        => 'Cambiar a vista Kanban',
                    'j / k'    => 'Navegar entre tareas (lista)',
                    'Enter'    => 'Abrir detalle de la tarea seleccionada',
                    '1 / 2 / 3 / 4' => 'Asignar P0 / P1 / P2 / P3 a la seleccionada',
                    'Esc'      => 'Cerrar panel de detalle',
                    '?'        => 'Mostrar/ocultar esta ayuda',
                ];
            @endphp
            @foreach($shortcuts as $key => $action)
                <kbd style="display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:22px;padding:0 7px;background:var(--alg-bg);border:1px solid var(--alg-line);border-bottom:2px solid var(--alg-line-2);border-radius:4px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;color:var(--alg-ink-2);">{{ $key }}</kbd>
                <span style="color:var(--alg-ink-3);">{{ $action }}</span>
            @endforeach
        </div>
        <p style="margin:14px 0 0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);letter-spacing:.04em;">Los atajos se desactivan mientras escribís en un input.</p>
    </div>
</div>
