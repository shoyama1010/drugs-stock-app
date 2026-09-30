<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_items', function (Blueprint $table) {
            $table->unsignedInteger('shipped_quantity')->default(0)->after('quantity');
            $table->unique(['shipment_id', 'product_id']);
        });

        Schema::table('shipment_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('shipment_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipment_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropUnique(['shipment_id', 'product_id']);
            $table->dropColumn('shipped_quantity');
        });

        Schema::table('shipment_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
