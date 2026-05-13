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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('cod_collected_amount', 10, 2)->nullable()->after('bank_transfer_slip_uploaded_at');
            $table->decimal('cod_remitted_amount', 10, 2)->nullable()->after('cod_collected_amount');
            $table->string('cod_remittance_reference')->nullable()->after('cod_remitted_amount');
            $table->text('cod_settlement_note')->nullable()->after('cod_remittance_reference');
            $table->foreignId('cod_settled_by')->nullable()->after('cod_settlement_note')->constrained('users')->nullOnDelete();
            $table->timestamp('cod_settled_at')->nullable()->after('cod_settled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['cod_settled_by']);
            $table->dropColumn([
                'cod_collected_amount',
                'cod_remitted_amount',
                'cod_remittance_reference',
                'cod_settlement_note',
                'cod_settled_by',
                'cod_settled_at',
            ]);
        });
    }
};
