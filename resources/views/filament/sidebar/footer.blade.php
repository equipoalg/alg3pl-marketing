{{-- Sidebar footer user block — per Claude Design dashboard-a.jsx:86-99 --}}
@php
    $user = auth()->user();
    $name = $user?->name ?? 'Usuario';
    $role = $user?->role ?? ($user?->is_super_admin ? 'Super admin' : 'Marketing manager');
    // Compute initials from name (first letter of first + last word)
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0] ?? 'N', 0, 1) . substr(end($parts) ?: '', 0, 1));
    if (strlen($initials) === 0) $initials = 'NN';
@endphp

{{-- Variant A: full footer with name + role + settings --}}
<div class="alg-sidebar-footer-a" style="padding:10px;display:flex;align-items:center;gap:10px;">
    <div style="width:26px;height:26px;border-radius:50%;background:#EFF3FB;color:#1E3A8A;display:grid;place-items:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;flex-shrink:0;">{{ $initials }}</div>
    <div style="flex:1;line-height:1.2;min-width:0;">
        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;color:#0F172A;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $name }}</div>
        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#94A3B8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $role }}</div>
    </div>
    <a href="/admin/profile" style="color:#94A3B8;text-decoration:none;flex-shrink:0;display:grid;place-items:center;width:18px;height:18px;" title="Configuración">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="2.5"/><path d="M10 2v2M10 16v2M2 10h2M16 10h2M4.2 4.2l1.5 1.5M14.3 14.3l1.5 1.5M4.2 15.8l1.5-1.5M14.3 5.7l1.5-1.5"/></svg>
    </a>
</div>

{{-- Variant B: compact icon-rail bottom (settings + LA avatar) --}}
{{-- Hidden by default, shown via CSS when body[data-alg-variant="b"] --}}
<div class="alg-sidebar-footer-b" style="display:none;flex-direction:column;align-items:center;gap:8px;padding:10px 0;">
    <a href="/admin/profile" style="width:36px;height:36px;border-radius:7px;background:transparent;display:grid;place-items:center;color:rgba(255,255,255,0.55);text-decoration:none;transition:background 150ms ease-out;" onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.color='#FFFFFF';" onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,0.55)';" title="Configuración">
        <svg width="17" height="17" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="2.5"/><path d="M10 2v2M10 16v2M2 10h2M16 10h2M4.2 4.2l1.5 1.5M14.3 14.3l1.5 1.5M4.2 15.8l1.5-1.5M14.3 5.7l1.5-1.5"/></svg>
    </a>
    <div style="width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,0.10);display:grid;place-items:center;color:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;">{{ $initials }}</div>
</div>
