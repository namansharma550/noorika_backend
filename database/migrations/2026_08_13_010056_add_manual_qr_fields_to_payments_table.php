<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('screenshot_path')->nullable()->after('raw_response');
            $table->string('utr_reference')->nullable()->after('screenshot_path');
            $table->timestamp('verified_at')->nullable()->after('utr_reference');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('verified_by');
        });

        // MySQL enums need a raw ALTER to add a new value.
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'pending_verification', 'success', 'failed', 'refunded') DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'pending_verification', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['screenshot_path', 'utr_reference', 'verified_at', 'rejection_reason']);
        });

        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
    }
};
