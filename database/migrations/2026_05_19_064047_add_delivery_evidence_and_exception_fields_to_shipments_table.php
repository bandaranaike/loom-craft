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
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('delivery_recipient_name')->nullable()->after('delivered_at');
            $table->string('delivery_proof_reference')->nullable()->after('delivery_recipient_name');
            $table->string('delivery_evidence_path')->nullable()->after('delivery_proof_reference');
            $table->string('delivery_evidence_original_name')->nullable()->after('delivery_evidence_path');
            $table->string('delivery_evidence_mime_type')->nullable()->after('delivery_evidence_original_name');
            $table->timestamp('delivery_evidence_uploaded_at')->nullable()->after('delivery_evidence_mime_type');
            $table->foreignId('delivery_confirmed_by')->nullable()->after('delivery_evidence_uploaded_at')->constrained('users')->nullOnDelete();
            $table->text('delivery_note')->nullable()->after('delivery_confirmed_by');
            $table->string('delivery_exception_reason', 64)->nullable()->after('delivery_note');
            $table->text('delivery_exception_note')->nullable()->after('delivery_exception_reason');
            $table->timestamp('delivery_exception_at')->nullable()->after('delivery_exception_note');
            $table->unsignedInteger('failed_delivery_attempts')->default(0)->after('delivery_exception_at');

            $table->index(['delivery_exception_reason', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['delivery_exception_reason', 'status']);
            $table->dropConstrainedForeignId('delivery_confirmed_by');
            $table->dropColumn([
                'delivery_recipient_name',
                'delivery_proof_reference',
                'delivery_evidence_path',
                'delivery_evidence_original_name',
                'delivery_evidence_mime_type',
                'delivery_evidence_uploaded_at',
                'delivery_note',
                'delivery_exception_reason',
                'delivery_exception_note',
                'delivery_exception_at',
                'failed_delivery_attempts',
            ]);
        });
    }
};
