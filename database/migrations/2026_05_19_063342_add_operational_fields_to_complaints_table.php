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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('complaint_number')->nullable()->unique()->after('id');
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('shipment_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->foreignId('order_return_id')->nullable()->after('shipment_id')->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->after('order_return_id')->constrained()->nullOnDelete();
            $table->string('category', 64)->nullable()->after('guest_email');
            $table->string('severity', 32)->default('normal')->after('category');
            $table->string('resolution_type', 64)->nullable()->after('status');
            $table->text('resolution_note')->nullable()->after('resolution_type');
            $table->string('courier_claim_reference')->nullable()->after('resolution_note');
            $table->foreignId('assigned_to')->nullable()->after('handled_by')->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at')->nullable()->after('assigned_to');
            $table->timestamp('first_response_due_at')->nullable()->after('opened_at');
            $table->timestamp('sla_due_at')->nullable()->after('first_response_due_at');
            $table->timestamp('first_responded_at')->nullable()->after('sla_due_at');
            $table->timestamp('resolved_at')->nullable()->after('first_responded_at');
            $table->timestamp('closed_at')->nullable()->after('resolved_at');

            $table->index(['order_id', 'status']);
            $table->index(['category', 'status']);
            $table->index(['sla_due_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'status']);
            $table->dropIndex(['category', 'status']);
            $table->dropIndex(['sla_due_at', 'status']);
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('payment_id');
            $table->dropConstrainedForeignId('order_return_id');
            $table->dropConstrainedForeignId('shipment_id');
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn([
                'complaint_number',
                'category',
                'severity',
                'resolution_type',
                'resolution_note',
                'courier_claim_reference',
                'opened_at',
                'first_response_due_at',
                'sla_due_at',
                'first_responded_at',
                'resolved_at',
                'closed_at',
            ]);
        });
    }
};
