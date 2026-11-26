<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipient_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('type', 40);
            $table->text('address');
            $table->unsignedInteger('target_portions')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('location_category_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('recipient_category_id')->constrained('recipient_categories')->cascadeOnDelete();
            $table->unsignedInteger('portion_count')->default(0);
            $table->timestamps();

            $table->unique(['location_id', 'recipient_category_id'], 'loc_cat_quota_unique');
        });

        Schema::create('portion_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_category_id')->constrained('recipient_categories')->cascadeOnDelete();
            $table->string('meal_component', 50);
            $table->decimal('quantity', 8, 2);
            $table->enum('unit', ['g', 'ml'])->default('g');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['recipient_category_id', 'meal_component'], 'portion_cat_comp_unique');
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('category_focus')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pagu_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_category_id')->constrained('recipient_categories')->cascadeOnDelete();
            $table->unsignedInteger('max_budget_per_portion');
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('sop_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->enum('type', ['suhu', 'kebersihan', 'apd'])->index();
            $table->string('title');
            $table->string('threshold_value', 60)->nullable();
            $table->string('frequency', 60)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->date('operational_date')->index();
            $table->string('waybill_number', 50)->unique();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('status', 30)->default('Disiapkan')->index();
            $table->unsignedInteger('dispatched_portions')->default(0);
            $table->unsignedInteger('confirmed_portions')->nullable();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('occurred_at')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name_snapshot')->nullable();
            $table->string('role', 60)->nullable()->index();
            $table->string('action', 60)->index();
            $table->string('target')->nullable();
            $table->string('module', 60)->index();
            $table->string('ip_address', 45)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("
                CREATE TRIGGER prevent_audit_logs_update
                BEFORE UPDATE ON audit_logs
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tabel audit_logs bersifat immutable dan tidak dapat diubah.';
                END
            ");

            DB::unprepared("
                CREATE TRIGGER prevent_audit_logs_delete
                BEFORE DELETE ON audit_logs
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tabel audit_logs bersifat immutable dan tidak dapat dihapus.';
                END
            ");
        }

        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('identifier');
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('data_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('revisable_type');
            $table->unsignedBigInteger('revisable_id');
            $table->json('old_values');
            $table->json('new_values');
            $table->text('reason');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['revisable_type', 'revisable_id']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('security')->index();
            $table->string('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('trigger_type', 30)->default('manual');
            $table->string('status', 30)->default('success');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_audit_logs_update');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_audit_logs_delete');
        }

        Schema::dropIfExists('system_backups');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('data_revisions');
        Schema::dropIfExists('password_reset_requests');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('delivery_routes');
        Schema::dropIfExists('sop_checklists');
        Schema::dropIfExists('pagu_parameters');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('portion_standards');
        Schema::dropIfExists('location_category_quotas');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('recipient_categories');
    }
};
