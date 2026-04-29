<?php

use Database\Seeders\DuLieuDanhMucVaKhoaHocSeeder;
use Database\Seeders\DuLieuNguoiDungVaPhongBanSeeder;
use Database\Seeders\DuLieuVanHanhDaoTaoSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nguoi_dung') || ! Schema::hasTable('danh_muc') || ! Schema::hasTable('khoa_hoc')) {
            return;
        }

        app(DuLieuNguoiDungVaPhongBanSeeder::class)->run();
        app(DuLieuDanhMucVaKhoaHocSeeder::class)->run();
        app(DuLieuVanHanhDaoTaoSeeder::class)->run();
    }

    public function down(): void
    {
    }
};
