<?php

/**
 * Script hỗ trợ chạy lệnh migration trực tiếp từ trình duyệt 
 * dành cho môi trường XAMPP / Local.
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Đang chạy Migration...</h1>";

try {
    $kernel->call('migrate', ['--force' => true]);
    $output = $kernel->output();
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    echo "<h2 style='color: green;'>Chạy Migration thành công!</h2>";
    echo "<a href='./admin/classes' style='padding: 10px 20px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 5px; font-family: sans-serif;'>Quay lại trang Lớp học</a>";
} catch (\Exception $e) {
    echo "<h2 style='color: red;'>Lỗi: " . $e->getMessage() . "</h2>";
}
