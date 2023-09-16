<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticulosTable extends Migration
{
    public function up()
    {
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comprobante_id'); // Relación con la tabla comprobantes
            $table->string('nombre'); // Nombre del artículo
            $table->integer('cantidad'); // Cantidad del artículo
            $table->decimal('precio', 8, 2); // Precio del artículo
            $table->timestamps();

            $table->foreign('comprobante_id')->references('id')->on('comprobantes')->onDelete('cascade'); // Clave foránea
        });
    }

    public function down()
    {
        Schema::dropIfExists('articulos');
    }
}
