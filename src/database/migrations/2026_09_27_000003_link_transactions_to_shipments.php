<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->after('store_id')->constrained()->nullOnDelete();
            $table->foreignId('shipment_item_id')->nullable()->after('shipment_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipment_item_id');
            $table->dropConstrainedForeignId('shipment_id');
        });
    }
};
