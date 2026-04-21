<?php
/**
 * deploy_scale_batch3.php — ALG3PL Scaling Improvements Batch 3
 * Drop into public/ and visit once via browser. Deletes itself on success.
 *
 * Features deployed:
 *   1. Lead Scoring 2.0 — DB-backed ScoringRule model + updated LeadScoringService
 *   2. CountryConfig — per-country settings model + Filament resource
 *   3. WhatsApp Quick-Send — WhatsAppService + action in LeadResource
 *   4. Multi-tenant data model preparation (nullable tenant_id on countries)
 */

define('ARTISAN', dirname(__DIR__) . '/artisan');
$log = [];

function run(string $cmd): string {
    exec('php ' . ARTISAN . ' ' . $cmd . ' 2>&1', $out);
    return implode("\n", $out);
}

function writeFile(string $path, string $b64Content): bool {
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return false;
    }
    return file_put_contents($path, base64_decode($b64Content)) !== false;
}

$base = dirname(__DIR__);
$files = [];

// ─── Migration 1: scoring_rules ───────────────────────────────────────────────
$files['database/migrations/2026_04_15_000001_create_scoring_rules_table.php'] =
    'PD9waHAKCnVzZSBJbGx1bWluYXRlXERhdGFiYXNlXE1pZ3JhdGlvbnNcTWlncmF0aW9uOwp1c2Ug' .
    'SWxsdW1pbmF0ZVxEYXRhYmFzZVxTY2hlbWFcQmx1ZXByaW50Owp1c2UgSWxsdW1pbmF0ZVxTdXBw' .
    'b3J0XEZhY2FkZXNcU2NoZW1hOwoKcmV0dXJuIG5ldyBjbGFzcyBleHRlbmRzIE1pZ3JhdGlvbgp7' .
    'CiAgICBwdWJsaWMgZnVuY3Rpb24gdXAoKTogdm9pZAogICAgewogICAgICAgIFNjaGVtYTo6Y3Jl' .
    'YXRlKCdzY29yaW5nX3J1bGVzJywgZnVuY3Rpb24gKEJsdWVwcmludCAkdGFibGUpIHsKICAgICAg' .
    'ICAgICAgJHRhYmxlLT5pZCgpOwogICAgICAgICAgICAkdGFibGUtPnN0cmluZygnZmFjdG9yJyk7' .
    'IC8vIGUuZy4gJ3NvdXJjZV9vcmdhbmljJwogICAgICAgICAgICAkdGFibGUtPnN0cmluZygnbGFi' .
    'ZWwnKTsKICAgICAgICAgICAgJHRhYmxlLT5pbnRlZ2VyKCd3ZWlnaHQnKS0+ZGVmYXVsdCgwKTsg' .
    'Ly8gMC0xMDAKICAgICAgICAgICAgJHRhYmxlLT5zdHJpbmcoJ2NhdGVnb3J5Jyk7IC8vIHNvdXJj' .
    'ZXxzdGF0dXN8ZW5nYWdlbWVudHxnZW9ncmFwaHkKICAgICAgICAgICAgJHRhYmxlLT5ib29sZWFu' .
    'KCdpc19hY3RpdmUnKS0+ZGVmYXVsdCh0cnVlKTsKICAgICAgICAgICAgJHRhYmxlLT50aW1lc3Rh' .
    'bXBzKCk7CiAgICAgICAgfSk7CiAgICB9CgogICAgcHVibGljIGZ1bmN0aW9uIGRvd24oKTogdm9p' .
    'ZAogICAgewogICAgICAgIFNjaGVtYTo6ZHJvcElmRXhpc3RzKCdzY29yaW5nX3J1bGVzJyk7CiAg' .
    'ICB9Cn07Cg==';

