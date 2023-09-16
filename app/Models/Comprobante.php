<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'xml_content', 'issuer_name', 'issuer_document_type',
        'issuer_document_number', 'receiver_name', 'receiver_document_type',
        'receiver_document_number', 'total_amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function articulos()
{
    return $this->hasMany(Articulo::class);
}

}
