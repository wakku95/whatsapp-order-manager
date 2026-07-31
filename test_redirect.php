<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$req = Illuminate\Http\Request::create('/', 'GET');
$res = $kernel->handle($req);
echo "Guest / -> Status: " . $res->getStatusCode() . " Location: " . $res->headers->get('Location') . "\n";
