<?php
/*
|--------------------------------------------------------------------------
| NexCore System - Asset Publisher
|--------------------------------------------------------------------------
|
| This script publishes the NexcoreSystem module's assets to the public
| directory. Use this on shared hosting where artisan CLI is not available.
|
| Usage: Navigate to /nx_publish_assets.php in the browser
|        (Place this file in the Laravel public_html root)
|
| What it does:
|   Copies: Modules/NexcoreSystem/Resources/assets/*
|   To:     public/nexcore/*
|
| NexCore Africa Proprietary Limited
|
*/

$moduleAssets = __DIR__ . '/application/Modules/NexcoreSystem/Resources/assets';
$publicTarget = __DIR__ . '/public/nexcore';

if (!is_dir($moduleAssets)) {
    echo "<h2 style='color:red;'>ERROR: Module assets directory not found</h2>";
    echo "<p>Expected: $moduleAssets</p>";
    exit;
}

$copied = 0;
$errors = 0;

function copyDirectory($source, $destination, &$copied, &$errors) {
    if (!is_dir($destination)) {
        if (!mkdir($destination, 0755, true)) {
            $errors++;
            echo "<div style='color:red;'>FAILED to create: $destination</div>";
            return;
        }
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $source . '/' . $file;
        $dstPath = $destination . '/' . $file;

        if (is_dir($srcPath)) {
            copyDirectory($srcPath, $dstPath, $copied, $errors);
        } else {
            if (copy($srcPath, $dstPath)) {
                $copied++;
                echo "<div style='color:#059669;'>OK: $dstPath</div>";
            } else {
                $errors++;
                echo "<div style='color:red;'>FAILED: $dstPath</div>";
            }
        }
    }
    closedir($dir);
}

echo "<!DOCTYPE html><html><head><title>NexCore Asset Publisher</title></head><body style='font-family:monospace; background:#0a0e1a; color:#fff; padding:40px;'>";
echo "<h1 style='color:#06b6d4;'>NexCore System - Asset Publisher</h1>";
echo "<p>Source: $moduleAssets</p>";
echo "<p>Target: $publicTarget</p>";
echo "<hr style='border-color:#1e293b;'>";

copyDirectory($moduleAssets, $publicTarget, $copied, $errors);

echo "<hr style='border-color:#1e293b;'>";
echo "<h2 style='color:" . ($errors > 0 ? "#ef4444" : "#059669") . ";'>Done: $copied files copied, $errors errors</h2>";
echo "<p><a href='/nexcore/system/system_master_page' style='color:#06b6d4;'>Go to System Master Page</a></p>";
echo "</body></html>";
