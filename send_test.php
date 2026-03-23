<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
Illuminate\Support\Facades\Mail::raw('Test message', function($msg){ $msg->to('voan5646@gmail.com')->subject('SMTP test'); });
echo 'done';
