<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('is_contact_public')->default(true)->after('years_active');
            $table->boolean('is_website_public')->default(true)->after('is_contact_public');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'is_contact_public',
                'is_website_public',
            ]);
        });
    }
};