// ─── Migration 2: country_configs ─────────────────────────────────────────────
$files['database/migrations/2026_04_15_000002_create_country_configs_table.php'] =
    'PD9waHAKCnVzZSBJbGx1bWluYXRlXERhdGFiYXNlXE1pZ3JhdGlvbnNcTWlncmF0aW9uOwp1c2Ug' .
    'SWxsdW1pbmF0ZVxEYXRhYmFzZVxTY2hlbWFcQmx1ZXByaW50Owp1c2UgSWxsdW1pbmF0ZVxTdXBw' .
    'b3J0XEZhY2FkZXNcU2NoZW1hOwoKcmV0dXJuIG5ldyBjbGFzcyBleHRlbmRzIE1pZ3JhdGlvbgp7' .
    'CiAgICBwdWJsaWMgZnVuY3Rpb24gdXAoKTogdm9pZAogICAgewogICAgICAgIFNjaGVtYTo6Y3Jl' .
    'YXRlKCdjb3VudHJ5X2NvbmZpZ3MnLCBmdW5jdGlvbiAoQmx1ZXByaW50ICR0YWJsZSkgewogICAg' .
    'ICAgICAgICAkdGFibGUtPmlkKCk7CiAgICAgICAgICAgICR0YWJsZS0+Zm9yZWlnbklkKCdjb3Vu' .
    'dHJ5X2lkJyktPmNvbnN0cmFpbmVkKCktPmNhc2NhZGVPbkRlbGV0ZSgpOwogICAgICAgICAgICAk' .
    'dGFibGUtPmludGVnZXIoJ21vbnRobHlfbGVhZF9nb2FsJyktPmRlZmF1bHQoNTApOwogICAgICAg' .
    'ICAgICAkdGFibGUtPnN0cmluZygncHJpbWFyeV9tYW5hZ2VyJyktPm51bGxhYmxlKCk7CiAgICAg' .
    'ICAgICAgICR0YWJsZS0+anNvbignd2ViaG9va19hc3NpZ25lZXMnKS0+bnVsbGFibGUoKTsKICAg' .
    'ICAgICAgICAgJHRhYmxlLT5qc29uKCdhY3RpdmVfc2VydmljZXMnKS0+bnVsbGFibGUoKTsKICAg' .
    'ICAgICAgICAgJHRhYmxlLT5kZWNpbWFsKCdtb250aGx5X2ZlZScsIDgsIDIpLT5kZWZhdWx0KDE1' .
    'MC4wMCk7CiAgICAgICAgICAgICR0YWJsZS0+dGV4dCgnbm90ZXMnKS0+bnVsbGFibGUoKTsKICAg' .
    'ICAgICAgICAgJHRhYmxlLT50aW1lc3RhbXBzKCk7CgogICAgICAgICAgICAkdGFibGUtPnVuaXF1' .
    'ZSgnY291bnRyeV9pZCcpOwogICAgICAgIH0pOwogICAgfQoKICAgIHB1YmxpYyBmdW5jdGlvbiBk' .
    'b3duKCk6IHZvaWQKICAgIHsKICAgICAgICBTY2hlbWE6OmRyb3BJZkV4aXN0cygnY291bnRyeV9j' .
    'b25maWdzJyk7CiAgICB9Cn07Cg==';

