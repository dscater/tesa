<?php

namespace App\Http\Controllers;

use App\Models\HistorialAccion;
use App\Models\IngresoMaterial;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IngresoMaterialController extends Controller
{
    public $validacion = [
        'material_id' => 'required',
        'proveedor_id' => 'required',
        'fecha_ingreso' => 'required',
        'cantidad' => 'required|numeric',
        'peso' => 'required|numeric',
        'precio' => 'required|numeric',
        'tipo_ingreso_id' => 'required',
    ];

    public $mensajes = [
        "fecha_ingreso.required" => "Debes ingresar una fecha",
        "peso.required" => "Debes completar este campo",
        "peso.numeric" => "Debes ingresar un valor númerico",
        "precio.required" => "Debes completar este campo",
        "precio.numeric" => "Debes ingresar un valor númerico",
    ];

    public function index(Request $request)
    {
        $ingreso_materials = IngresoMaterial::with(["material", "proveedor", "tipo_ingreso"])->orderBy("id", "desc")->get();
        return response()->JSON(['ingreso_materials' => $ingreso_materials, 'total' => count($ingreso_materials)], 200);
    }

    public function store(Request $request)
    {
        $request->validate($this->validacion, $this->mensajes);

        DB::beginTransaction();
        try {
            // crear IngresoMaterial
            $request["fecha_registro"] = date("Y-m-d");
            $nuevo_ingreso_material = IngresoMaterial::create(array_map('mb_strtoupper', $request->all()));

            // registrar movimiento
            MovimientoMaterial::registroIngreso("INGRESO", $nuevo_ingreso_material->id, $nuevo_ingreso_material->material, $nuevo_ingreso_material->cantidad, $nuevo_ingreso_material->descripcion);

            $datos_original = HistorialAccion::getDetalleRegistro($nuevo_ingreso_material, "ingreso_materials");
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'CREACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' REGISTRO UN INGRESO DE MATERIAL',
                'datos_original' => $datos_original,
                'modulo' => 'INGRESO DE MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);

            DB::commit();
            return response()->JSON([
                'sw' => true,
                'ingreso_material' => $nuevo_ingreso_material,
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

    public function update(Request $request, IngresoMaterial $ingreso_material)
    {
        $request->validate($this->validacion, $this->mensajes);
        if ($request->material_id != $ingreso_material->material_id) {
            return response()->JSON([
                'sw' => false,
                'message' => "Error, los datos enviados son incorrectos",
            ], 500);
        } else {
            DB::beginTransaction();
            try {
                // descontar el stock
                Material::decrementarStock($ingreso_material->material, $ingreso_material->cantidad);

                $datos_original = HistorialAccion::getDetalleRegistro($ingreso_material, "ingreso_materials");
                $ingreso_material->update(array_map('mb_strtoupper', $request->all()));

                // INCREMENTAR STOCK
                Material::incrementarStock($ingreso_material->material, $ingreso_material->cantidad);

                // actualizar movimiento
                $movimiento = MovimientoMaterial::where("material_id", $ingreso_material->material_id)
                    ->where("tipo_registro", "INGRESO")
                    ->where("registro_id", $ingreso_material->id)
                    ->get()->first();
                MovimientoMaterial::actualizaRegistrosKardex($movimiento->id, $movimiento->material_id);

                $datos_nuevo = HistorialAccion::getDetalleRegistro($ingreso_material, "ingreso_materials");
                HistorialAccion::create([
                    'user_id' => Auth::user()->id,
                    'accion' => 'MODIFICACIÓN',
                    'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' MODIFICÓ UN INGRESO DE MATERIAL',
                    'datos_original' => $datos_original,
                    'datos_nuevo' => $datos_nuevo,
                    'modulo' => 'INGRESO DE MATERIALES',
                    'fecha' => date('Y-m-d'),
                    'hora' => date('H:i:s')
                ]);

                DB::commit();

                return response()->JSON([
                    'sw' => true,
                    'ingreso_material' => $ingreso_material,
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
    }

    public function show(IngresoMaterial $ingreso_material)
    {
        return response()->JSON([
            'sw' => true,
            'ingreso_material' => $ingreso_material
        ], 200);
    }

    public function destroy(IngresoMaterial $ingreso_material)
    {
        DB::beginTransaction();
        try {
            $eliminar_movimiento = MovimientoMaterial::where("tipo_registro", "INGRESO")
                ->where("registro_id", $ingreso_material->id)
                ->where("material_id", $ingreso_material->material_id)
                ->get()
                ->first();
            $id_movimiento = $eliminar_movimiento->id;
            $id_material = $eliminar_movimiento->material_id;
            $eliminar_movimiento->delete();

            $anterior = MovimientoMaterial::where("material_id", $id_material)
                ->where("id", "<", $id_movimiento)
                ->get()
                ->last();
            $actualiza_desde = null;
            if ($anterior) {
                $actualiza_desde = $anterior;
            } else {
                // comprobar si existen registros posteriorres al actualizado
                $siguiente = MovimientoMaterial::where("material_id", $id_material)
                    ->where("id", ">", $id_movimiento)
                    ->get()->first();
                if ($siguiente)
                    $actualiza_desde = $siguiente;
            }

            if ($actualiza_desde) {
                // actualizar a partir de este registro los sgtes. registros
                MovimientoMaterial::actualizaRegistrosKardex($actualiza_desde->id, $actualiza_desde->material_id);
            }

            // descontar el stock
            Material::decrementarStock($ingreso_material->material, $ingreso_material->cantidad);
            $datos_original = HistorialAccion::getDetalleRegistro($ingreso_material, "ingreso_materials");
            $ingreso_material->delete();

            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'ELIMINACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' ELIMINÓ UN INGRESO DE MATERIAL',
                'datos_original' => $datos_original,
                'modulo' => 'INGRESO DE MATERIALES',
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
}
