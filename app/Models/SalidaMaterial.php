<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        "codigo",
        "nro",
        "producto_id",
        "cantidad",
        "fecha_salida",
        "estado",
        "fecha_registro",
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function salida_detalles()
    {
        return $this->hasMany(SalidaDetalle::class, 'salida_material_id');
    }

    public static function getNuevoCodigo()
    {
        $ultimo_registro = SalidaMaterial::orderBy("nro", "asc")->get()->last();
        $nro = 1;
        if ($ultimo_registro) {
            $nro = (int)$ultimo_registro->nro + 1;
        }

        return ["PROD." . $nro, $nro];
    }
}