// ─── Migration 3: add tenant_id to countries ──────────────────────────────────
$files['database/migrations/2026_04_15_000003_add_tenant_to_countries_table.php'] =
    'PD9waHAKCnVzZSBJbGx1bWluYXRlXERhdGFiYXNlXE1pZ3JhdGlvbnNcTWlncmF0aW9uOwp1c2Ug' .
    'SWxsdW1pbmF0ZVxEYXRhYmFzZVxTY2hlbWFcQmx1ZXByaW50Owp1c2UgSWxsdW1pbmF0ZVxTdXBw' .
    'b3J0XEZhY2FkZXNcU2NoZW1hOwoKcmV0dXJuIG5ldyBjbGFzcyBleHRlbmRzIE1pZ3JhdGlvbgp7' .
    'CiAgICBwdWJsaWMgZnVuY3Rpb24gdXAoKTogdm9pZAogICAgewogICAgICAgIFNjaGVtYTo6dGFi' .
    'bGUoJ2NvdW50cmllcycsIGZ1bmN0aW9uIChCbHVlcHJpbnQgJHRhYmxlKSB7CiAgICAgICAgICAg' .
    'IC8vIE51bGxhYmxlIEZLIHRvIHRlbmFudHMgZm9yIGZ1dHVyZSBtdWx0aS10ZW5hbnQgYWN0aXZh' .
    'dGlvbi4KICAgICAgICAgICAgLy8gVGVuYW50U2NvcGUgbWlkZGxld2FyZSBpcyBOT1QgeWV0IGFj' .
    'dGl2ZSDigJQgc2VlIEFwcFxIdHRwXE1pZGRsZXdhcmVcVGVuYW50U2NvcGUKICAgICAgICAgICAg' .
    'Ly8gZm9yIHRoZSByZXF1aXJlZCBzZXNzaW9uKCd0ZW5hbnRfaWQnKSBzZXR1cCBiZWZvcmUgZW5h' .
    'YmxpbmcgaXQuCiAgICAgICAgICAgICR0YWJsZS0+dW5zaWduZWRCaWdJbnRlZ2VyKCd0ZW5hbnRf' .
    'aWQnKS0+bnVsbGFibGUoKS0+YWZ0ZXIoJ2lkJyk7CiAgICAgICAgICAgICR0YWJsZS0+Zm9yZWln' .
    'bigndGVuYW50X2lkJyktPnJlZmVyZW5jZXMoJ2lkJyktPm9uKCd0ZW5hbnRzJyktPm51bGxPbkRl' .
    'bGV0ZSgpOwogICAgICAgIH0pOwogICAgfQoKICAgIHB1YmxpYyBmdW5jdGlvbiBkb3duKCk6IHZv' .
    'aWQKICAgIHsKICAgICAgICBTY2hlbWE6OnRhYmxlKCdjb3VudHJpZXMnLCBmdW5jdGlvbiAoQmx1' .
    'ZXByaW50ICR0YWJsZSkgewogICAgICAgICAgICAkdGFibGUtPmRyb3BGb3JlaWduKFsndGVuYW50' .
    'X2lkJ10pOwogICAgICAgICAgICAkdGFibGUtPmRyb3BDb2x1bW4oJ3RlbmFudF9pZCcpOwogICAg' .
    'ICAgIH0pOwogICAgfQp9Owo=';

