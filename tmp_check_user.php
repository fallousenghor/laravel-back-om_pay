<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = new App\Models\Utilisateur();
echo get_class($u) . " extends " . get_parent_class($u) . "\n";
