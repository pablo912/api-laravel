<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dni extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'nombre_completo',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'tipo_seguro',
        'formato',
        'numero_afiliacion',
        'plan_beneficios',
        'fecha_afiliacion',
        'eess',
        'ubicacion',
        "fecha_nacimiento",
        "estado_civil",
        "sexo",
        "edad",
        "domicilio",
        "departamento",
        "provincia",
        "distrito",
        "photo",
        "ubigeo2",
        'status'
    ];
}