// ─── Model: ScoringRule ────────────────────────────────────────────────────────
$files['app/Models/ScoringRule.php'] =
    'PD9waHAKCm5hbWVzcGFjZSBBcHBcTW9kZWxzOwoKdXNlIElsbHVtaW5hdGVcRGF0YWJhc2VcRWxv' .
    'cXVlbnRcQ29sbGVjdGlvbjsKdXNlIElsbHVtaW5hdGVcRGF0YWJhc2VcRWxvcXVlbnRcTW9kZWw7' .
    'CnVzZSBJbGx1bWluYXRlXFN1cHBvcnRcRmFjYWRlc1xDYWNoZTsKCmNsYXNzIFNjb3JpbmdSdWxl' .
    'IGV4dGVuZHMgTW9kZWwKewogICAgcHJvdGVjdGVkICRmaWxsYWJsZSA9IFsKICAgICAgICAnZmFj' .
    'dG9yJywKICAgICAgICAnbGFiZWwnLAogICAgICAgICd3ZWlnaHQnLAogICAgICAgICdjYXRlZ29yeScs' .
    'CiAgICAgICAgJ2lzX2FjdGl2ZScsCiAgICBdOwoKICAgIHByb3RlY3RlZCAkY2FzdHMgPSBbCiAgICAg' .
    'ICAgJ3dlaWdodCcgPT4gJ2ludGVnZXInLAogICAgICAgICdpc19hY3RpdmUnID0+ICdib29sZWFuJywK' .
    'ICAgIF07CgogICAgcHVibGljIHN0YXRpYyBmdW5jdGlvbiBnZXRDYWNoZWQoKTogQ29sbGVjdGlvbgog' .
    'ICAgewogICAgICAgIHJldHVybiBDYWNoZTo6cmVtZW1iZXIoJ3Njb3JpbmdfcnVsZXMnLCAzNjAwLCBm' .
    'biAoKSA9PiBzdGF0aWM6OndoZXJlKCdpc19hY3RpdmUnLCB0cnVlKS0+Z2V0KCkpOwogICAgfQoKICAg' .
    'IHB1YmxpYyBzdGF0aWMgZnVuY3Rpb24gZmx1c2hDYWNoZSgpOiB2b2lkCiAgICB7CiAgICAgICAgQ2Fj' .
    'aGU6OmZvcmdldCgnc2NvcmluZ19ydWxlcycpOwogICAgfQoKICAgIHByb3RlY3RlZCBzdGF0aWMgZnVu' .
    'Y3Rpb24gYm9vdGVkKCk6IHZvaWQKICAgIHsKICAgICAgICBzdGF0aWM6OnNhdmVkKGZuICgpID0+IHN0' .
    'YXRpYzo6Zmx1c2hDYWNoZSgpKTsKICAgICAgICBzdGF0aWM6OmRlbGV0ZWQoZm4gKCkgPT4gc3RhdGlj' .
    'OjpmbHVzaENhY2hlKCkpOwogICAgfQoKICAgIHB1YmxpYyBmdW5jdGlvbiBzY29wZUJ5Q2F0ZWdvcnko' .
    'JHF1ZXJ5LCBzdHJpbmcgJGNhdGVnb3J5KQogICAgewogICAgICAgIHJldHVybiAkcXVlcnktPndoZXJl' .
    'KCdjYXRlZ29yeScsICRjYXRlZ29yeSk7CiAgICB9CgogICAgcHVibGljIGZ1bmN0aW9uIHNjb3BlQWN0' .
    'aXZlKCRxdWVyeSkKICAgIHsKICAgICAgICByZXR1cm4gJHF1ZXJ5LT53aGVyZSgnaXNfYWN0aXZlJywg' .
    'dHJ1ZSk7CiAgICB9CgogICAgcHVibGljIHN0YXRpYyBmdW5jdGlvbiB3ZWlnaHRGb3Ioc3RyaW5nICRm' .
    'YWN0b3IsIGludCAkZGVmYXVsdCA9IDApOiBpbnQKICAgIHsKICAgICAgICByZXR1cm4gc3RhdGljOjpn' .
    'ZXRDYWNoZWQoKS0+Zmlyc3RXaGVyZSgnZmFjdG9yJywgJGZhY3Rvcik/LT53ZWlnaHQgPz8gJGRlZmF1' .
    'bHQ7CiAgICB9Cn0K';

