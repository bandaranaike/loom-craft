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
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->string('type');
            $table->string('full_name');
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city');
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code');
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
