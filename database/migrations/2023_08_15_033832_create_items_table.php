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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comprobante_id'); // Relación con la tabla vouchers
            $table->string('description'); // Descripción del artículo
            $table->integer('quantity'); // Cantidad
            $table->decimal('unit_price', 8, 2); // Precio unitario
            $table->decimal('total_amount', 8, 2); // Monto total del artículo
            $table->timestamps();
    
            $table->foreign('comprobante_id')->references('id')->on('comprobantes')->onDelete('cascade'); // Clave foránea
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
