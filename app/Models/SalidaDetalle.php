<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        "salida_material_id",
        "material_id",
        "cantidad",
        "descripcion",
    ];

    public function salida_material()
    {
        return $this->belongsTo(SalidaMaterial::class, 'salida_material_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
