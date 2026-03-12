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
        Schema::create('stock_lot_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_lot_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('location_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantity_initial');

            $table->integer('quantity_remaining');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_lot_locations');
    }
};
