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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('public_id', 40)->nullable()->after('id');
        });

        DB::table('orders')
            ->select('id')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $order): void {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'public_id' => $this->newPublicId(),
                    ]);
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_public_id_unique');
            $table->dropColumn('public_id');
        });
    }

    private function newPublicId(): string
    {
        do {
            $publicId = 'ORD-'.Str::upper(Str::random(28));
        } while (DB::table('orders')->where('public_id', $publicId)->exists());

        return $publicId;
    }
};
