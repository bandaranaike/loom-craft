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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained();
            $table->string('name');
            $table->text('description');
            $table->decimal('vendor_price', 10, 2);
            $table->decimal('commission_rate', 5, 2)->default(7.00);
            $table->decimal('selling_price', 10, 2);
            $table->text('materials')->nullable();
            $table->unsignedInteger('pieces_count')->nullable();
            $table->unsignedInteger('production_time_days')->nullable();
            $table->decimal('dimension_length', 10, 2)->nullable();
            $table->decimal('dimension_width', 10, 2)->nullable();
            $table->decimal('dimension_height', 10, 2)->nullable();
            $table->string('dimension_unit')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
