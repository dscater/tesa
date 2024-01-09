<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\FechaStock;
use App\Models\FechaStockMaterial;
use App\Models\IngresoMaterial;
use App\Models\IngresoProducto;
use App\Models\KardexProducto;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SalidaMaterial;
use DateTimeImmutable;
use Illuminate\Http\Request;

class AnalisisBiController extends Controller
{
    public function p_fabricacion1(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $filtro = $request->filtro;

        $salida_materials = [];
        $terminados = [];
        $produccion = [];
        $p_terminados = 0;
        $p_produccion = 0;

        if ($fecha_ini && $fecha_fin && $fecha_ini != "" && $fecha_fin != "") {
            $salida_materials = SalidaMaterial::whereBetween("fecha_salida", [$fecha_ini, $fecha_fin])->get();
            $terminados = SalidaMaterial::whereBetween("fecha_salida", [$fecha_ini, $fecha_fin])->where("estado", "TERMINADO")->get();
            $produccion = SalidaMaterial::whereBetween("fecha_salida", [$fecha_ini, $fecha_fin])->where("estado", "EN PRODUCCIÓN")->get();
        } else {
            $salida_materials = SalidaMaterial::all();
            $terminados = SalidaMaterial::where("estado", "TERMINADO")->get();
            $produccion = SalidaMaterial::where("estado", "EN PRODUCCIÓN")->get();
        }

        if (count($salida_materials) > 0) {
            $p_terminados = ((float)count($terminados) * 100) / (float)count($salida_materials);
            $p_terminados = number_format($p_terminados, 2, ".", "");
            $p_produccion = ((float)count($produccion) * 100) / (float)count($salida_materials);
            $p_produccion = number_format($p_produccion, 2, ".", "");
        }

        $datos[] = [
            "name" => "TERMINADOS",
            "y" => (float)$p_terminados,
            "dataLabels" => [
                "distance" => 30 // Individual distance (in px)
            ]
        ];
        $datos[] = [
            "name" => "EN PRODUCCIÓN",
            "y" => (float)$p_produccion,
            "dataLabels" => [
                "distance" => 30 // Individual distance (in px)
            ]
        ];
        // $datos[] = ["TERMINADOS", (float)$p_terminados];
        // $datos[] = ["EN PRODUCCIÓN", (float)$p_produccion];
        return response()->JSON([
            "datos" => $datos
        ]);
    }

