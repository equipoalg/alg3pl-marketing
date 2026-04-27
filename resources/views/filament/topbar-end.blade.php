{{-- Topbar end: Buscar (⌘K) + bell + Nuevo lead (per Claude Design) --}}
<div style="display:flex;align-items:center;gap:8px;">
    {{-- Buscar pill triggers Filament's global search modal via the keyboard shortcut.
         Click also dispatches the same shortcut so it works without keyboard. --}}
    <button type="button"
            x-data
            x-on:click="document.querySelector('.fi-global-search-field input')?.focus() || $dispatch('keydown.cmd.k', { bubbles: true })"
            style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:6px;border:1px solid #E2E8F0;background:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;cursor:pointer;transition:background 150ms ease-out;"
            onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="9" r="5"/><path d="M13 13l4 4"/></svg>
        <span>Buscar</span>
        <span style="font-family:ui-monospace,monospace;font-size:11px;padding:2px 6px;border:1px solid #E2E8F0;border-radius:4px;background:#FFFFFF;color:#64748B;margin-left:4px;">⌘K</span>
    </button>
    <button type="button" style="width:30px;height:30px;border-radius:6px;border:1px solid #E2E8F0;background:#FFFFFF;display:grid;place-items:center;color:#64748B;cursor:pointer;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
        <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13V9a5 5 0 0110 0v4l1 2H4l1-2zM8 17a2 2 0 004 0"/></svg>
    </button>
    <a href="/admin/leads/create" style="display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid #0F172A;background:#0F172A;color:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;text-decoration:none;transition:opacity 150ms ease-out;" onmouseover="this.style.opacity='.86'" onmouseout="this.style.opacity='1'">
        <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4v12M4 10h12"/></svg>
        Nuevo lead
    </a>
</div>
