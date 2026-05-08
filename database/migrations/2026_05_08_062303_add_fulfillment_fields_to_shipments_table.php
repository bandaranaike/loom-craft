<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('shipment_number')->nullable()->after('id');
            $table->string('service_level')->nullable()->after('carrier');
            $table->unsignedInteger('package_count')->default(1)->after('tracking_number');
            $table->decimal('parcel_weight', 10, 2)->nullable()->after('package_count');
            $table->string('weight_unit', 10)->nullable()->after('parcel_weight');
            $table->decimal('parcel_length', 10, 2)->nullable()->after('weight_unit');
            $table->decimal('parcel_width', 10, 2)->nullable()->after('parcel_length');
            $table->decimal('parcel_height', 10, 2)->nullable()->after('parcel_width');
            $table->string('parcel_dimension_unit', 10)->nullable()->after('parcel_height');
        });

        DB::table('shipments')
            ->orderBy('id')
            ->get(['id', 'created_at'])
            ->each(function (object $shipment): void {
                $date = Carbon::parse($shipment->created_at ?? now());

                DB::table('shipments')
                    ->where('id', $shipment->id)
                    ->update([
                        'shipment_number' => sprintf('SHP-%s-%06d', $date->format('Ym'), $shipment->id),
                    ]);
            });

        Schema::table('shipments', function (Blueprint $table) {
            $table->unique('shipment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropUnique('shipments_shipment_number_unique');
            $table->dropColumn([
                'shipment_number',
                'service_level',
                'package_count',
                'parcel_weight',
                'weight_unit',
                'parcel_length',
                'parcel_width',
                'parcel_height',
                'parcel_dimension_unit',
            ]);
        });
    }
};
