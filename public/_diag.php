<?php
/**
 * One-shot diagnostic. Self-destructs after run.
 * Visit: https://marketing.alg3pl.com/_diag.php
 */
error_reporting(E_ALL); ini_set('display_errors', 1); ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

$ROOT = dirname(__DIR__);

echo "========================================\n";
echo "  ALG3PL DIAGNOSTIC — " . date('Y-m-d H:i:s T') . "\n";
echo "========================================\n\n";

// 1) OPcache state + reset
echo "[1] OPCACHE\n";
if (function_exists('opcache_get_status')) {
    $s = @opcache_get_status(false);
    if ($s && !empty($s['opcache_enabled'])) {
        echo "    enabled=yes cached=" . ($s['opcache_statistics']['num_cached_scripts'] ?? '?') . "\n";
    } else {
        echo "    enabled=no\n";
    }
} else {
    echo "    no extension\n";
}
if (function_exists('opcache_reset')) { @opcache_reset(); echo "    opcache_reset() called\n"; }

// Force invalidate key files
$targets = [
    'app/Filament/Resources/AdMetricResource.php',
    'app/Filament/Resources/CountryConfigResource.php',
    'app/Filament/Resources/ScoringRuleResource.php',
    'app/Filament/Widgets/AdMetricsWidget.php',
    'app/Providers/Filament/AdminPanelProvider.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/config.php',
];
foreach ($targets as $t) {
    $f = $ROOT . '/' . $t;
    if (function_exists('opcache_invalidate')) { @opcache_invalidate($f, true); }
}
echo "    invalidated " . count($targets) . " targets\n\n";

// 2) Git state
echo "[2] GIT\n";
$head = trim(@file_get_contents($ROOT . '/.git/HEAD') ?: '');
echo "    HEAD: $head\n";
if (preg_match('/ref:\s*(.+)/', $head, $m)) {
    $ref = trim(@file_get_contents($ROOT . '/.git/' . $m[1]) ?: '');
    echo "    commit: " . substr($ref, 0, 12) . "\n";
}
$logFile = $ROOT . '/.git/logs/HEAD';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last = end($lines);
    echo "    last-op: " . trim($last) . "\n";
}
echo "\n";

// 3) Check key files on disk
echo "[3] FILE CONTENTS (line 21 of each)\n";
foreach (['app/Filament/Resources/AdMetricResource.php',
          'app/Filament/Resources/CountryConfigResource.php',
          'app/Filament/Resources/ScoringRuleResource.php'] as $p) {
    $f = $ROOT . '/' . $p;
    if (!file_exists($f)) { echo "    MISSING: $p\n"; continue; }
    $lines = file($f);
    echo "    $p\n";
    echo "      mtime=" . date('H:i:s', filemtime($f)) . "\n";
    echo "      L21: " . trim($lines[20] ?? '') . "\n";
}
echo "\n";

// 4) Try to bootstrap Laravel and instantiate classes via Reflection
echo "[4] LARAVEL BOOTSTRAP\n";
try {
    require_once $ROOT . '/vendor/autoload.php';
    echo "    autoload ok\n";
    $app = require_once $ROOT . '/bootstrap/app.php';
    echo "    app bootstrap ok\n";
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "    kernel ok\n";
} catch (\Throwable $e) {
    echo "    BOOTSTRAP FAILED: " . get_class($e) . "\n";
    echo "      " . $e->getMessage() . "\n";
    echo "      at " . $e->getFile() . ':' . $e->getLine() . "\n";
}

// 5) Try each Resource via Reflection — the real type mismatch test
echo "\n[5] REFLECTION OF RESOURCES\n";
$resources = [
    'App\\Filament\\Resources\\AdMetricResource',
    'App\\Filament\\Resources\\CountryConfigResource',
    'App\\Filament\\Resources\\ScoringRuleResource',
    'App\\Filament\\Resources\\LeadResource',
    'App\\Filament\\Resources\\CampaignResource',
    'App\\Filament\\Resources\\ClientResource',
    'App\\Filament\\Resources\\CountryReportResource',
    'App\\Filament\\Resources\\EmailTemplateResource',
    'App\\Filament\\Resources\\FunnelResource',
    'App\\Filament\\Resources\\SegmentResource',
];
foreach ($resources as $cls) {
    try {
        $r = new ReflectionClass($cls);
        $out = [];
        foreach (['navigationGroup', 'navigationIcon', 'model'] as $prop) {
            if ($r->hasProperty($prop)) {
                $p = $r->getProperty($prop);
                $t = $p->getType();
                $out[] = "$prop=" . ($t ? (string)$t : 'untyped');
            }
        }
        echo "    OK: " . substr($cls, 24) . " [" . implode(', ', $out) . "]\n";
    } catch (\Throwable $e) {
        echo "    FAIL: $cls\n";
        echo "      " . $e->getMessage() . "\n";
    }
}

// 6) Same for Widgets
echo "\n[6] REFLECTION OF WIDGETS\n";
$widgets = [
    'App\\Filament\\Widgets\\AdMetricsWidget',
    'App\\Filament\\Widgets\\SmartAlertsWidget',
    'App\\Filament\\Widgets\\LeadHeroWidget',
    'App\\Filament\\Widgets\\RegionalMapWidget',
    'App\\Filament\\Widgets\\ContactTimelineWidget',
    'App\\Filament\\Widgets\\TrafficOverviewWidget',
    'App\\Filament\\Widgets\\TrafficTrendWidget',
    'App\\Filament\\Widgets\\LeadsByCountryWidget',
    'App\\Filament\\Widgets\\TopKeywordsWidget',
    'App\\Filament\\Widgets\\SalesPipelineWidget',
    'App\\Filament\\Widgets\\TaskProgressWidget',
];
foreach ($widgets as $cls) {
    try {
        $r = new ReflectionClass($cls);
        $out = [];
        foreach (['view', 'sort', 'columnSpan'] as $prop) {
            if ($r->hasProperty($prop)) {
                $p = $r->getProperty($prop);
                $t = $p->getType();
                $static = $p->isStatic() ? 'static' : '';
                $out[] = "$prop=" . ($t ? (string)$t : 'untyped') . "$static";
            }
        }
        echo "    OK: " . substr($cls, 22) . " [" . implode(', ', $out) . "]\n";
    } catch (\Throwable $e) {
        echo "    FAIL: $cls\n";
        echo "      " . $e->getMessage() . "\n";
    }
}

// 7) Fresh log tail
echo "\n[7] LOG TAIL (last 6KB)\n";
$log = $ROOT . '/storage/logs/laravel.log';
if (file_exists($log)) {
    $size = filesize($log);
    $fh = fopen($log, 'r');
    fseek($fh, -min($size, 6000), SEEK_END);
    $tail = fread($fh, 6000);
    fclose($fh);
    // Show only lines with ERROR or recent timestamp
    echo "    size=" . number_format($size) . " bytes, mtime=" . date('H:i:s', filemtime($log)) . "\n";
    echo "    ---\n";
    foreach (explode("\n", $tail) as $line) {
        if (strlen($line) > 500) $line = substr($line, 0, 500) . '...';
        echo "    $line\n";
    }
} else {
    echo "    no log file\n";
}

echo "\n\n=== END ===\n";
@unlink(__FILE__);
