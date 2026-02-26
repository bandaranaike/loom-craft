<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('display_name');
            $table->string('tagline')->nullable()->after('bio');
            $table->string('website_url')->nullable()->after('tagline');
            $table->string('contact_email')->nullable()->after('website_url');
            $table->string('contact_phone', 50)->nullable()->after('contact_email');
            $table->string('whatsapp_number', 50)->nullable()->after('contact_phone');
            $table->string('logo_path')->nullable()->after('whatsapp_number');
            $table->string('cover_image_path')->nullable()->after('logo_path');
            $table->string('about_title')->nullable()->after('cover_image_path');
            $table->json('craft_specialties')->nullable()->after('about_title');
            $table->unsignedSmallInteger('years_active')->nullable()->after('craft_specialties');

            $table->unique('slug');
        });

        DB::table('vendors')
            ->select(['id', 'display_name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $vendor): void {
                $base = Str::slug($vendor->display_name);
                $slug = ($base !== '' ? $base : 'vendor').'-'.$vendor->id;

                DB::table('vendors')
                    ->where('id', $vendor->id)
                    ->update(['slug' => $slug]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'tagline',
                'website_url',
                'contact_email',
                'contact_phone',
                'whatsapp_number',
                'logo_path',
                'cover_image_path',
                'about_title',
                'craft_specialties',
                'years_active',
            ]);
        });
    }
};
