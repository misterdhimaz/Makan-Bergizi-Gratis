<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_menus', function (Blueprint $table) {
            $table->dropUnique('daily_menus_operational_date_unique');
            $table->index('operational_date');
        });
    }

    public function down(): void
    {
        Schema::table('daily_menus', function (Blueprint $table) {
            $table->dropIndex(['operational_date']);
            $table->unique('operational_date');
        });
    }
};
