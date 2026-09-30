<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('shipment_number')->nullable()->unique()->after('id');
            $table->foreignId('confirmed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('delivered_by')->nullable()->after('confirmed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable()->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('requested_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->text('note')->nullable()->after('delivered_at');
            $table->index('status');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['user_id']);
            $table->dropConstrainedForeignId('delivered_by');
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropIndex(['status']);
            $table->dropUnique(['shipment_number']);
            $table->dropColumn([
                'shipment_number',
                'requested_at',
                'confirmed_at',
                'delivered_at',
                'note',
            ]);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
