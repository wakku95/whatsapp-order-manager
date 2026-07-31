<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test 1: Guest visits /
$req1 = Illuminate\Http\Request::create('/', 'GET');
$res1 = $kernel->handle($req1);
echo "1. Guest / -> " . $res1->getStatusCode() . " " . $res1->headers->get('Location') . "\n";

// Test 2: Authenticated user (no business) visits /
// We need to mock auth for this.
