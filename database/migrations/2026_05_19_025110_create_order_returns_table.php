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
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->nullable()->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipping_carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32);
            $table->string('reason', 64);
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('resolution', 64)->nullable();
            $table->string('carrier')->nullable();
            $table->string('service_level')->nullable();
            $table->string('tracking_number')->nullable();
            $table->unsignedInteger('package_count')->nullable();
            $table->decimal('parcel_weight', 10, 2)->nullable();
            $table->string('weight_unit', 10)->nullable();
            $table->decimal('parcel_length', 10, 2)->nullable();
            $table->decimal('parcel_width', 10, 2)->nullable();
            $table->decimal('parcel_height', 10, 2)->nullable();
            $table->string('parcel_dimension_unit', 10)->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('admin_received_at')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamp('vendor_review_started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['shipment_id', 'status']);
            $table->index(['tracking_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
