<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        "registro_id",
        "tipo_registro",
        "material_id",
        "detalle",
        "tipo_is",
        "cantidad_ingreso",
        "cantidad_salida",
        "cantidad_saldo",
        "fecha",
    ];

    public function producto()
    {
        return $this->belongsTo(Material::class, 'producto_id');
    }

    // REGISTRAR INGRESO
    public static function registroIngreso($tipo_registro, $registro_id = 0, Material $material, $cantidad, $detalle = "")
    {
        //buscar el ultimo registro y usar sus valores
        $ultimo = MovimientoMaterial::where('material_id', $material->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->last();
        if ($ultimo) {
            if (!$detalle || $detalle == "") {
                $detalle = "INGRESO DE MATERIAL";
            }
            MovimientoMaterial::create([
                'registro_id' => $registro_id,
                'tipo_registro' => $tipo_registro, //INGRESO, EGRESO, VENTA, COMPRA,etc...
                'material_id' => $material->id,
                'detalle' => $detalle,
                'tipo_is' => 'INGRESO',
                'cantidad_ingreso' => $cantidad,
                'cantidad_saldo' => (float)$ultimo->cantidad_saldo + (float)$cantidad,
                'cu' => $material->precio,
                'fecha' => date('Y-m-d'),
            ]);
        } else {
            $detalle = "VALOR INICIAL";
            MovimientoMaterial::create([
                'tipo_registro' => $tipo_registro, //INGRESO, EGRESO, VENTA,etc...
                'registro_id' => $registro_id,
                'material_id' => $material->id,
                'detalle' => $detalle,
                'tipo_is' => 'INGRESO',
                'cantidad_ingreso' => $cantidad,
                'cantidad_saldo' => (float)$cantidad,
                'fecha' => date('Y-m-d'),
            ]);
        }

        // INCREMENTAR STOCK
        Material::incrementarStock($material, $cantidad);

        return true;
    }

    // REGISTRAR EGRESO
    public static function registroEgreso($tipo_registro, $registro_id = 0, Material $material, $cantidad, $detalle = "")
    {
        //buscar el ultimo registro y usar sus valores
        $ultimo = MovimientoMaterial::where('material_id', $material->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->last();

        if (!$detalle || $detalle == "") {
            $detalle = "SALIDA DE MATERIAL";
        }

        MovimientoMaterial::create([
            'tipo_registro' => $tipo_registro,
            'registro_id' => $registro_id,
            'material_id' => $material->id,
            'detalle' => $detalle,
            'tipo_is' => 'EGRESO',
            'cantidad_salida' => $cantidad,
            'cantidad_saldo' => (float)$ultimo->cantidad_saldo - (float)$cantidad,
            'fecha' => date('Y-m-d'),
        ]);

        Material::decrementarStock($material, $cantidad);

        return true;
    }

    // ACTUALIZA REGISTROS KARDEX
    // FUNCIÓN QUE ACTUALIZA LOS REGISTROS DEL KARDEX DE UN LUGAR
    // SOLO ACTUALIZARA LOS REGISTROS POSTERIORES AL REGISTRO ACTUALIZADO
    public static function actualizaRegistrosKardex($id, $material_id)
    {
        $siguientes = MovimientoMaterial::where("material_id", $material_id)
            ->where("id", ">=", $id)
            ->get();

        foreach ($siguientes as $item) {
            $anterior = MovimientoMaterial::where("material_id", $material_id)
                ->where("id", "<", $item->id)->get()
                ->last();

            $datos_actualizacion = [
                "cantidad_ingreso" => NULL,
                "cantidad_salida" => NULL,
                "cantidad_saldo" => 0,
            ];

            switch ($item->tipo_registro) {
                case 'INGRESO':
                    $ingreso_material = IngresoMaterial::find($item->registro_id);
                    if ($anterior) {
                        $datos_actualizacion["cantidad_ingreso"] =  $ingreso_material->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$anterior->cantidad_saldo + (float)$ingreso_material->cantidad;
                    } else {
                        $datos_actualizacion["cantidad_ingreso"] =  $ingreso_material->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$ingreso_material->cantidad;
                    }
                    break;
                case 'SALIDA':
                    $salida_material = SalidaDetalle::find($item->registro_id);
                    if ($anterior) {
                        $datos_actualizacion["cantidad_salida"] =  $salida_material->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$anterior->cantidad_saldo - (float)$salida_material->cantidad;
                    } else {
                        $datos_actualizacion["cantidad_salida"] =  $salida_material->cantidad;
                        $datos_actualizacion["cantidad_saldo"] = (float)$salida_material->cantidad * (-1);
                    }

                    break;
            }

            $item->update($datos_actualizacion);
        }
    }
}
