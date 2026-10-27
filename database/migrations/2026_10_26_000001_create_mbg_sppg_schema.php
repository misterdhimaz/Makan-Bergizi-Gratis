<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mitra Penerima Manfaat (Sekolah & Posyandu di wilayah SPPG Sungai Selayur 1)
        Schema::create('beneficiary_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Contoh: SCH-SS1-001, POS-SS1-001
            $table->string('name');
            $table->enum('type', ['sd', 'smp', 'sma', 'paud_tk', 'posyandu']);
            $table->unsignedInteger('default_quota')->default(0); // Kuota porsi reguler
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contact_phone', 25)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Users dengan 7 Role RBAC
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 25)->nullable();
            $table->string('password');
            $table->enum('role', [
                'admin',
                'ahli_gizi',
                'akuntan',
                'kepala_dapur',
                'asisten_lapangan',
                'tim_distribusi',
                'guru_pic',
            ])->index();
            // Khusus untuk role guru_pic: mengaitkan akun dengan Sekolah / Posyandu terkait
            $table->foreignId('beneficiary_institution_id')
                ->nullable()
                ->constrained('beneficiary_institutions')
                ->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Master Bahan Baku & Persediaan Gudang SPPG
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('name');
            $table->enum('category', ['karbohidrat', 'protein_hewani', 'protein_nabati', 'sayuran', 'buah', 'susu', 'bumbu_minyak']);
            $table->string('unit', 20); // kg, liter, butir, pcs, ikat
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('min_stock_threshold', 12, 2)->default(10);
            $table->decimal('unit_cost', 14, 2)->default(0); // Harga rata-rata per satuan (Rp)
            $table->timestamps();
        });

        // 4. Perencanaan & Validasi Menu Harian (Ahli Gizi -> Approval Kepala Dapur)
        Schema::create('daily_menus', function (Blueprint $table) {
            $table->id();
            $table->date('operational_date')->unique();
            $table->string('menu_code', 30)->unique();
            $table->string('menu_name'); // Contoh: Paket Gizi Nusantara A (Nasi, Ayam Bakar Madu, Tumis Buncis Wortel, Tempe Bacem, Pisang, Susu)
            $table->unsignedInteger('target_portions');
            // Informasi Nilai Gizi per Porsi
            $table->decimal('calories_kcal', 6, 2);
            $table->decimal('protein_gram', 6, 2);
            $table->decimal('carbs_gram', 6, 2);
            $table->decimal('fat_gram', 6, 2);
            $table->decimal('fiber_gram', 6, 2)->default(0);
            $table->decimal('estimated_cost_per_portion', 12, 2)->default(15000);
            // Alur Validasi
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'revision_needed', 'in_production', 'completed'])
                ->default('draft')
                ->index();
            $table->foreignId('created_by')->constrained('users'); // Ahli Gizi
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Kepala Dapur
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
        });

        // 5. Detail Komposisi Bahan Baku per Menu Harian (Gramasi & Kebutuhan Total)
        Schema::create('daily_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained('daily_menus')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('qty_per_portion', 10, 4); // Kebutuhan per 1 porsi dalam unit bahan
            $table->decimal('total_planned_qty', 12, 2); // qty_per_portion * target_portions
            $table->decimal('actual_used_qty', 12, 2)->nullable(); // Diisi saat produksi aktual
            $table->string('preparation_notes')->nullable(); // Contoh: Potong dadu 2cm
            $table->timestamps();
        });

        // 6. Log Mutasi Bahan Baku (Penerimaan Supplier & Pemakaian Produksi)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 40)->unique();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('daily_menu_id')->nullable()->constrained('daily_menus')->nullOnDelete();
            $table->enum('type', ['inbound_purchase', 'outbound_production', 'spoilage_adjustment']);
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_cost', 16, 2)->default(0);
            $table->string('supplier_name')->nullable();
            $table->string('invoice_photo_path')->nullable();
            $table->enum('freshness_condition', ['sangat_segar', 'layak', 'ditolak'])->default('sangat_segar');
            $table->foreignId('recorded_by')->constrained('users'); // Asisten Lapangan / Akuntan
            $table->foreignId('verified_by_accountant')->nullable()->constrained('users'); // Akuntan
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Monitoring Produksi Dapur & Perhitungan Selisih Porsi Real-Time
        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->unique()->constrained('daily_menus')->cascadeOnDelete();
            $table->unsignedInteger('planned_portions');
            $table->unsignedInteger('actual_good_portions')->default(0);
            $table->unsignedInteger('rejected_damaged_portions')->default(0);
            // Selisih = actual_good_portions - planned_portions (negatif jika kurang, positif jika surplus)
            $table->integer('portion_variance')->default(0);
            $table->timestamp('cooking_started_at')->nullable();
            $table->timestamp('cooking_finished_at')->nullable();
            $table->timestamp('packaging_finished_at')->nullable();
            $table->enum('status', ['scheduled', 'cooking', 'qc_inspection', 'packaging', 'ready_for_dispatch'])
                ->default('scheduled');
            $table->foreignId('supervised_by')->constrained('users'); // Kepala Dapur / Asisten Lapangan
            $table->text('variance_reason')->nullable();
            $table->timestamps();
        });

        // 8. Quality Control (Keamanan Pangan, Suhu & Kepatuhan APD Relawan)
        Schema::create('qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_run_id')->constrained('production_runs')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users'); // Ahli Gizi / Kepala Dapur
            // Parameter Suhu Keamanan Pangan (°C)
            $table->decimal('cooking_core_temp_c', 5, 2); // Standar minimal >= 75°C
            $table->decimal('holding_temp_c', 5, 2);      // Standar >= 60°C (panas)
            $table->decimal('pre_dispatch_temp_c', 5, 2);
            // Kepatuhan APD Relawan Dapur
            $table->unsignedSmallInteger('total_kitchen_staff_present')->default(0);
            $table->boolean('apd_mask_compliant')->default(false);
            $table->boolean('apd_hairnet_compliant')->default(false);
            $table->boolean('apd_gloves_compliant')->default(false);
            $table->boolean('apd_apron_compliant')->default(false);
            $table->boolean('apd_safety_shoes_compliant')->default(false);
            $table->boolean('kitchen_sanitation_passed')->default(false);
            // Food Bank Sample (Sampel Arsip 2x24 Jam)
            $table->boolean('food_sample_retained')->default(false);
            $table->string('food_sample_code', 40)->nullable();
            $table->enum('qc_verdict', ['passed', 'conditional_pass', 'failed_hold'])->default('passed');
            $table->text('corrective_action')->nullable();
            $table->timestamp('inspected_at');
            $table->timestamps();
        });

        // 9. Distribusi Makanan ke Sekolah & Posyandu
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('waybill_number', 40)->unique(); // Nomor Surat Jalan (Contoh: SJ/MBG-SS1/20261026/001)
            $table->foreignId('production_run_id')->constrained('production_runs')->cascadeOnDelete();
            $table->foreignId('beneficiary_institution_id')->constrained('beneficiary_institutions')->restrictOnDelete();
            $table->foreignId('courier_id')->constrained('users'); // Tim Distribusi
            $table->string('vehicle_plate_number', 20)->nullable();
            $table->unsignedInteger('allocated_portions');
            $table->unsignedInteger('dispatched_portions');
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->string('dispatch_photo_path')->nullable();
            $table->string('signed_waybill_path')->nullable();
            $table->enum('status', ['preparing', 'in_transit', 'arrived', 'confirmed_clean', 'confirmed_with_issue'])
                ->default('preparing')
                ->index();
            $table->text('courier_notes')->nullable();
            $table->timestamps();
        });

        // 10. Konfirmasi Penerimaan & Uji Organoleptik oleh Guru PIC / Posyandu
        Schema::create('delivery_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->unique()->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('confirmed_by')->constrained('users'); // Guru PIC
            $table->unsignedInteger('received_good_portions');
            $table->unsignedInteger('received_damaged_or_missing_portions')->default(0);
            $table->integer('reception_variance')->default(0); // received_good - dispatched
            // Uji Organoleptik (Sensorik Pangan oleh Guru PIC sebelum dibagikan ke siswa)
            $table->enum('aroma_test', ['normal_segar', 'kurang_sedap', 'basi_asam'])->default('normal_segar');
            $table->enum('taste_test', ['normal_lezat', 'hambar', 'tidak_layak'])->default('normal_lezat');
            $table->enum('color_visual_test', ['cerah_normal', 'pucat', 'berubah_warna'])->default('cerah_normal');
            $table->enum('texture_test', ['baik_sesuai', 'terlalu_keras_lembek', 'berlendir'])->default('baik_sesuai');
            $table->decimal('arrival_temperature_c', 5, 2)->nullable();
            $table->boolean('is_fit_for_consumption')->default(true);
            $table->enum('complaint_severity', ['none', 'low', 'medium', 'critical'])->default('none')->index();
            $table->text('feedback_or_complaint')->nullable();
            $table->string('reception_photo_path')->nullable();
            $table->timestamp('confirmed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_confirmations');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('qc_inspections');
        Schema::dropIfExists('production_runs');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('daily_menu_items');
        Schema::dropIfExists('daily_menus');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('beneficiary_institutions');
    }
};

