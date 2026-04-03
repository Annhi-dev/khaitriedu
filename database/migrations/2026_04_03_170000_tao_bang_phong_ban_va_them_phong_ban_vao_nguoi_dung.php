<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('phong_ban')) {
            Schema::create('phong_ban', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 150)->unique();
                $table->text('description')->nullable();
                $table->string('status', 30)->default('active');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('phong_ban') && DB::table('phong_ban')->count() === 0) {
            $now = now();

            DB::table('phong_ban')->insert([
                [
                    'code' => 'DT',
                    'name' => 'Phòng Đào tạo',
                    'description' => 'Phụ trách chuyên môn đào tạo và phân công giảng dạy.',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'KTCL',
                    'name' => 'Phòng Khảo thí - Chất lượng',
                    'description' => 'Theo dõi chất lượng giảng dạy, đánh giá và kiểm định nội bộ.',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'CNTT',
                    'name' => 'Phòng Công nghệ giáo dục',
                    'description' => 'Vận hành nền tảng, tài nguyên số và hỗ trợ kỹ thuật học tập.',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        if (Schema::hasTable('nguoi_dung') && ! Schema::hasColumn('nguoi_dung', 'department_id')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('role_id')
                    ->constrained('phong_ban')
                    ->nullOnDelete();
            });
        }

        $defaultDepartmentId = DB::table('phong_ban')->orderBy('id')->value('id');
        $teacherRoleId = Schema::hasTable('roles')
            ? DB::table('roles')->where('name', 'teacher')->value('id')
            : null;

        if ($defaultDepartmentId && $teacherRoleId && Schema::hasColumn('nguoi_dung', 'department_id')) {
            DB::table('nguoi_dung')
                ->where('role_id', $teacherRoleId)
                ->whereNull('department_id')
                ->update(['department_id' => $defaultDepartmentId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nguoi_dung') && Schema::hasColumn('nguoi_dung', 'department_id')) {
            Schema::table('nguoi_dung', function (Blueprint $table) {
                $table->dropConstrainedForeignId('department_id');
            });
        }

        Schema::dropIfExists('phong_ban');
    }
};