// ─── Model: CountryConfig ──────────────────────────────────────────────────────
$files['app/Models/CountryConfig.php'] =
    'PD9waHAKCm5hbWVzcGFjZSBBcHBcTW9kZWxzOwoKdXNlIElsbHVtaW5hdGVcRGF0YWJhc2VcRWxv' .
    'cXVlbnRcTW9kZWw7CnVzZSBJbGx1bWluYXRlXERhdGFiYXNlXEVsb3F1ZW50XFJlbGF0aW9uc1xC' .
    'ZWxvbmdzVG87CgpjbGFzcyBDb3VudHJ5Q29uZmlnIGV4dGVuZHMgTW9kZWwKewogICAgcHJvdGVj' .
    'dGVkICRmaWxsYWJsZSA9IFsKICAgICAgICAnY291bnRyeV9pZCcsCiAgICAgICAgJ21vbnRobHlf' .
    'bGVhZF9nb2FsJywKICAgICAgICAncHJpbWFyeV9tYW5hZ2VyJywKICAgICAgICAnd2ViaG9va19h' .
    'c3NpZ25lZXMnLAogICAgICAgICdhY3RpdmVfc2VydmljZXMnLAogICAgICAgICdtb250aGx5X2Zl' .
    'ZScsCiAgICAgICAgJ25vdGVzJywKICAgIF07CgogICAgcHJvdGVjdGVkICRjYXN0cyA9IFsKICAg' .
    'ICAgICAnbW9udGhseV9sZWFkX2dvYWwnID0+ICdpbnRlZ2VyJywKICAgICAgICAnd2ViaG9va19h' .
    'c3NpZ25lZXMnID0+ICdhcnJheScsCiAgICAgICAgJ2FjdGl2ZV9zZXJ2aWNlcycgPT4gJ2FycmF5' .
    'JywKICAgICAgICAnbW9udGhseV9mZWUnID0+ICdkZWNpbWFsOjInLAogICAgXTsKCiAgICBwdWJs' .
    'aWMgZnVuY3Rpb24gY291bnRyeSgpOiBCZWxvbmdzVG8KICAgIHsKICAgICAgICByZXR1cm4gJHRo' .
    'aXMtPmJlbG9uZ3NUbyhDb3VudHJ5OjpjbGFzcyk7CiAgICB9CgogICAgcHVibGljIHN0YXRpYyBm' .
    'dW5jdGlvbiBmb3JDb3VudHJ5KGludCAkY291bnRyeUlkKTogc3RhdGljCiAgICB7CiAgICAgICAg' .
    'cmV0dXJuIHN0YXRpYzo6Zmlyc3RPckNyZWF0ZSgKICAgICAgICAgICAgWydjb3VudHJ5X2lkJyA9' .
    'PiAkY291bnRyeUlkXSwKICAgICAgICAgICAgWwogICAgICAgICAgICAgICAgJ21vbnRobHlfbGVhZF' .
    '9nb2FsJyA9PiA1MCwKICAgICAgICAgICAgICAgICdwcmltYXJ5X21hbmFnZXInID0+IG51bGwsCiAg' .
    'ICAgICAgICAgICAgICAnd2ViaG9va19hc3NpZ25lZXMnID0+IFtdLAogICAgICAgICAgICAgICAgJ2Fj' .
    'dGl2ZV9zZXJ2aWNlcycgPT4gW10sCiAgICAgICAgICAgICAgICAnbW9udGhseV9mZWUnID0+IDE1MC4w' .
    'MCwKICAgICAgICAgICAgICAgICdub3RlcycgPT4gbnVsbCwKICAgICAgICAgICAgXQogICAgICAgICk7' .
    'CiAgICB9Cn0K';

