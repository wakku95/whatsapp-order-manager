<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $req = Illuminate\Http\Request::create('/dashboard', 'GET');
    $res = $kernel->handle($req);
    echo "Status: " . $res->getStatusCode() . " Location: " . $res->headers->get('Location') . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
