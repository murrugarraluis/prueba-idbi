<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;

    protected $fillable = [
        'comprobante_id',
        'nombre',
        'cantidad',
        'precio',
    ];

    // Relación con la tabla comprobantes
    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }
}