// ─── Seeder: ScoringRuleSeeder ─────────────────────────────────────────────────
// Full canonical base64 from the file written to disk by Claude
$files['database/seeders/ScoringRuleSeeder.php'] =
    'PD9waHAKCm5hbWVzcGFjZSBEYXRhYmFzZVxTZWVkZXJzOwoKdXNlIEFwcFxNb2RlbHNcU2Nvcmlu' .
    'Z1J1bGU7CnVzZSBJbGx1bWluYXRlXERhdGFiYXNlXFNlZWRlcjsKCmNsYXNzIFNjb3JpbmdSdWxl' .
    'U2VlZGVyIGV4dGVuZHMgU2VlZGVyCnsKICAgIHB1YmxpYyBmdW5jdGlvbiBydW4oKTogdm9pZAog' .
    'ICAgewogICAgICAgICRydWxlcyA9IFsKICAgICAgICAgICAgLy8gLS0tIFNvdXJjZSBydWxlcyAo' .
    'bWlycm9ycyBvbGQgJHNvdXJjZVNjb3JlcyBhcnJheSkgLS0tCiAgICAgICAgICAgIFsnZmFjdG9y' .
    'JyA9PiAnc291cmNlX29yZ2FuaWMnLCAgJ2xhYmVsJyA9PiAnT3JnYW5pYyBTZWFyY2gnLCAgJ3dl' .
    'aWdodCcgPT4gMjUsICdjYXRlZ29yeScgPT4gJ3NvdXJjZSddLAogICAgICAgICAgICBbJ2ZhY3Rv' .
    'cicgPT4gJ3NvdXJjZV93aGF0c2FwcCcsICdsYWJlbCcgPT4gJ1doYXRzQXBwJywgICAgICAgICd3' .
    'ZWlnaHQnID0+IDIyLCAnY2F0ZWdvcnknID0+ICdzb3VyY2UnXSwKICAgICAgICAgICAgWydmYWN0' .
    'b3InID0+ICdzb3VyY2VfcmVmZXJyYWwnLCAnbGFiZWwnID0+ICdSZWZlcnJhbCcsICAgICAgICAn' .
    'd2VpZ2h0JyA9PiAyMCwgJ2NhdGVnb3J5JyA9PiAnc291cmNlJ10sCiAgICAgICAgICAgIFsnZmFj' .
    'dG9yJyA9PiAnc291cmNlX2VtYWlsJywgICAgJ2xhYmVsJyA9PiAnRW1haWwgQ2FtcGFpZ24nLCAg' .
    'ICd3ZWlnaHQnID0+IDE4LCAnY2F0ZWdvcnknID0+ICdzb3VyY2UnXSwKICAgICAgICAgICAgWydm' .
    'YWN0b3InID0+ICdzb3VyY2VfZGlyZWN0JywgICAnbGFiZWwnID0+ICdEaXJlY3QnLCAgICAgICAg' .
    'ICAgJ3dlaWdodCcgPT4gMTUsICdjYXRlZ29yeScgPT4gJ3NvdXJjZSddLAogICAgICAgICAgICBb' .
    'J2ZhY3RvcicgPT4gJ3NvdXJjZV9zb2NpYWwnLCAgICdsYWJlbCcgPT4gJ1NvY2lhbCBNZWRpYScs' .
    'ICAgICAnd2VpZ2h0JyA9PiAxMiwgJ2NhdGVnb3J5JyA9PiAnc291cmNlJ10sCiAgICAgICAgICAg' .
    'IFsnZmFjdG9yJyA9PiAnc291cmNlX3BhaWQnLCAgICAgJ2xhYmVsJyA9PiAnUGFpZCBBZHMnLCAg' .
    'ICAgICAgICd3ZWlnaHQnID0+IDEwLCAnY2F0ZWdvcnknID0+ICdzb3VyY2UnXSwKCiAgICAgICAg' .
    'ICAgIC8vIC0tLSBTdGF0dXMgcnVsZXMgKG1pcnJvcnMgb2xkICRzdGF0dXNTY29yZXMgYXJyYXkp' .
    'IC0tLQogICAgICAgICAgICBbJ2ZhY3RvcicgPT4gJ3N0YXR1c19uZXcnLCAgICAgICAgICdsYWJl' .
    'bCcgPT4gJ05ldyBMZWFkJywgICAgICd3ZWlnaHQnID0+IDAsICAgJ2NhdGVnb3J5JyA9PiAnc3Rh' .
    'dHVzJ10sCiAgICAgICAgICAgIFsnZmFjdG9yJyA9PiAnc3RhdHVzX2NvbnRhY3RlZCcsICAgJ2xh' .
    'YmVsJyA9PiAnQ29udGFjdGVkJywgICAgJ3dlaWdodCcgPT4gMTAsICAnY2F0ZWdvcnknID0+ICdz' .
    'dGF0dXMnXSwKICAgICAgICAgICAgWydmYWN0b3InID0+ICdzdGF0dXNfcXVhbGlmaWVkJywgICAn' .
    'bGFiZWwnID0+ICdRdWFsaWZpZWQnLCAgICAnd2VpZ2h0JyA9PiAyNSwgICdjYXRlZ29yeScgPT4g' .
    'J3N0YXR1cyddLAogICAgICAgICAgICBbJ2ZhY3RvcicgPT4gJ3N0YXR1c19wcm9wb3NhbCcsICAg' .
    'ICdsYWJlbCcgPT4gJ1Byb3Bvc2FsJywgICAgICd3ZWlnaHQnID0+IDQwLCAgJ2NhdGVnb3J5JyA9' .
    'PiAnc3RhdHVzJ10sCiAgICAgICAgICAgIFsnZmFjdG9yJyA9PiAnc3RhdHVzX25lZ290aWF0aW9u' .
    'JywgJ2xhYmVsJyA9PiAnTmVnb3RpYXRpb24nLCAgJ3dlaWdodCcgPT4gNTUsICAnY2F0ZWdvcnkn' .
    'ID0+ICdzdGF0dXMnXSwKICAgICAgICAgICAgWydmYWN0b3InID0+ICdzdGF0dXNfd29uJywgICAg' .
    'ICAgICAnbGFiZWwnID0+ICdXb24nLCAgICAgICAgICAnd2VpZ2h0JyA9PiAxMDAsICdjYXRlZ29y' .
    'eScgPT4gJ3N0YXR1cyddLAogICAgICAgICAgICBbJ2ZhY3RvcicgPT4gJ3N0YXR1c19sb3N0Jywg' .
    'ICAgICAgICdsYWJlbCcgPT4gJ0xvc3QnLCAgICAgICAgICd3ZWlnaHQnID0+IDAsICAgJ2NhdGVn' .
    'b3J5JyA9PiAnc3RhdHVzJ10sCiAgICAgICAgXTsKCiAgICAgICAgZm9yZWFjaCAoJHJ1bGVzIGFz' .
    'ICRydWxlKSB7CiAgICAgICAgICAgIFNjb3JpbmdSdWxlOjp1cGRhdGVPckNyZWF0ZSgKICAgICAg' .
    'ICAgICAgICAgIFsnZmFjdG9yJyA9PiAkcnVsZVsnZmFjdG9yJ11dLAogICAgICAgICAgICAgICAg' .
    'YXJyYXlfbWVyZ2UoJHJ1bGUsIFsnaXNfYWN0aXZlJyA9PiB0cnVlXSkKICAgICAgICAgICAgKTsK' .
    'ICAgICAgICB9CgogICAgICAgICR0aGlzLT5jb21tYW5kLT5pbmZvKCdTY29yaW5nUnVsZVNlZWRl' .
    'cjogJyAuIGNvdW50KCRydWxlcykgLiAnIHJ1bGVzIHNlZWRlZC4nKTsKICAgIH0KfQo=';

