<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_menus', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_menus', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('daily_menus', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            }
            if (! Schema::hasColumn('daily_menus', 'last_note')) {
                $table->text('last_note')->nullable()->after('revision_reason');
            }
            if (! Schema::hasColumn('daily_menus', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('last_note');
            }
            if (! Schema::hasColumn('daily_menus', 'submission_cycle')) {
                $table->unsignedInteger('submission_cycle')->default(0)->after('locked_at');
            }
        });

        Schema::table('pagu_parameters', function (Blueprint $table) {
            if (! Schema::hasColumn('pagu_parameters', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            }
        });

        Schema::create('menu_category_portions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained('daily_menus')->cascadeOnDelete();
            $table->foreignId('recipient_category_id')->constrained('recipient_categories')->cascadeOnDelete();
            $table->unsignedInteger('portions');
            $table->timestamps();

            $table->unique(['daily_menu_id', 'recipient_category_id'], 'menu_category_unique');
        });

        Schema::create('menu_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained('daily_menus')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('slot', 30)->nullable();
            $table->timestamps();

            $table->unique(['daily_menu_id', 'ingredient_id'], 'menu_component_unique');
        });

        Schema::create('menu_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained('daily_menus')->cascadeOnDelete();
            $table->string('aksi', 80);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('pagu_parameter_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_category_id')->constrained('recipient_categories')->cascadeOnDelete();
            $table->unsignedInteger('nilai_lama')->nullable();
            $table->unsignedInteger('nilai_baru');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('pagu_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained('daily_menus')->cascadeOnDelete();
            $table->unsignedInteger('siklus_pengajuan');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('hasil', 30);
            $table->text('catatan');
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->unique(['daily_menu_id', 'siklus_pengajuan'], 'pagu_check_cycle_unique');
        });

        Schema::create('pagu_check_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pagu_check_id')->constrained('pagu_checks')->cascadeOnDelete();
            $table->foreignId('recipient_category_id')->constrained('recipient_categories')->cascadeOnDelete();
            $table->unsignedInteger('estimasi_per_porsi')->nullable();
            $table->unsignedInteger('pagu_saat_itu');
            $table->timestamps();

            $table->unique(['pagu_check_id', 'recipient_category_id'], 'pagu_estimate_unique');
        });

        $this->migrateLegacyStatuses();
    }

    public function down(): void
    {
        Schema::dropIfExists('pagu_check_estimates');
        Schema::dropIfExists('pagu_checks');
        Schema::dropIfExists('pagu_parameter_histories');
        Schema::dropIfExists('menu_histories');
        Schema::dropIfExists('menu_components');
        Schema::dropIfExists('menu_category_portions');

        Schema::table('pagu_parameters', function (Blueprint $table) {
            if (Schema::hasColumn('pagu_parameters', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });

        Schema::table('daily_menus', function (Blueprint $table) {
            if (Schema::hasColumn('daily_menus', 'submitted_by')) {
                $table->dropConstrainedForeignId('submitted_by');
            }
            foreach (['submitted_at', 'last_note', 'locked_at', 'submission_cycle'] as $col) {
                if (Schema::hasColumn('daily_menus', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function migrateLegacyStatuses(): void
    {
        $map = [
            'draft' => 'draf',
            'pending_budget_review' => 'menunggu_pemeriksaan_anggaran',
            'pending_approval' => 'lolos_pagu',
            'budget_revision' => 'perlu_revisi_anggaran',
            'approved' => 'disetujui',
            'revision_needed' => 'minta_revisi',
        ];

        foreach ($map as $from => $to) {
            DB::table('daily_menus')->where('status', $from)->update(['status' => $to]);
        }
    }
};
