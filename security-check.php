<?php
/**
 * ============================================
 * BYPASSERV3 SECURITY CHECK SCRIPT
 * ============================================
 */

require_once 'config.php';
require_once 'functions.php';

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "🔒 BYPASSERV3 SECURITY SCAN\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. Malicious files
echo "1️⃣  Scanning for suspicious files...\n";
$suspicious = securityScan(false);

if (!empty($suspicious)) {
    echo "⚠️  ALERT: " . count($suspicious) . " suspicious file(s) detected:\n\n";
    foreach ($suspicious as $item) {
        echo "   📄 " . $item['path'] . "\n";
        echo "   📝 " . $item['reason'] . "\n";
        echo "   🚨 " . strtoupper($item['severity']) . "\n\n";
    }
    
    echo "   Delete these files? [y/N]: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim(strtolower($line)) === 'y') {
        securityScan(true);
        echo "   ✅ Files removed\n";
    }
    fclose($handle);
} else {
    echo "   ✅ No suspicious files\n";
}

echo "\n";

// 2. Permissions
echo "2️⃣  Checking file permissions...\n";

$files = [
    'config.php' => 0644,
    'functions.php' => 0644,
    'api/bypass.php' => 0644
];

$issues = [];
foreach ($files as $file => $expected) {
    if (file_exists($file)) {
        $current = fileperms($file) & 0777;
        if ($current != $expected) {
            $issues[$file] = decoct($current);
        }
    }
}

if (empty($issues)) {
    echo "   ✅ Permissions correct\n";
} else {
    echo "   ⚠️  Issues found:\n";
    foreach ($issues as $file => $perm) {
        echo "   $file: $perm\n";
    }
}

echo "\n";

// 3. .htaccess
echo "3️⃣  Checking .htaccess files...\n";

$htaccess = [
    '.htaccess',
    'data/.htaccess',
    'data/tokens/.htaccess'
];

$missing = [];
foreach ($htaccess as $file) {
    if (!file_exists($file)) {
        $missing[] = $file;
    }
}

if (empty($missing)) {
    echo "   ✅ All present\n";
} else {
    echo "   ⚠️  Missing: " . implode(', ', $missing) . "\n";
}

echo "\n";

// 4. Security log
echo "4️⃣  Checking security log...\n";

$logFile = dirname(DATA_PATH) . '/security.log';

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES);
    echo "   📊 " . count($lines) . " events\n";
    
    $recent = array_slice($lines, -5);
    echo "   Recent:\n";
    foreach ($recent as $line) {
        $data = json_decode($line, true);
        if ($data) {
            echo "      [{$data['timestamp']}] {$data['event']}\n";
        }
    }
} else {
    echo "   ℹ️  No log found\n";
}

echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════\n";
$totalIssues = count($suspicious) + count($issues) + count($missing);

if ($totalIssues === 0) {
    echo "✅ ALL CLEAR - No issues detected\n";
} else {
    echo "⚠️  $totalIssues issue(s) detected\n";
}

echo "═══════════════════════════════════════════════════════\n\n";
?>
