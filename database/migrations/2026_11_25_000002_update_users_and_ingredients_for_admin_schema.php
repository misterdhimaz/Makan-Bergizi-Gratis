<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 80)->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('users', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('beneficiary_institution_id')->constrained('locations')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('users', 'password_expires_at')) {
                $table->timestamp('password_expires_at')->nullable()->after('must_change_password');
            }

            if (! Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->unsignedInteger('failed_login_attempts')->default(0)->after('password_expires_at');
            }

            if (! Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('locked_until');
            }
        });

        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'sku')) {
                $table->dropUnique(['sku']);
                $table->dropColumn('sku');
            }

            if (Schema::hasColumn('ingredients', 'min_stock_threshold')) {
                $table->dropColumn('min_stock_threshold');
            }

            if (! Schema::hasColumn('ingredients', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('unit')->constrained('suppliers')->nullOnDelete();
            }

            if (! Schema::hasColumn('ingredients', 'is_active')) {
                $table->boolean('is_active')->default(true)->index()->after('supplier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
            if (Schema::hasColumn('ingredients', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }
            foreach (['username', 'must_change_password', 'password_expires_at', 'failed_login_attempts', 'locked_until', 'last_login_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
