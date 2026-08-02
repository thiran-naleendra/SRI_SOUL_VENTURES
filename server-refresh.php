<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

const REPAIR_KEY = 'srisoul-2026-route-fix-8Qv4';

if (! hash_equals(REPAIR_KEY, (string) ($_GET['key'] ?? ''))) {
    http_response_code(404);
    exit('Not Found');
}

$publicRoot = __DIR__;
$root = dirname(__DIR__);
$deleted = [];
$failed = [];

$cacheFiles = array_merge(
    glob($root.'/bootstrap/cache/routes-*.php') ?: [],
    glob($root.'/bootstrap/cache/config.php') ?: [],
    glob($root.'/storage/framework/views/*.php') ?: [],
);

foreach ($cacheFiles as $file) {
    if (@unlink($file)) {
        $deleted[] = str_replace($root.'/', '', $file);
    } else {
        $failed[] = str_replace($root.'/', '', $file);
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

$routes = @file_get_contents($root.'/routes/web.php') ?: '';
$editView = @file_get_contents($root.'/resources/views/admin/packages/edit.blade.php') ?: '';
$controller = @file_get_contents($root.'/app/Http/Controllers/Admin/PackageController.php') ?: '';
$storageTarget = $root.'/storage/app/public';
$storageLink = $publicRoot.'/storage';
$storageStatus = 'already exists';

if (! file_exists($storageLink) && ! is_link($storageLink)) {
    $storageStatus = @symlink($storageTarget, $storageLink) ? 'created' : 'could not be created';
}

echo "Sri Soul Ventures server refresh\n\n";
echo 'Project root: '.$root."\n";
echo 'Public root: '.$publicRoot."\n";
echo 'Package save route installed: '.(str_contains($routes, "packages/{package}/save") ? 'YES' : 'NO')."\n";
echo 'Package save form installed: '.(str_contains($editView, "admin.packages.save") ? 'YES' : 'NO')."\n";
echo 'Package controller installed: '.(str_contains($controller, 'public function show') ? 'YES' : 'NO')."\n";
echo 'Route cache present after refresh: '.((glob($root.'/bootstrap/cache/routes-*.php') ?: []) === [] ? 'NO' : 'YES')."\n";
echo 'Config cache present after refresh: '.(is_file($root.'/bootstrap/cache/config.php') ? 'YES' : 'NO')."\n";
echo 'Deleted cache files: '.count($deleted)."\n";
echo 'Public storage link: '.$storageStatus."\n";
echo 'Storage target exists: '.(is_dir($storageTarget) ? 'YES' : 'NO')."\n";

if ($failed !== []) {
    echo "\nCould not delete these files (check permissions):\n- ".implode("\n- ", $failed)."\n";
}

echo "\nIMPORTANT: Delete server-refresh.php from public_html now.\n";
