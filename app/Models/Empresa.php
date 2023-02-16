<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [

        'ruc',
        'razon',
        'estado',
        'condicion',
        'ubigeo',
        'tipo_via',
        'nombre_via',
        'codigo_zona',
        'tipo_zona',
        'numero',
        'interior',
        'lote',
        'departamento',
        'manzana',
        'km',
        'status'
    ];
}
