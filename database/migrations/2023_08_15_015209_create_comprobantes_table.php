<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprobantesTable extends Migration
{
    public function up()
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Relación con la tabla users
            $table->string('issuer_name'); // Nombre del emisor
            $table->string('issuer_document_type'); // Tipo de documento del emisor (ej. DNI, RUC, etc.)
            $table->string('issuer_document_number'); // Número de documento del emisor
            $table->string('receiver_name'); // Nombre del receptor
            $table->string('receiver_document_type'); // Tipo de documento del receptor (ej. DNI, RUC, etc.)
            $table->string('receiver_document_number'); // Número de documento del receptor
            $table->decimal('total_amount', 8, 2); // Monto total del comprobante
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Clave foránea
        });
    }

    public function down()
    {
        Schema::dropIfExists('comprobantes');
    }
}