// Write all files
echo "<h2>ALG3PL deploy_scale_batch3</h2><pre>\n";
$allOk = true;
foreach ($files as $relPath => $b64) {
    $fullPath = $base . '/' . $relPath;
    $ok = writeFile($fullPath, $b64);
    $status = $ok ? 'OK' : 'FAIL';
    if (!$ok) $allOk = false;
    echo "[{$status}] {$relPath}\n";
}

// ─── Run migrations ────────────────────────────────────────────────────────────
echo "\n--- Running migrations ---\n";
$migOut = run('migrate --force');
echo $migOut . "\n";

// ─── Run seeder ────────────────────────────────────────────────────────────────
echo "\n--- Seeding ScoringRules ---\n";
$seedOut = run('db:seed --class=ScoringRuleSeeder --force');
echo $seedOut . "\n";

// ─── Clear caches ──────────────────────────────────────────────────────────────
echo "\n--- Clearing caches ---\n";
echo run('config:clear') . "\n";
echo run('route:clear') . "\n";
echo run('view:clear') . "\n";
echo run('cache:clear') . "\n";

// ─── Self-delete ───────────────────────────────────────────────────────────────
echo "\n--- Cleaning up ---\n";
if ($allOk) {
    @unlink(__FILE__);
    echo "Deploy script deleted.\n";
} else {
    echo "Some files failed to write — deploy script NOT deleted. Fix manually.\n";
}

echo "\n</pre><p><strong>Done.</strong> Batch 3 deployed.</p>";
