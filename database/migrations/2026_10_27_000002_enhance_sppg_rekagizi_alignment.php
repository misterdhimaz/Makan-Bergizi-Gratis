<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah data kategori penerima & PIC pada tabel beneficiary_institutions, serta dukung tipe 'pesantren'
        Schema::table('beneficiary_institutions', function (Blueprint $table) {
            $table->string('type', 40)->change();
            $table->string('recipient_category', 50)->default('siswa')->after('type');
            $table->string('pic_name', 100)->nullable()->after('contact_phone');
            $table->string('pic_phone', 25)->nullable()->after('pic_name');
        });

        // 2. Tambah validasi PAGU Akuntan & Kategori Penerima pada daily_menus
        Schema::table('daily_menus', function (Blueprint $table) {
            $table->string('recipient_category', 50)->default('siswa')->after('target_portions');
            $table->string('pagu_status', 30)->default('pending')->after('estimated_cost_per_portion'); // pending, sesuai_pagu, melebihi_pagu
            $table->text('pagu_notes')->nullable()->after('pagu_status');
            $table->foreignId('verified_by_accountant_id')->nullable()->after('pagu_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('pagu_verified_at')->nullable()->after('verified_by_accountant_id');
            $table->string('status', 40)->default('draft')->change();
        });

        // 3. Tambah catatan kendala operasional, tindak lanjut & jumlah relawan (35 relawan) pada production_runs
        Schema::table('production_runs', function (Blueprint $table) {
            $table->text('operational_obstacle')->nullable()->after('variance_reason');
            $table->text('action_taken')->nullable()->after('operational_obstacle');
            $table->unsignedSmallInteger('volunteers_count')->default(35)->after('action_taken');
        });

        // 4. Tambah status tindak lanjut QC pada qc_inspections
        Schema::table('qc_inspections', function (Blueprint $table) {
            $table->string('corrective_action_status', 30)->default('open')->after('corrective_action'); // open, resolved
            $table->foreignId('resolved_by')->nullable()->after('corrective_action_status')->constrained('users')->nullOnDelete();
        });

        // 5. Tabel Tiket Keluhan Digital (Fitur 7) - Nomor tiket otomatis, foto bukti, prioritas kritis
        Schema::create('complaint_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 40)->unique(); // TKT-YYYYMMDD-XXX
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
            $table->foreignId('beneficiary_institution_id')->constrained('beneficiary_institutions')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete(); // Guru PIC / Posyandu
            $table->string('category', 50); // porsi_kurang, basi_asam, benda_asing, keterlambatan, ompreng_rusak, lainnya
            $table->enum('severity', ['rendah', 'sedang', 'kritis'])->default('rendah')->index();
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open')->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete(); // Aslap / Kepala Dapur
            $table->text('resolution_notes')->nullable();
            $table->string('resolution_photo_path')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 6. Tabel Log Aktivitas Pengguna / Audit Trail (Fitur 2 & Non-Fungsional Kriteria Penerimaan)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 120);
            $table->string('role', 50)->index();
            $table->string('action', 50)->index(); // login, create, update, approve, verify, dispatch, confirm, resolve, delete
            $table->string('module', 50)->index(); // auth, menu, inventory, production, qc, distribution, complaints, rbac, settings
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 7. Tabel Parameter Master & SOP BGN Terkonfigurasi (Fitur 3 & Non-Fungsional Fleksibilitas)
        Schema::create('sop_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('param_key', 50)->unique();
            $table->string('param_name');
            $table->text('param_value');
            $table->string('unit', 30)->nullable();
            $table->string('category', 50)->index(); // pagu, portion, haccp_temp, apd, operational
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_configurations');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('complaint_tickets');

        Schema::table('qc_inspections', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['corrective_action_status', 'resolved_by']);
        });

        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropColumn(['operational_obstacle', 'action_taken', 'volunteers_count']);
        });

        Schema::table('daily_menus', function (Blueprint $table) {
            $table->dropForeign(['verified_by_accountant_id']);
            $table->dropColumn([
                'recipient_category',
                'pagu_status',
                'pagu_notes',
                'verified_by_accountant_id',
                'pagu_verified_at',
            ]);
        });

        Schema::table('beneficiary_institutions', function (Blueprint $table) {
            $table->dropColumn(['recipient_category', 'pic_name', 'pic_phone']);
        });
    }
};

