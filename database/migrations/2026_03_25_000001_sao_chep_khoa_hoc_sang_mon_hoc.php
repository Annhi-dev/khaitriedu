<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        DB::statement("
            INSERT INTO mon_hoc (name, description, price, created_at, updated_at)
            SELECT 
                kh.title as name,
                kh.description,
                0 as price,
                kh.created_at,
                kh.updated_at
            FROM khoa_hoc kh
            WHERE kh.id NOT IN (SELECT id FROM mon_hoc)
        ");
    }

    
    public function down(): void
    {
    }
};
