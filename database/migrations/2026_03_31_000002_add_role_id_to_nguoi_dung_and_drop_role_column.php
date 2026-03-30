<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add role_id column (nullable initially for migration)
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('id');
        });

        // Step 2: Migrate existing role strings to role_id
        $roleMap = [
            'admin' => 1,
            'giang_vien' => 2,
            'hoc_vien' => 3,
        ];

        foreach ($roleMap as $roleName => $roleId) {
            DB::table('nguoi_dung')
                ->where('role', $roleName)
                ->update(['role_id' => $roleId]);
        }

        // Assign any unmapped users to student (role_id 3)
        DB::table('nguoi_dung')
            ->whereNull('role_id')
            ->update(['role_id' => 3]);

        // Step 3: Make role_id non-nullable and add FK constraint
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->default(3)->change();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
        });

        // Step 4: Drop old role column
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->string('role')->default('hoc_vien')->after('id');
        });

        // Restore role strings from role_id
        $roleMap = [
            1 => 'admin',
            2 => 'giang_vien',
            3 => 'hoc_vien',
        ];

        foreach ($roleMap as $roleId => $roleName) {
            DB::table('nguoi_dung')
                ->where('role_id', $roleId)
                ->update(['role' => $roleName]);
        }

        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