    public function stock_productos1()
    {
        $productos = Producto::all();
        $datos = [];
        foreach ($productos as $producto) {
            $datos[] = [$producto->nombre, (float)$producto->stock_actual];
        }

        return response()->JSON(["datos" => $datos]);
    }
    public function stock_productos2(Request $request)
    {
        $fecha_ini = date("Y-m-d", strtotime($request->fecha_ini));
        $fecha_fin = date("Y-m-d", strtotime($request->fecha_fin));
        $filtro = $request->filtro;
        $producto = $request->producto;

        $productos = Producto::all();
        if ($filtro != 'TODOS' && $producto && $producto != "TODOS") {
            $productos = Producto::where("id", $producto)->get();
        }

        $datos = [];
        if ($fecha_ini && $fecha_fin) {
            foreach ($productos as $o_prod) {
                $aux_ini = $fecha_ini;
                $data = [];
                while ($aux_ini <= $fecha_fin) {
                    // si no existe registros con la fecha inicial
                    // verificar si existen registros en el kardex y obtener el ultimo registro
                    $fecha_stock = FechaStock::where("fecha", $aux_ini)->where("producto_id", $o_prod->id)->get()->first();
                    $stock = 0;
                    if ($fecha_stock) {
                        $stock = $fecha_stock->stock;
                    } else {
                        $ultimo_registro_kardex = KardexProducto::where("producto_id", $o_prod->id)
                            ->where("fecha", "<", $aux_ini)
                            ->get()->last();
                        if ($ultimo_registro_kardex) {
                            $stock = $ultimo_registro_kardex->cantidad_saldo;
                        }
                    }
                    // $data[] = [date("d-m-y",strtotime($aux_ini)), (float)$stock];
                    $data[] = (float)$stock;
                    $aux_ini = date("Y-m-d", strtotime($aux_ini . "+1 days"));
                }
                $datos[] = [
                    "data" => $data,
                    "name" => $o_prod->nombre
                ];
            }
        }
        return response()->JSON([
            "datos" => $datos,
            "inicio" => $fecha_ini
        ]);
    }
    public function stock_productos3(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $producto = $request->producto;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        // verificar si existen registros en ese anio y mes en el kardex
        // si no existe asignar el stock de uno de los que si encuentra si no encuentra mostrar 0

        $productos = Producto::all();
        if ($filtro != "TODOS" && $producto != "TODOS") {
            $productos = Producto::where("id", $producto)->get();
        }

        $datos = [];
        foreach ($productos as $o_prod) {
            $promedio = 0;
            $kardex1 = KardexProducto::where("producto_id", $o_prod->id)->where("fecha", "LIKE", "$mes_anterior1%")->get()->last();
            if ($kardex1) {
                $promedio += $kardex1->cantidad_saldo;
            }
            $kardex2 = KardexProducto::where("producto_id", $o_prod->id)->where("fecha", "LIKE", "$mes_anterior2%")->get()->last();
            if ($kardex2) {
                $promedio += $kardex2->cantidad_saldo;
            }
            $kardex3 = KardexProducto::where("producto_id", $o_prod->id)->where("fecha", "LIKE", "$mes_anterior3%")->get()->last();
            if ($kardex3) {
                $promedio += $kardex3->cantidad_saldo;
            }
            $promedio = $promedio / 3;
            $datos[] = [$o_prod->nombre, (int)$promedio];
        }

        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos
        ]);
    }

    public function stock_materials1()
    {
        $materials = Material::all();
        $datos = [];
        foreach ($materials as $material) {
            $datos[] = [$material->nombre, (float)$material->stock];
        }

        return response()->JSON(["datos" => $datos]);
    }
    public function stock_materials2(Request $request)
    {
        $fecha_ini = date("Y-m-d", strtotime($request->fecha_ini));
        $fecha_fin = date("Y-m-d", strtotime($request->fecha_fin));
        $filtro = $request->filtro;
        $material = $request->material;

        $materials = Material::all();
        if ($filtro != 'TODOS' && $material && $material != "TODOS") {
            $materials = Material::where("id", $material)->get();
        }

        $datos = [];
        if ($fecha_ini && $fecha_fin) {
            foreach ($materials as $o_mat) {
                $aux_ini = $fecha_ini;
                $data = [];
                while ($aux_ini <= $fecha_fin) {
                    // si no existe registros con la fecha inicial
                    // verificar si existen registros en el kardex y obtener el ultimo registro
                    $fecha_stock = FechaStockMaterial::where("fecha", $aux_ini)->where("material_id", $o_mat->id)->get()->first();
                    $stock = 0;
                    if ($fecha_stock) {
                        $stock = $fecha_stock->stock;
                    } else {
                        $ultimo_registro_kardex = MovimientoMaterial::where("material_id", $o_mat->id)
                            ->where("fecha", "<", $aux_ini)
                            ->get()->last();
                        if ($ultimo_registro_kardex) {
                            $stock = $ultimo_registro_kardex->cantidad_saldo;
                        }
                    }
                    // $data[] = [date("d-m-y",strtotime($aux_ini)), (float)$stock];
                    $data[] = (float)$stock;
                    $aux_ini = date("Y-m-d", strtotime($aux_ini . "+1 days"));
                }
                $datos[] = [
                    "data" => $data,
                    "name" => $o_mat->nombre
                ];
            }
        }
        return response()->JSON([
            "datos" => $datos,
            "inicio" => $fecha_ini
        ]);
    }
    public function stock_materials3(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $material = $request->material;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        // verificar si existen registros en ese anio y mes en el kardex
        // si no existe asignar el stock de uno de los que si encuentra si no encuentra mostrar 0

        $materials = Material::all();
        if ($filtro != "TODOS" && $material != "TODOS") {
            $materials = Material::where("id", $material)->get();
        }

        $datos = [];
        foreach ($materials as $o_mat) {
            $promedio = 0;
            $kardex1 = MovimientoMaterial::where("material_id", $o_mat->id)->where("fecha", "LIKE", "$mes_anterior1%")->get()->last();
            if ($kardex1) {
                $promedio += $kardex1->cantidad_saldo;
            }
            $kardex2 = MovimientoMaterial::where("material_id", $o_mat->id)->where("fecha", "LIKE", "$mes_anterior2%")->get()->last();
            if ($kardex2) {
                $promedio += $kardex2->cantidad_saldo;
            }
            $kardex3 = MovimientoMaterial::where("material_id", $o_mat->id)->where("fecha", "LIKE", "$mes_anterior3%")->get()->last();
            if ($kardex3) {
                $promedio += $kardex3->cantidad_saldo;
            }
            $promedio = $promedio / 3;
            $datos[] = [$o_mat->nombre, (int)$promedio];
        }

        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos
        ]);
    }

    public function proveedors1(Request $request)
    {
        $proveedors = Proveedor::all();
        $datos = [];
        foreach ($proveedors as $o_prov) {
            $total = IngresoMaterial::where("proveedor_id", $o_prov->id)->sum("cantidad");
            $datos[] = [$o_prov->razon_social, (float)$total];
        }

        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function proveedors2(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $filtro = $request->filtro;
        $proveedor = $request->proveedor;

        $proveedors = Proveedor::all();
        if ($filtro != "TODOS" && $proveedor != "TODOS") {
            $proveedors = Proveedor::where("id", $proveedor)->get();
        }
        foreach ($proveedors as $o_prov) {
            $total = IngresoMaterial::where("proveedor_id", $o_prov->id)
                ->whereBetween("fecha_registro", [$fecha_ini, $fecha_fin])->sum("cantidad");
            $datos[] = [$o_prov->razon_social, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function proveedors3(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $proveedor = $request->proveedor;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        $proveedors = Proveedor::all();
        if ($filtro != "TODOS" && $proveedor != "TODOS") {
            $proveedors = Proveedor::where("id", $proveedor)->get();
        }
        foreach ($proveedors as $o_prov) {
            $promedio = 0;
            $total1 = IngresoMaterial::where("proveedor_id", $o_prov->id)
                ->where("fecha_registro", "LIKE", "$mes_anterior1%")->sum("cantidad");

            $total2 = IngresoMaterial::where("proveedor_id", $o_prov->id)
                ->where("fecha_registro", "LIKE", "$mes_anterior2%")->sum("cantidad");

            $total3 = IngresoMaterial::where("proveedor_id", $o_prov->id)
                ->where("fecha_registro", "LIKE", "$mes_anterior3%")->sum("cantidad");
            $promedio = ($total1 + $total2 + $total3) / 3;
            $datos[] = [$o_prov->razon_social, (float)$promedio];
        }
        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos
        ]);
    }

    public function ventas1(Request $request)
    {
        $productos = Producto::all();
        $datos = [];
        foreach ($productos as $o_prod) {
            $total = DetalleVenta::where("producto_id", $o_prod->id)->sum("cantidad");
            $datos[] = [$o_prod->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function ventas2(Request $request)
    {
        $productos = Producto::all();
        $datos = [];
        foreach ($productos as $o_prod) {
            $total = DetalleVenta::where("producto_id", $o_prod->id)->sum("subtotal");
            $datos[] = [$o_prod->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function ventas3(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $filtro = $request->filtro;
        $producto = $request->producto;

        $productos = Producto::all();
        if ($filtro != "TODOS" && $producto != "TODOS") {
            $productos = Producto::where("id", $producto)->get();
        }
        $datos = [];
        foreach ($productos as $o_prod) {
            $total = DetalleVenta::where("producto_id", $o_prod->id)->whereBetween("created_at", [$fecha_ini, $fecha_fin])->sum("cantidad");
            $datos[] = [$o_prod->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos,
            "xd" => ""
        ]);
    }
    public function ventas4(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $filtro = $request->filtro;
        $producto = $request->producto;

        $productos = Producto::all();
        if ($filtro != "TODOS" && $producto != "TODOS") {
            $productos = Producto::where("id", $producto)->get();
        }
        $datos = [];
        foreach ($productos as $o_prod) {
            $total = DetalleVenta::where("producto_id", $o_prod->id)->whereBetween("created_at", [$fecha_ini, $fecha_fin])->sum("subtotal");
            $datos[] = [$o_prod->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function ventas5(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $producto = $request->producto;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        // verificar si existen registros en ese anio y mes en el kardex
        // si no existe asignar el stock de uno de los que si encuentra si no encuentra mostrar 0

        $productos = Producto::all();
        if ($filtro != "TODOS" && $producto != "TODOS") {
            $productos = Producto::where("id", $producto)->get();
        }

        $datos = [];
        foreach ($productos as $o_prod) {
            $promedio = 0;
            $total1 = DetalleVenta::where("producto_id", $o_prod->id)->where("created_at", "LIKE", "$mes_anterior1%")->sum("cantidad");
            if ($total1) {
                $promedio += $total1;
            }
            $total2 = DetalleVenta::where("producto_id", $o_prod->id)->where("created_at", "LIKE", "$mes_anterior2%")->sum("cantidad");
            if ($total2) {
                $promedio += $total2;
            }
            $total3 = DetalleVenta::where("producto_id", $o_prod->id)->where("created_at", "LIKE", "$mes_anterior3%")->sum("cantidad");
            if ($total3) {
                $promedio += $total3;
            }
            $promedio = $promedio / 3;
            $datos[] = [$o_prod->nombre, (int)$promedio];
        }

        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos
        ]);
    }
    public function ventas6(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $producto = $request->producto;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        // verificar si existen registros en ese anio y mes en el kardex
        // si no existe asignar el stock de uno de los que si encuentra si no encuentra mostrar 0

        $productos = Producto::all();
        if ($filtro != "TODOS" && $producto != "TODOS") {
            $productos = Producto::where("id", $producto)->get();
        }

        $datos = [];
        foreach ($productos as $o_prod) {
            $promedio = 0;
            $total1 = DetalleVenta::where("producto_id", $o_prod->id)->where("created_at", "LIKE", "$mes_anterior1%")->sum("subtotal");
            if ($total1) {
                $promedio += $total1;
            }
            $total2 = DetalleVenta::where("producto_id", $o_prod->id)->where("created_at", "LIKE", "$mes_anterior2%")->sum("subtotal");
            if ($total2) {
                $promedio += $total2;
            }
            $total3 = DetalleVenta::where("producto_id", $o_prod->id)->where("created_at", "LIKE", "$mes_anterior3%")->sum("subtotal");
            if ($total3) {
                $promedio += $total3;
            }
            $promedio = $promedio / 3;
            $datos[] = [$o_prod->nombre, (int)$promedio];
        }

        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos
        ]);
    }

    public function clientes1(Request $request)
    {
        $datos = [];

        $clientes = Cliente::all();
        foreach ($clientes as $o_cli) {
            $total = DetalleVenta::select("detalle_ventas.cantidad")
                ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                ->where("cliente_id", $o_cli->id)
                ->sum("detalle_ventas.cantidad");
            $datos[] = [$o_cli->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function clientes2(Request $request)
    {
        $datos = [];

        $clientes = Cliente::all();
        foreach ($clientes as $o_cli) {
            $total = DetalleVenta::select("detalle_ventas.subtotal")
                ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                ->where("cliente_id", $o_cli->id)
                ->sum("detalle_ventas.subtotal");
            $datos[] = [$o_cli->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function clientes3(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $filtro = $request->filtro;
        $producto = $request->producto;

        $datos = [];
        $clientes = Cliente::all();
        foreach ($clientes as $o_cli) {
            $total = DetalleVenta::select("detalle_ventas.cantidad")
                ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                ->where("cliente_id", $o_cli->id)
                ->whereBetween("ventas.fecha_registro", [$fecha_ini, $fecha_fin])
                ->sum("detalle_ventas.cantidad");

            if ($filtro != "TODOS" && $producto != "TODOS") {
                $total = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->whereBetween("ventas.fecha_registro", [$fecha_ini, $fecha_fin])
                    ->sum("detalle_ventas.cantidad");
            }
            $datos[] = [$o_cli->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function clientes4(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $filtro = $request->filtro;
        $producto = $request->producto;

        $datos = [];
        $clientes = Cliente::all();
        foreach ($clientes as $o_cli) {
            $total = DetalleVenta::select("detalle_ventas.subtotal")
                ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                ->where("cliente_id", $o_cli->id)
                ->whereBetween("ventas.fecha_registro", [$fecha_ini, $fecha_fin])
                ->sum("detalle_ventas.subtotal");

            if ($filtro != "TODOS" && $producto != "TODOS") {
                $total = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->whereBetween("ventas.fecha_registro", [$fecha_ini, $fecha_fin])
                    ->sum("detalle_ventas.subtotal");
            }
            $datos[] = [$o_cli->nombre, (float)$total];
        }
        return response()->JSON([
            "datos" => $datos
        ]);
    }
    public function clientes5(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $producto = $request->producto;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        $datos = [];
        $clientes = Cliente::all();
        foreach ($clientes as $o_cli) {
            $promedio = 0;
            if ($filtro != "TODOS" && $producto != "TODOS") {
                $total1 = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior1%")
                    ->sum("detalle_ventas.cantidad");
                $total2 = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior2%")
                    ->sum("detalle_ventas.cantidad");
                $total3 = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior3%")
                    ->sum("detalle_ventas.cantidad");
            } else {
                $total1 = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior1%")
                    ->sum("detalle_ventas.cantidad");

                $total2 = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior2%")
                    ->sum("detalle_ventas.cantidad");

                $total3 = DetalleVenta::select("detalle_ventas.cantidad")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior3%")
                    ->sum("detalle_ventas.cantidad");
            }

            $promedio = ($total1 + $total2 + $total3) / 3;

            $datos[] = [$o_cli->nombre, (float)$promedio];
        }
        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos,
        ]);
    }
    public function clientes6(Request $request)
    {
        $anio = $request->anio;
        $mes = $request->mes;
        $filtro = $request->filtro;
        $producto = $request->producto;
        $meses = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

        $fecha_armada = $anio . '-' . $mes . '-01';
        $mes_anterior1 = date("Y-m", strtotime($fecha_armada . '-1 month'));
        $mes_anterior2 = date("Y-m", strtotime($fecha_armada . '-2 month'));
        $mes_anterior3 = date("Y-m", strtotime($fecha_armada . '-3 month'));

        $datos = [];
        $clientes = Cliente::all();
        foreach ($clientes as $o_cli) {
            $promedio = 0;
            if ($filtro != "TODOS" && $producto != "TODOS") {
                $total1 = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior1%")
                    ->sum("detalle_ventas.subtotal");
                $total2 = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior2%")
                    ->sum("detalle_ventas.subtotal");
                $total3 = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("producto_id", $producto)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior3%")
                    ->sum("detalle_ventas.subtotal");
            } else {
                $total1 = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior1%")
                    ->sum("detalle_ventas.subtotal");

                $total2 = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior2%")
                    ->sum("detalle_ventas.subtotal");

                $total3 = DetalleVenta::select("detalle_ventas.subtotal")
                    ->join("ventas", "ventas.id", "=", "detalle_ventas.venta_id")
                    ->where("cliente_id", $o_cli->id)
                    ->where("ventas.fecha_registro", "LIKE", "$mes_anterior3%")
                    ->sum("detalle_ventas.subtotal");
            }

            $promedio = ($total1 + $total2 + $total3) / 3;

            $datos[] = [$o_cli->nombre, (float)$promedio];
        }
        return response()->JSON([
            "mes_anio" => mb_strtoupper($meses[$mes]) . " DE " . $anio,
            "datos" => $datos,
        ]);
    }
}
