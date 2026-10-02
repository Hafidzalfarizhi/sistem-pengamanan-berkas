<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
             // Mengubah nama kolom status menjadi type
            DB::statement("ALTER TABLE `notifications` CHANGE `status` `type` ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info';");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Kembalikan nama kolom type menjadi status jika di-rollback
            DB::statement("ALTER TABLE `notifications` CHANGE `type` `status` ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info';");
        });
    }
};
