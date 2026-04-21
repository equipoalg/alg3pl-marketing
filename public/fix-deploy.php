<?php
/**
 * Emergency fix: TrafficTrendWidget $maxHeight static conflict
 * Upload to public/, visit in browser, then delete.
 */

$basePath = dirname(__DIR__);
$results = [];

// 1. Fix TrafficTrendWidget.php — ensure $maxHeight is NOT static
$widgetPath = $basePath . '/app/Filament/Widgets/TrafficTrendWidget.php';
if (file_exists($widgetPath)) {
    $content = file_get_contents($widgetPath);
    $original = $content;
    // Fix: remove "static" from maxHeight declaration
    $content = preg_replace(
        '/protected\s+static\s+\?\s*string\s+\$maxHeight/',
        'protected ?string $maxHeight',
        $content
    );
    if ($content !== $original) {
        file_put_contents($widgetPath, $content);
        $results[] = '✓ Fixed TrafficTrendWidget.php (removed static from $maxHeight)';
    } else {
        $results[] = '○ TrafficTrendWidget.php already correct';
    }
} else {
    $results[] = '✗ TrafficTrendWidget.php not found at: ' . $widgetPath;
}

// 2. Fix LeadsByCountryWidget.php — same issue possible
$lbcPath = $basePath . '/app/Filament/Widgets/LeadsByCountryWidget.php';
if (file_exists($lbcPath)) {
    $content = file_get_contents($lbcPath);
    $original = $content;
    $content = preg_replace(
        '/protected\s+static\s+\?\s*string\s+\$maxHeight/',
        'protected ?string $maxHeight',
        $content
    );
    if ($content !== $original) {
        file_put_contents($lbcPath, $content);
        $results[] = '✓ Fixed LeadsByCountryWidget.php';
    } else {
        $results[] = '○ LeadsByCountryWidget.php already correct';
    }
}

// 3. Fix SalesPipelineWidget.php — same issue possible
$spPath = $basePath . '/app/Filament/Widgets/SalesPipelineWidget.php';
if (file_exists($spPath)) {
    $content = file_get_contents($spPath);
    $original = $content;
    $content = preg_replace(
        '/protected\s+static\s+\?\s*string\s+\$maxHeight/',
        'protected ?string $maxHeight',
        $content
    );
    if ($content !== $original) {
        file_put_contents($spPath, $content);
        $results[] = '✓ Fixed SalesPipelineWidget.php';
    } else {
        $results[] = '○ SalesPipelineWidget.php already correct';
    }
}

// 4. Disable APP_DEBUG
$envPath = $basePath . '/.env';
if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    $original = $env;
    $env = preg_replace('/^APP_DEBUG\s*=\s*true$/mi', 'APP_DEBUG=false', $env);
    if ($env !== $original) {
        file_put_contents($envPath, $env);
        $results[] = '✓ Set APP_DEBUG=false';
    } else {
        $results[] = '○ APP_DEBUG already false';
    }
}

// 5. Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results[] = '✓ OPcache cleared';
} else {
    $results[] = '○ OPcache not available';
}

// 6. Clear Laravel caches
$cachePaths = [
    $basePath . '/bootstrap/cache/config.php',
    $basePath . '/bootstrap/cache/routes-v7.php',
    $basePath . '/bootstrap/cache/filament',
];
foreach ($cachePaths as $cp) {
    if (is_file($cp)) {
        unlink($cp);
        $results[] = '✓ Deleted ' . basename($cp);
    } elseif (is_dir($cp)) {
        array_map('unlink', glob("$cp/*"));
        $results[] = '✓ Cleared ' . basename($cp) . '/';
    }
}

// Clear view cache
$viewCache = $basePath . '/storage/framework/views';
if (is_dir($viewCache)) {
    $files = glob("$viewCache/*.php");
    array_map('unlink', $files);
    $results[] = '✓ Cleared ' . count($files) . ' view cache files';
}

// 7. Self-delete
$selfDeleted = false;
if (unlink(__FILE__)) {
    $selfDeleted = true;
}

// Output results
header('Content-Type: text/plain; charset=utf-8');
echo "=== ALG3PL Fix Deploy ===\n\n";
foreach ($results as $r) {
    echo "$r\n";
}
echo "\n" . ($selfDeleted ? '✓ This script has been deleted.' : '⚠ Delete this script manually!');
echo "\n\nDone. Reload /admin/leads to verify.\n";
