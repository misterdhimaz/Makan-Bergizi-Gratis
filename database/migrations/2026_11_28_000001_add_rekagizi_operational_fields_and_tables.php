<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_routes', 'kelompok_rute')) {
                $table->string('kelompok_rute', 50)->nullable()->after('waybill_number');
            }
            if (! Schema::hasColumn('delivery_routes', 'planned_departure_time')) {
                $table->string('planned_departure_time', 10)->nullable()->after('operational_date');
            }
            if (! Schema::hasColumn('delivery_routes', 'nama_penerima_serah')) {
                $table->string('nama_penerima_serah', 120)->nullable()->after('notes');
            }
            if (! Schema::hasColumn('delivery_routes', 'jumlah_diserahkan')) {
                $table->unsignedInteger('jumlah_diserahkan')->nullable()->after('nama_penerima_serah');
            }
            if (! Schema::hasColumn('delivery_routes', 'catatan_lapangan')) {
                $table->text('catatan_lapangan')->nullable()->after('jumlah_diserahkan');
            }
        });

        if (! Schema::hasTable('delivery_status_logs')) {
            Schema::create('delivery_status_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_route_id')->constrained('delivery_routes')->cascadeOnDelete();
                $table->string('status', 40);
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('delivery_documents')) {
            Schema::create('delivery_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_route_id')->constrained('delivery_routes')->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->string('mime', 100);
                $table->unsignedBigInteger('size');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        Schema::table('complaint_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('complaint_tickets', 'sequence')) {
                $table->unsignedInteger('sequence')->nullable()->after('ticket_number');
            }
            if (! Schema::hasColumn('complaint_tickets', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('reported_at');
            }
        });

        if (! Schema::hasTable('complaint_histories')) {
            Schema::create('complaint_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('complaint_ticket_id')->constrained('complaint_tickets')->cascadeOnDelete();
                $table->string('status', 40);
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        Schema::table('data_revisions', function (Blueprint $table) {
            if (! Schema::hasColumn('data_revisions', 'field')) {
                $table->string('field', 100)->nullable()->after('revisable_id');
            }
            if (! Schema::hasColumn('data_revisions', 'status')) {
                $table->string('status', 30)->default('menunggu')->after('requested_by');
            }
            if (! Schema::hasColumn('data_revisions', 'decision_notes')) {
                $table->text('decision_notes')->nullable()->after('approved_by');
            }
        });

        Schema::table('food_safety_findings', function (Blueprint $table) {
            if (! Schema::hasColumn('food_safety_findings', 'handled_at')) {
                $table->timestamp('handled_at')->nullable()->after('handler_note');
            }
            if (! Schema::hasColumn('food_safety_findings', 'food_safety_check_id')) {
                $table->foreignId('food_safety_check_id')->nullable()->after('id')->constrained('food_safety_checks')->nullOnDelete();
            }
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_transactions', 'procurement_status')) {
                $table->string('procurement_status', 30)->default('menunggu_terima')->after('verification_status');
            }
            if (! Schema::hasColumn('inventory_transactions', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('procurement_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $cols = array_filter(['procurement_status', 'received_at'], fn (string $c) => Schema::hasColumn('inventory_transactions', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('food_safety_findings', function (Blueprint $table) {
            if (Schema::hasColumn('food_safety_findings', 'food_safety_check_id')) {
                $table->dropConstrainedForeignId('food_safety_check_id');
            }
            if (Schema::hasColumn('food_safety_findings', 'handled_at')) {
                $table->dropColumn('handled_at');
            }
        });

        Schema::table('data_revisions', function (Blueprint $table) {
            $cols = array_filter(['field', 'status', 'decision_notes'], fn (string $c) => Schema::hasColumn('data_revisions', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('complaint_histories');

        Schema::table('complaint_tickets', function (Blueprint $table) {
            $cols = array_filter(['sequence', 'first_response_at'], fn (string $c) => Schema::hasColumn('complaint_tickets', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('delivery_documents');
        Schema::dropIfExists('delivery_status_logs');

        Schema::table('delivery_routes', function (Blueprint $table) {
            $cols = array_filter([
                'kelompok_rute',
                'planned_departure_time',
                'nama_penerima_serah',
                'jumlah_diserahkan',
                'catatan_lapangan',
            ], fn (string $c) => Schema::hasColumn('delivery_routes', $c));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
