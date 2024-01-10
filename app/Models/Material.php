<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        "nombre",
        "color",
        "stock",
        "imagen",
        "fecha_registro",
    ];

    protected $appends = ["url_imagen"];

    public function getUrlImagenAttribute()
    {
        if ($this->imagen && trim($this->imagen)) {
            return asset("imgs/materials/" . $this->imagen);
        }
        return asset("imgs/materials/default.png");
    }

    public function fecha_stocks()
    {
        return $this->hasMany(FechaStockMaterial::class, 'material_id');
    }

    // FUNCIONES PARA INCREMETAR Y DECREMENTAR EL STOCK
    public static function incrementarStock($material, $cantidad)
    {
        $material->stock = (float)$material->stock + $cantidad;
        $material->save();

        $fecha_actual = date("Y-m-d");
        self::registraFechaStock($material, $fecha_actual);
        return true;
    }
    public static function decrementarStock($material, $cantidad)
    {
        $material->stock = (float)$material->stock - $cantidad;
        $material->save();

        $fecha_actual = date("Y-m-d");
        self::registraFechaStock($material, $fecha_actual);
        return true;
    }
    public static function registraFechaStock($material, $fecha)
    {
        $existe = FechaStockMaterial::where("material_id", $material->id)
            ->where("fecha", $fecha)
            ->get()
            ->first();
        if ($existe) {
            $existe->stock = $material->stock;
            $existe->save();
        } else {
            $material->fecha_stocks()->create([
                "stock" => $material->stock,
                "fecha" => $fecha
            ]);
        }
        return true;
    }
}
