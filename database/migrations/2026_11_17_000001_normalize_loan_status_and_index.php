<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satukan lifecycle status: hilangkan status warisan "terlambat"
     * dan percepat filter berdasarkan status.
     */
    public function up(): void
    {
        DB::table('loans')
            ->where('status', 'terlambat')
            ->update(['status' => 'dipinjam']);

        Schema::table('loans', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
