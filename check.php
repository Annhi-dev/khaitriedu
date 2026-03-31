<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "Rooms: " . json_encode(Illuminate\Support\Facades\Schema::getColumnListing('rooms')) . "\n";
echo "Khoa Hoc: " . json_encode(Illuminate\Support\Facades\Schema::getColumnListing('khoa_hoc')) . "\n";
