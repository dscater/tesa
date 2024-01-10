<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresoMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        "material_id",
        "proveedor_id",
        "descripcion",
        "cantidad",
        "peso",
        "precio",
        "tipo_ingreso_id",
        "fecha_ingreso",
        "fecha_registro",
    ];
    protected $appends = ["nombre_material"];

    public function getNombreMaterialAttribute()
    {
        return $this->material->nombre;
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function tipo_ingreso()
    {
        return $this->belongsTo(TipoIngreso::class, 'tipo_ingreso_id');
    }
}
