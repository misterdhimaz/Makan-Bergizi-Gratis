<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apd_inspections', function (Blueprint $table) {
            $table->id();
            $table->date('inspection_date')->index();
            $table->string('shift', 50)->default('Pagi (05:00 - 11:00)');
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('production_run_id')->nullable()->constrained('production_runs')->nullOnDelete();
            $table->unsignedSmallInteger('total_volunteers')->default(35);
            $table->unsignedSmallInteger('volunteers_checked')->default(35);
            $table->unsignedSmallInteger('apron_compliant_count')->default(35);
            $table->unsignedSmallInteger('hairnet_compliant_count')->default(35);
            $table->unsignedSmallInteger('mask_compliant_count')->default(35);
            $table->unsignedSmallInteger('gloves_compliant_count')->default(35);
            $table->unsignedSmallInteger('safety_shoes_compliant_count')->default(35);
            $table->boolean('clean_nails_compliant')->default(true);
            $table->boolean('no_jewelry_compliant')->default(true);
            $table->boolean('handwash_facility_ready')->default(true);
            $table->decimal('compliance_percentage', 5, 2)->default(100.00);
            $table->string('status', 30)->default('patuh_penuh'); // patuh_penuh, peringatan, pelanggaran_kritis
            $table->text('notes')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->text('corrective_action')->nullable();
            $table->foreignId('verified_by_head_chef_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apd_inspections');
    }
};

