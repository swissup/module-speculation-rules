<?php
$root = __DIR__;
while (!file_exists($root . '/vendor/autoload.php')) {
    $parent = dirname($root);
    if ($parent === $root) {
        throw new RuntimeException('Unable to locate vendor/autoload.php above ' . __DIR__);
    }
    $root = $parent;
}

require_once $root . '/vendor/autoload.php';

// Load the module under test from this checkout, not from vendor/
spl_autoload_register(function (string $class): void {
    $map = [
        'Swissup\\SpeculationRules\\Test\\Unit\\' => __DIR__ . '/../Test/Unit/',
        'Swissup\\SpeculationRules\\'             => __DIR__ . '/../',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require $file;
            }
            return;
        }
    }
}, true, true);
