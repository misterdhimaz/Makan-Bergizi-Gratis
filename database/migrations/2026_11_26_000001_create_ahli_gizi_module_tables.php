<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_menus', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_menus', 'categories_json')) {
                $table->json('categories_json')->nullable()->after('recipient_category');
            }
            if (! Schema::hasColumn('daily_menus', 'components_json')) {
                $table->json('components_json')->nullable()->after('categories_json');
            }
            if (! Schema::hasColumn('daily_menus', 'notes')) {
                $table->text('notes')->nullable()->after('approval_notes');
            }
            if (! Schema::hasColumn('daily_menus', 'revision_reason')) {
                $table->text('revision_reason')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('daily_menus', 'status_history_json')) {
                $table->json('status_history_json')->nullable()->after('revision_reason');
            }
        });

        Schema::table('daily_menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_menu_items', 'dispatched_qty')) {
                $table->decimal('dispatched_qty', 12, 2)->nullable()->after('total_planned_qty');
            }
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_transactions', 'planned_qty')) {
                $table->decimal('planned_qty', 12, 2)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('inventory_transactions', 'verification_status')) {
                $table->string('verification_status', 30)->default('menunggu_verifikasi')->index()->after('freshness_condition');
            }
            if (! Schema::hasColumn('inventory_transactions', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verified_by_accountant')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('inventory_transactions', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
            if (! Schema::hasColumn('inventory_transactions', 'verification_notes')) {
                $table->text('verification_notes')->nullable()->after('verified_at');
            }
        });

        Schema::table('complaint_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('complaint_tickets', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('beneficiary_institution_id')->constrained('locations')->nullOnDelete();
            }
            if (! Schema::hasColumn('complaint_tickets', 'reported_at')) {
                $table->timestamp('reported_at')->nullable()->after('status');
            }
        });

        Schema::create('production_obstacles', function (Blueprint $table) {
            $table->id();
            $table->date('operational_date')->index();
            $table->foreignId('daily_menu_id')->nullable()->constrained('daily_menus')->nullOnDelete();
            $table->text('description');
            $table->string('status', 30)->default('terbuka')->index();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('food_safety_checks', function (Blueprint $table) {
            $table->id();
            $table->date('check_date')->index();
            $table->string('shift', 20)->index();
            $table->json('temperatures_json')->nullable();
            $table->json('hygiene_json')->nullable();
            $table->json('apd_json')->nullable();
            $table->unsignedSmallInteger('total_volunteers')->default(35);
            $table->unsignedSmallInteger('compliant_volunteers')->default(35);
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['check_date', 'shift'], 'food_safety_date_shift_unique');
        });

        Schema::create('food_safety_findings', function (Blueprint $table) {
            $table->id();
            $table->date('finding_date')->index();
            $table->string('type', 30);
            $table->text('description');
            $table->string('status', 30)->default('terbuka')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('handler_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_safety_findings');
        Schema::dropIfExists('food_safety_checks');
        Schema::dropIfExists('production_obstacles');

        Schema::table('complaint_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('complaint_tickets', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }
            if (Schema::hasColumn('complaint_tickets', 'reported_at')) {
                $table->dropColumn('reported_at');
            }
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_transactions', 'verified_by')) {
                $table->dropConstrainedForeignId('verified_by');
            }
            foreach (['planned_qty', 'verification_status', 'verified_at', 'verification_notes'] as $col) {
                if (Schema::hasColumn('inventory_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('daily_menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('daily_menu_items', 'dispatched_qty')) {
                $table->dropColumn('dispatched_qty');
            }
        });

        Schema::table('daily_menus', function (Blueprint $table) {
            foreach (['categories_json', 'components_json', 'notes', 'revision_reason', 'status_history_json'] as $col) {
                if (Schema::hasColumn('daily_menus', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
