<?php

use App\Http\Controllers\AnalisisBiController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\HistorialAccionController;
use App\Http\Controllers\IngresoMaterialController;
use App\Http\Controllers\IngresoProductoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SalidaMaterialController;
use App\Http\Controllers\SalidaProductoController;
use App\Http\Controllers\TipoIngresoController;
use App\Http\Controllers\TipoSalidaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// VACIAR CACHE
Route::get('/cache_clear', function () {
    Artisan::call("route:clear");
    Artisan::call("route:cache");
    Artisan::call("view:clear");
    Artisan::call("config:cache");
    Artisan::call("optimize");

    return 'Cache borrada correctamente<br/><a href="' . url("/") . '">Volver al inicio<a>';
});

// LOGIN
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);

// CONFIGURACIÓN
Route::get('/configuracion/getConfiguracion', [ConfiguracionController::class, 'getConfiguracion']);

Route::middleware(['auth'])->group(function () {
    Route::post('/configuracion/update', [ConfiguracionController::class, 'update']);

    Route::prefix('admin')->group(function () {
        // Usuarios
        Route::get('usuarios/getUsuario/{usuario}', [UserController::class, 'getUsuario']);
        Route::get('usuarios/userActual', [UserController::class, 'userActual']);
        Route::get('usuarios/getInfoBox', [UserController::class, 'getInfoBox']);
        Route::get('usuarios/getPermisos/{usuario}', [UserController::class, 'getPermisos']);
        Route::get('usuarios/getEncargadosRepresentantes', [UserController::class, 'getEncargadosRepresentantes']);
        Route::post('usuarios/actualizaContrasenia/{usuario}', [UserController::class, 'actualizaContrasenia']);
        Route::post('usuarios/actualizaFoto/{usuario}', [UserController::class, 'actualizaFoto']);
        Route::resource('usuarios', UserController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Proveedores
        Route::resource('proveedors', ProveedorController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Productos
        Route::post("productos/excel", [ProductoController::class, 'excel']);
        Route::get("productos/valida_stock", [ProductoController::class, 'valida_stock']);
        Route::get("productos/buscar_producto", [ProductoController::class, 'buscar_producto']);
        Route::resource('productos', ProductoController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Categorias
        Route::resource('categorias', CategoriaController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Tipo Ingresos
        Route::resource('tipo_ingresos', TipoIngresoController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Ingreso productos
        Route::resource('ingreso_productos', IngresoProductoController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Tipo Salidas
        Route::resource('tipo_salidas', TipoSalidaController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Salida productos
        Route::resource('salida_productos', SalidaProductoController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Clientes
        Route::resource('clientes', ClienteController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Ventas
        Route::post("ventas/pdf/{venta}", [VentaController::class, 'pdf']);
        Route::get("ventas/info/getLiteral", [VentaController::class, 'getLiteral']);
        Route::get("ventas/info/devolucions", [VentaController::class, 'getDevolucions']);
        Route::resource('ventas', VentaController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // historial acciones
        Route::resource('historial_accions', HistorialAccionController::class)->only([
            'index', 'show'
        ]);

        // materials
        Route::get("materials/valida_stock", [MaterialController::class, 'valida_stock']);
        Route::get("materials/buscar_material", [MaterialController::class, 'buscar_material']);
        Route::resource('materials', MaterialController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // ingreso_materials
        Route::resource('ingreso_materials', IngresoMaterialController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // salida_materials
        Route::put("salida_materials/terminado/{salida_material}", [SalidaMaterialController::class, 'terminado']);
        Route::get("salida_materials/paginado", [SalidaMaterialController::class, 'paginado']);
        Route::resource('salida_materials', SalidaMaterialController::class)->only([
            'index', 'store', 'update', 'destroy', 'show'
        ]);

        // Analisis BI
        Route::get("analisis_bi/p_fabricacion1", [AnalisisBiController::class, 'p_fabricacion1']);

        Route::get("analisis_bi/stock_productos1", [AnalisisBiController::class, 'stock_productos1']);
        Route::get("analisis_bi/stock_productos2", [AnalisisBiController::class, 'stock_productos2']);
        Route::get("analisis_bi/stock_productos3", [AnalisisBiController::class, 'stock_productos3']);

        Route::get("analisis_bi/stock_materials1", [AnalisisBiController::class, 'stock_materials1']);
        Route::get("analisis_bi/stock_materials2", [AnalisisBiController::class, 'stock_materials2']);
        Route::get("analisis_bi/stock_materials3", [AnalisisBiController::class, 'stock_materials3']);

        Route::get("analisis_bi/proveedors1", [AnalisisBiController::class, 'proveedors1']);
        Route::get("analisis_bi/proveedors2", [AnalisisBiController::class, 'proveedors2']);
        Route::get("analisis_bi/proveedors3", [AnalisisBiController::class, 'proveedors3']);

        Route::get("analisis_bi/ventas1", [AnalisisBiController::class, 'ventas1']);
        Route::get("analisis_bi/ventas2", [AnalisisBiController::class, 'ventas2']);
        Route::get("analisis_bi/ventas3", [AnalisisBiController::class, 'ventas3']);
        Route::get("analisis_bi/ventas4", [AnalisisBiController::class, 'ventas4']);
        Route::get("analisis_bi/ventas5", [AnalisisBiController::class, 'ventas5']);
        Route::get("analisis_bi/ventas6", [AnalisisBiController::class, 'ventas6']);

        Route::get("analisis_bi/clientes1", [AnalisisBiController::class, 'clientes1']);
        Route::get("analisis_bi/clientes2", [AnalisisBiController::class, 'clientes2']);
        Route::get("analisis_bi/clientes3", [AnalisisBiController::class, 'clientes3']);
        Route::get("analisis_bi/clientes4", [AnalisisBiController::class, 'clientes4']);
        Route::get("analisis_bi/clientes5", [AnalisisBiController::class, 'clientes5']);
        Route::get("analisis_bi/clientes6", [AnalisisBiController::class, 'clientes6']);

        // REPORTES
        Route::post('reportes/usuarios', [ReporteController::class, 'usuarios']);
        Route::post('reportes/kardex', [ReporteController::class, 'kardex']);
        Route::post('reportes/kardex_materials', [ReporteController::class, 'kardex_materials']);
        Route::post('reportes/ventas', [ReporteController::class, 'ventas']);
        Route::post('reportes/stock_productos', [ReporteController::class, 'stock_productos']);
        Route::post('reportes/stock_materials', [ReporteController::class, 'stock_materials']);
        Route::post('reportes/historial_accion', [ReporteController::class, 'historial_accion']);
        Route::post('reportes/grafico_ingresos', [ReporteController::class, 'grafico_ingresos']);
        Route::post('reportes/grafico_orden', [ReporteController::class, 'grafico_orden']);
    });
});


// ---------------------------------------
Route::get('/{optional?}', function () {
    return view('app');
})->name('base_path')->where('optional', '.*');
