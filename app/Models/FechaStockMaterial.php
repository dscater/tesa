<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechaStockMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        "material_id", "fecha", "stock",
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
