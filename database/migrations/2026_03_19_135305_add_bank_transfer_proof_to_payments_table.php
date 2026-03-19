<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('bank_transfer_slip_path')->nullable()->after('provider_reference');
            $table->string('bank_transfer_slip_original_name')->nullable()->after('bank_transfer_slip_path');
            $table->string('bank_transfer_slip_mime_type')->nullable()->after('bank_transfer_slip_original_name');
            $table->timestamp('bank_transfer_slip_uploaded_at')->nullable()->after('bank_transfer_slip_mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'bank_transfer_slip_path',
                'bank_transfer_slip_original_name',
                'bank_transfer_slip_mime_type',
                'bank_transfer_slip_uploaded_at',
            ]);
        });
    }
};
