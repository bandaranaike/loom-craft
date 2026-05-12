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
        Schema::table('shipments', function (Blueprint $table) {
            $table->timestamp('vendor_preparing_at')->nullable()->after('tracking_number');
            $table->timestamp('vendor_handed_to_admin_at')->nullable()->after('vendor_preparing_at');
            $table->timestamp('admin_received_at')->nullable()->after('vendor_handed_to_admin_at');
            $table->timestamp('quality_checked_at')->nullable()->after('admin_received_at');
            $table->timestamp('packed_at')->nullable()->after('quality_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_preparing_at',
                'vendor_handed_to_admin_at',
                'admin_received_at',
                'quality_checked_at',
                'packed_at',
            ]);
        });
    }
};
