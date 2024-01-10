<?php

namespace App\Http\Controllers;

use App\Models\HistorialAccion;
use App\Models\IngresoMaterial;
use App\Models\Material;
use App\Models\SalidaDetalle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public $validacion = [
        'nombre' => 'required|min:1',
        'color' => 'required|min:1',
    ];

    public $mensajes = [
        "nombre.required" => "Debes ingresar un nombre",
        "nombre.min" => "El nombre debe tener al menos :min caracter",
        "color.required" => "Debes ingresar un color",
        "color.min" => "El color debe tener al menos :min caracter",
    ];

    public function index(Request $request)
    {
        $materials = Material::orderBy("id", "DESC")
            ->get();
        return response()->JSON(['materials' => $materials, 'total' => count($materials)], 200);
    }
    public function buscar_material(Request $request)
    {
        $value = $request->value;
        $sw_busqueda = $request->sw_busqueda;

        $materials = [];
        if ($sw_busqueda == 'todos') {
            $materials = Material::where("nombre", "LIKE", "%$value%")
                ->orderBy("nombre", "ASC")
                ->get()->take(100);
        } else {
            $materials = Material::where("materials." . $sw_busqueda, $value)
                ->get()->take(100);
        }

        return response()->JSON($materials);
    }

    public function store(Request $request)
    {
        $request->validate($this->validacion, $this->mensajes);

        DB::beginTransaction();
        try {
            // crear Material
            $request["fecha_registro"] = date("Y-m-d");
            $request["stock"] = 0;
            $nuevo_material = Material::create(array_map('mb_strtoupper', $request->all()));

            if ($request->hasFile('imagen')) {
                $file = $request->imagen;
                $nom_file = time() . '_' . $nuevo_material->id . '.' . $file->getClientOriginalExtension();
                $nuevo_material->imagen = $nom_file;
                $file->move(public_path() . '/imgs/materials/', $nom_file);
            }
            $nuevo_material->save();

            $datos_original = HistorialAccion::getDetalleRegistro($nuevo_material, "materials");
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'CREACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' REGISTRO UN MATERIAL',
                'datos_original' => $datos_original,
                'modulo' => 'MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);

            DB::commit();
            return response()->JSON([
                'sw' => true,
                'material' => $nuevo_material,
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

    public function update(Request $request, Material $material)
    {
        $request->validate($this->validacion, $this->mensajes);

        DB::beginTransaction();
        try {
            $datos_original = HistorialAccion::getDetalleRegistro($material, "materials");
            $material->update(array_map('mb_strtoupper', $request->all()));

            if ($request->hasFile('imagen')) {
                $antiguo = $material->imagen;
                if ($antiguo && trim($antiguo) && $antiguo != 'default.png') {
                    \File::delete(public_path() . "/imgs/materials/" . $antiguo);
                }

                $file = $request->imagen;
                $nom_file = time() . '_' . $material->id . '.' . $file->getClientOriginalExtension();
                $material->imagen = $nom_file;
                $file->move(public_path() . '/imgs/materials/', $nom_file);
            }
            $material->save();

            $datos_nuevo = HistorialAccion::getDetalleRegistro($material, "materials");
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'MODIFICACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' MODIFICÓ UN MATERIAL',
                'datos_original' => $datos_original,
                'datos_nuevo' => $datos_nuevo,
                'modulo' => 'MATERIALES',
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s')
            ]);

            DB::commit();
            return response()->JSON([
                'sw' => true,
                'material' => $material,
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

    public function show(Material $material)
    {
        return response()->JSON([
            'sw' => true,
            'material' => $material,
            "stock" => $material->stock
        ], 200);
    }

    public function destroy(Material $material)
    {
        DB::beginTransaction();
        try {
            // validar que no exista en orden de ventas
            $existe_uso = SalidaDetalle::where("material_id", $material->id)->get();
            if (count($existe_uso) > 0) {
                throw new Exception('No es posible eliminar el registro debido a que esta siendo utilizado');
            }
            $existe_uso = IngresoMaterial::where("material_id", $material->id)->get();
            if (count($existe_uso) > 0) {
                throw new Exception('No es posible eliminar el registro debido a que esta siendo utilizado');
            }

            $datos_original = HistorialAccion::getDetalleRegistro($material, "materials");

            $antiguo = $material->imagen;
            if ($antiguo && trim($antiguo) && $antiguo != 'default.png') {
                \File::delete(public_path() . "/imgs/materials/" . $antiguo);
            }

            $material->delete();
            HistorialAccion::create([
                'user_id' => Auth::user()->id,
                'accion' => 'ELIMINACIÓN',
                'descripcion' => 'EL USUARIO ' . Auth::user()->usuario . ' ELIMINO UN MATERIAL',
                'datos_original' => $datos_original,
                'modulo' => 'MATERIALES',
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

    public function valida_stock(Request $request)
    {
        $cantidad = $request->cantidad;
        $material_id = $request->id;
        $material = Material::findOrFail($material_id);

        if ($material->stock >= $cantidad) {
            return response()->JSON(
                [
                    "sw" => true,
                    "material" => $material,
                ]
            );
        }
        return response()->JSON(
            [
                "sw" => false,
                "msj" => "La cantidad que desea ingresar supera al stock disponible del material.<br/> Stock actual: <b>" . $material->stock . " unidades</b>"
            ]
        );
    }
}
