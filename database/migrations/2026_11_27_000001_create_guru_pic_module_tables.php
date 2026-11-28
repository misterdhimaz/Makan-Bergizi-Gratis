<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_routes', 'scheduled_arrival_time')) {
                $table->string('scheduled_arrival_time', 10)->default('08:00')->after('operational_date');
            }
            if (! Schema::hasColumn('delivery_routes', 'prepared_at')) {
                $table->timestamp('prepared_at')->nullable()->after('confirmed_by');
            }
            if (! Schema::hasColumn('delivery_routes', 'courier_name')) {
                $table->string('courier_name', 120)->nullable()->after('courier_id');
            }
            if (! Schema::hasColumn('delivery_routes', 'vehicle_plate')) {
                $table->string('vehicle_plate', 40)->nullable()->after('courier_name');
            }
            if (! Schema::hasColumn('delivery_routes', 'packaging_condition')) {
                $table->string('packaging_condition', 60)->nullable()->after('notes');
            }
            if (! Schema::hasColumn('delivery_routes', 'organoleptic_taste')) {
                $table->string('organoleptic_taste', 40)->nullable()->after('packaging_condition');
            }
            if (! Schema::hasColumn('delivery_routes', 'organoleptic_aroma')) {
                $table->string('organoleptic_aroma', 40)->nullable()->after('organoleptic_taste');
            }
            if (! Schema::hasColumn('delivery_routes', 'organoleptic_texture')) {
                $table->string('organoleptic_texture', 40)->nullable()->after('organoleptic_aroma');
            }
            if (! Schema::hasColumn('delivery_routes', 'organoleptic_color')) {
                $table->string('organoleptic_color', 40)->nullable()->after('organoleptic_texture');
            }
            if (! Schema::hasColumn('delivery_routes', 'organoleptic_status')) {
                $table->string('organoleptic_status', 40)->nullable()->after('organoleptic_color');
            }
        });

        Schema::table('complaint_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('complaint_tickets', 'priority_notified_at')) {
                $table->timestamp('priority_notified_at')->nullable()->after('reported_at');
            }
            if (! Schema::hasColumn('complaint_tickets', 'priority_recipients')) {
                $table->string('priority_recipients', 255)->nullable()->after('priority_notified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaint_tickets', function (Blueprint $table) {
            $cols = array_filter(['priority_notified_at', 'priority_recipients'], fn (string $c) => Schema::hasColumn('complaint_tickets', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('delivery_routes', function (Blueprint $table) {
            $cols = array_filter([
                'scheduled_arrival_time',
                'prepared_at',
                'courier_name',
                'vehicle_plate',
                'packaging_condition',
                'organoleptic_taste',
                'organoleptic_aroma',
                'organoleptic_texture',
                'organoleptic_color',
                'organoleptic_status',
            ], fn (string $c) => Schema::hasColumn('delivery_routes', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
