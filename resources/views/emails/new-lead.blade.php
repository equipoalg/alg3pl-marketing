<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f5;padding:20px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #e4e4e7;">
    <div style="background:#0F172A;padding:20px 24px;">
        <h1 style="color:#fff;font-size:18px;margin:0;">ALG3PL — Nuevo Lead</h1>
    </div>
    <div style="padding:24px;">
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:#71717a;width:120px;">Nombre</td><td style="padding:8px 0;font-weight:600;">{{ $lead->name }}</td></tr>
            <tr><td style="padding:8px 0;color:#71717a;">Email</td><td style="padding:8px 0;">{{ $lead->email ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:#71717a;">Telefono</td><td style="padding:8px 0;">{{ $lead->phone ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:#71717a;">Empresa</td><td style="padding:8px 0;">{{ $lead->company ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:#71717a;">Pais</td><td style="padding:8px 0;">{{ $lead->country?->name ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:#71717a;">Servicio</td><td style="padding:8px 0;">{{ $lead->service_interest ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:#71717a;">Score</td><td style="padding:8px 0;font-weight:700;color:#3b82f6;">{{ $lead->score ?? 0 }}</td></tr>
        </table>
        @if($lead->notes)
        <div style="margin-top:16px;padding:12px;background:#f9fafb;border-radius:6px;border:1px solid #e4e4e7;">
            <p style="font-size:12px;color:#71717a;margin:0 0 4px;font-weight:600;">MENSAJE</p>
            <p style="font-size:14px;color:#374151;margin:0;line-height:1.5;">{{ $lead->notes }}</p>
        </div>
        @endif
        <div style="margin-top:20px;">
            <a href="{{ config('app.url') }}/admin/leads/{{ $lead->id }}/edit" style="display:inline-block;padding:10px 20px;background:#3b82f6;color:#fff;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">Ver Lead en Panel</a>
        </div>
    </div>
    <div style="background:#f9fafb;padding:12px 24px;font-size:11px;color:#a1a1aa;border-top:1px solid #e4e4e7;">
        ALG3PL Marketing Platform
    </div>
</div>
</body></html>
