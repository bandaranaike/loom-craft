<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->foreignId('shipping_carrier_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->foreignId('shipping_service_id')->nullable()->after('shipping_carrier_id')->constrained()->nullOnDelete();
        });

        DB::table('shipments')
            ->whereNotNull('carrier')
            ->where('carrier', '!=', '')
            ->orderBy('id')
            ->each(function (object $shipment): void {
                $carrierId = DB::table('shipping_carriers')
                    ->where('name', $shipment->carrier)
                    ->value('id');

                if ($carrierId === null) {
                    $carrierId = DB::table('shipping_carriers')->insertGetId([
                        'name' => $shipment->carrier,
                        'code' => null,
                        'is_active' => true,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $serviceId = null;

                if (is_string($shipment->service_level) && $shipment->service_level !== '') {
                    $serviceId = DB::table('shipping_services')
                        ->where('shipping_carrier_id', $carrierId)
                        ->where('name', $shipment->service_level)
                        ->value('id');

                    if ($serviceId === null) {
                        $serviceId = DB::table('shipping_services')->insertGetId([
                            'shipping_carrier_id' => $carrierId,
                            'name' => $shipment->service_level,
                            'code' => null,
                            'is_active' => true,
                            'sort_order' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                DB::table('shipments')
                    ->where('id', $shipment->id)
                    ->update([
                        'shipping_carrier_id' => $carrierId,
                        'shipping_service_id' => $serviceId,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['shipping_service_id']);
            $table->dropForeign(['shipping_carrier_id']);
            $table->dropColumn(['shipping_service_id', 'shipping_carrier_id']);
        });
    }
};
