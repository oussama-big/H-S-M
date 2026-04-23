<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('doctors', 'cabinet_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->foreignId('cabinet_id')
                    ->nullable()
                    ->after('license_number')
                    ->constrained('cabinets')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('doctors', 'cabinet_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropForeign(['cabinet_id']);
                $table->dropColumn('cabinet_id');
            });
        }
    }
};
