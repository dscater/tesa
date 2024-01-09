<?php

namespace App\Http\Controllers;

use App\Models\HistorialAccion;
use App\Models\IngresoProducto;
use App\Models\KardexProducto;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use App\Models\SalidaDetalle;
use App\Models\SalidaMaterial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalidaMaterialController extends Controller
{
    public $validacion = [
        'producto_id' => 'required',
        'cantidad' => 'required|numeric|min:1',
        'fecha_salida' => 'required|date',
    ];

    public $mensajes = [
        "producto_id.required" => "Este campo es obligatorio",
        "cantidad.required" => "Este campo es obligatorio",
        "cantidad.numeric" => "Debes ingresar un valor numerico",
        "cantidad.min" => "Debes ingresar un valor de por lo menos :min",
        "fecha_salida.required" => "Este campo es obligatorio",
        "fecha_salida.date" => "Debes ingresar una fecha valida",
    ];

    public function index(Request $request)
    {
        $salida_materials = SalidaMaterial::with(["producto", "salida_detalles.material"])->orderBy("nro", "desc")->get();
        return response()->JSON(['salida_materials' => $salida_materials, 'total' => count($salida_materials)], 200);
    }

    public function paginado(Request $request)
    {
        $estado = $request->estado;
        $codigo = $request->codigo;

        $salida_materials = SalidaMaterial::with(["producto", "salida_detalles.material"]);

        $salida_materials->where("codigo", "LIKE", "%$codigo%");
        if ($estado != 'TODOS') {
            $salida_materials->where("estado", $estado);
        }

        $salida_materials->orderBy("nro", "desc");
        $per_page = 12;
        $salida_materials = $salida_materials->paginate($per_page);
        return response()->JSON([
            'salida_materials' => $salida_materials,
            'per_page' => $per_page,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate($this->validacion, $this->mensajes);

        if (!isset($request->salida_detalles) || !$request->salida_detalles || count($request->salida_detalles) <= 0) {
            throw new Exception("Debes ingresar al menos un material");
        }
        $errors = self::validarArray($request["salida_detalles"]);
        if (count($errors) > 0) {
            return response()->JSON([
                "errors" => $errors
            ], 422);
        }
        $request["fecha_registro"] = date("Y-m-d");
        unset($request["estado"]);
        DB::beginTransaction();
        try {
            $array_codigo = SalidaMaterial::getNuevoCodigo();
            $request["codigo"] = $array_codigo[0];
            $request["nro"] = $array_codigo[1];
            // crear SalidaMaterial
            $nueva_salida_material = SalidaMaterial::create(array_map('mb_strtoupper', $request->except("salida_detalles")));

            $datos_original = HistorialAccion::getDetalleRegistro($nueva_salida_material, "salida_materials");
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'CREACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' REGISTRO UNA SALIDA DE MATERIAL',
                'datos_original' => $datos_original,
                'modulo' => 'SALIDA DE MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);

            // registrar detalles
            $salida_detalles = $request->salida_detalles;
            foreach ($salida_detalles as $value) {
                $salida_detalle = $nueva_salida_material->salida_detalles()->create([
                    "material_id" => $value["material_id"],
                    "cantidad" => $value["cantidad"],
                    "descripcion" => mb_strtoupper($value["descripcion"]),
                ]);
                // registrar movimiento
                MovimientoMaterial::registroEgreso("SALIDA", $salida_detalle->id, $salida_detalle->material, $salida_detalle->cantidad, $salida_detalle->descripcion);
            }

            DB::commit();
            return response()->JSON([
                'sw' => true,
                'salida_material' => $nueva_salida_material,
                'msj' => 'El registro se realizó de forma correcta',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->JSON([
                'sw' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function terminado(SalidaMaterial $salida_material)
    {
        DB::beginTransaction();
        try {
            $datos_original = HistorialAccion::getDetalleRegistro($salida_material, "salida_materials");
            $salida_material->update([
                "estado" => "TERMINADO"
            ]);

            $datos_nuevo = HistorialAccion::getDetalleRegistro($salida_material, "salida_materials");
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'MODIFICACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' MODIFICÓ UNA SALIDA DE MATERIAL',
                'datos_original' => $datos_original,
                'datos_nuevo' => $datos_nuevo,
                'modulo' => 'SALIDA DE MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);

            // ACTUALIZAR STOCK PRODUCTO
            $producto = $salida_material->producto;
            // registrar kardex
            $nuevo_ingreso_producto = IngresoProducto::create([
                "producto_id" => $producto->id,
                "precio_compra" => $producto->precio,
                "cantidad" => $salida_material->cantidad,
                "fecha_fabricacion" => date("Y-m-d"),
                "descripcion" => "INGRESO POR FABRICACIÓN",
                "fecha_registro" => date("Y-m-d"),
            ]);
            KardexProducto::registroIngreso("INGRESO", $nuevo_ingreso_producto->id, $nuevo_ingreso_producto->producto, $nuevo_ingreso_producto->cantidad, $nuevo_ingreso_producto->producto->precio, $nuevo_ingreso_producto->descripcion);
            DB::commit();
            return response()->JSON([
                'sw' => true,
                'salida_material' => $salida_material,
                'msj' => 'El registro se actualizó de forma correcta'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->JSON([
                'sw' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, SalidaMaterial $salida_material)
    {
        $request->validate($this->validacion, $this->mensajes);
        if ($salida_material->estado == 'TERMINADO') {
            throw new Exception("No es posible actualizar el registro debido a que su estado es TERMINADO");
        }
        if (!isset($request->salida_detalles) || !$request->salida_detalles || count($request->salida_detalles) <= 0) {
            throw new Exception("Debes ingresar al menos un material");
        }
        $errors = self::validarArray($request["salida_detalles"]);
        if (count($errors) > 0) {
            return response()->JSON([
                "errors" => $errors
            ], 422);
        }
        unset($request["estado"]);
        DB::beginTransaction();
        try {
            $datos_original = HistorialAccion::getDetalleRegistro($salida_material, "salida_materials");
            $salida_material->update(array_map('mb_strtoupper', $request->except("salida_detalles", "eliminados", "producto")));

            $datos_nuevo = HistorialAccion::getDetalleRegistro($salida_material, "salida_materials");
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'MODIFICACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' MODIFICÓ UNA SALIDA DE MATERIAL',
                'datos_original' => $datos_original,
                'datos_nuevo' => $datos_nuevo,
                'modulo' => 'SALIDA DE MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);

            // eliminados
            $eliminados = $request->eliminados;
            foreach ($eliminados as $value) {
                $salida_detalle = SalidaDetalle::findOrFail($value["id"]);
                $salida_detalle->delete();
            }
            // registrar detalles
            $salida_detalles = $request->salida_detalles;
            foreach ($salida_detalles as $value) {
                if ($value["id"] != 0) {
                    $salida_detalle = SalidaDetalle::findOrFail($value["id"]);

                    // renovar cantidad
                    MovimientoMaterial::registroIngreso("INGRESO", $salida_detalle->id, $salida_detalle->material, $salida_detalle->cantidad, "INGRESO POR ACTUALIZACIÓN DE LA SALIDA CON CÓDIGO: " . $salida_material->codigo);

                    $salida_detalle->update([
                        "material_id" => $value["material_id"],
                        "cantidad" => $value["cantidad"],
                        "descripcion" => mb_strtoupper($value["descripcion"]),
                    ]);

                    // registrar movimiento
                    MovimientoMaterial::registroEgreso("SALIDA", $salida_detalle->id, $salida_detalle->material, $salida_detalle->cantidad, $salida_detalle->descripcion);
                } else {
                    $salida_material->salida_detalles()->create([
                        "material_id" => $value["material_id"],
                        "cantidad" => $value["cantidad"],
                        "descripcion" => mb_strtoupper($value["descripcion"]),
                    ]);
                    // registrar movimiento
                    MovimientoMaterial::registroEgreso("SALIDA", $salida_detalle->id, $salida_detalle->material, $salida_detalle->cantidad, $salida_detalle->descripcion);
                }
            }

            DB::commit();
            return response()->JSON([
                'sw' => true,
                'salida_material' => $salida_material,
                'msj' => 'El registro se actualizó de forma correcta'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->JSON([
                'sw' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(SalidaMaterial $salida_material)
    {
        return response()->JSON([
            'sw' => true,
            'salida_material' => $salida_material->load(["producto", "salida_detalles.material"])
        ], 200);
    }

    public function destroy(SalidaMaterial $salida_material)
    {
        DB::beginTransaction();
        try {
            if ($salida_material->estado == 'TERMINADO') {
                throw new Exception("No es posible eliminar el registro debido a que su estado es TERMINADO");
            }

            foreach ($salida_material->salida_detalles as $value) {
                // renovar cantidad
                MovimientoMaterial::registroIngreso("INGRESO", $value->id, $value->material, $value->cantidad, "INGRESO POR ELIMINACIÓN DE SALIDA CON CÓDIGO: " . $salida_material->codigo);
            }

            $datos_original = HistorialAccion::getDetalleRegistro($salida_material, "salida_materials");
            $salida_material->delete();
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'ELIMINACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' ELIMINÓ UNA SALIDA DE MATERIAL',
                'datos_original' => $datos_original,
                'modulo' => 'SALIDA DE MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);
            DB::commit();
            return response()->JSON([
                'sw' => true,
                'msj' => 'El registro se eliminó correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->JSON([
                'sw' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function validarArray($array)
    {
        $errors = [];
        foreach ($array as $key => $value) {
            if (trim($value["material_id"]) == '' || !$value["material_id"]) {
                $errors["material_id_" . $key] = ["Debes seleccionar un material"];
            }
            if (trim($value["cantidad"]) == '' || !$value["cantidad"]) {
                $errors["cantidad_" . $key] = ["Debes ingresar la cantidad"];
            } else {
                // verificar stock
                if (trim($value["material_id"]) != '' && $value["material_id"]) {
                    $material = Material::findOrFail($value["material_id"]);
                    if ($material->stock < (float)$value["cantidad"]) {
                        $errors["material_id_" . $key] = ["Stock insuficiente, actual: " . $material->stock];
                        $errors["cantidad_" . $key] = ["La cantidad supera al stock disponible de " . $material->stock];
                    }
                }
            }
        }
        return $errors;
    }
}
