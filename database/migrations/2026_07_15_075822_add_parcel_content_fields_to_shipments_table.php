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
        Schema::table('shipments', function (Blueprint $table): void {
            $table->unsignedInteger('parcel_item_count')->nullable()->after('package_count');
            $table->text('parcel_styles')->nullable()->after('parcel_item_count');
            $table->text('parcel_materials')->nullable()->after('parcel_styles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropColumn(['parcel_item_count', 'parcel_styles', 'parcel_materials']);
        });
    }
};
