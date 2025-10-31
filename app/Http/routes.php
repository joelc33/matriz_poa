<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::group(['namespace' => 'Panel'], function () {
    Route::get('inicio', 'panelController@inicio');
    Route::get('inicio/bandeja', 'panelController@bandeja');
});

//*Modulos de Autenticacion*/
Route::group(['namespace' => 'Autenticacion'], function () {
    /*Llamadas al controlador autenticar*/
    Route::group(['prefix' => '/'], function () {
        Route::get('', 'autenticarController@index'); // Mostrar login
        Route::post('autenticar', 'autenticarController@validar'); // Verificar datos
        Route::get('autenticar', 'autenticarController@salir'); // Finalizar sesión
        Route::get('autenticar/captcha', 'externoController@captcha'); // Captcha
        Route::post('autenticar/recuperar', 'autenticarController@recuperar'); // Recuperar Password
        Route::get('ejercicio', 'ejercicioController@lista'); // Ejercicio Fiscal
        Route::post('ejercicio', 'ejercicioController@seleccionar'); // Ejercicio Fiscal
        Route::get('ejercicio/lista', 'ejercicioController@ejercicio'); // Ejercicio Fiscal
        //Route::get('recuperar', 'recuperarController@index'); // Resetear Clave
    });
    //*Modulo de roles*/
    Route::group(['prefix' => 'rol'], function () {
        Route::get('lista', 'rolController@lista');
        Route::post('storeLista', 'rolController@storeLista');
        Route::get('nuevo', 'rolController@nuevo');
        Route::post('guardar', 'rolController@guardar');
        Route::post('privilegio', 'rolController@privilegio');
        Route::post('guardarPrivilegio', 'rolController@guardarPrivilegio');
        Route::post('opcion', 'rolController@opcion');
        Route::post('opcion/storeLista', 'rolController@opcionStoreLista');
        Route::post('opcion/si', 'rolController@opcionSi');
        Route::post('opcion/no', 'rolController@opcionNo');
    });
    //*Modulo de usuarios*/
    Route::group(['prefix' => 'usuario'], function () {
        Route::get('lista', 'usuarioController@lista');
        Route::post('storeLista', 'usuarioController@storeLista');
        Route::get('nuevo', 'usuarioController@nuevo');
        Route::get('editar/{id}', 'usuarioController@editar');
        Route::post('guardar', 'usuarioController@guardar');
        Route::post('guardar/{id}', 'usuarioController@guardar');
        Route::get('contrasena', 'usuarioController@contrasena');
        Route::post('cambioContrasena', 'usuarioController@cambioContrasena');
        Route::get('rol', 'usuarioController@rol');
        Route::get('cargo', 'usuarioController@cargo');
        Route::get('documento', 'usuarioController@documento');
        Route::post('resetear', 'usuarioController@resetear');
        Route::post('deshabilitar', 'usuarioController@deshabilitar');
        Route::get('datos', 'usuarioController@datos');
        Route::post('cambios', 'usuarioController@cambios');
        Route::get('cambiar/clave/{id}', 'usuarioController@cambioClave');
        Route::post('cambiar/clave', 'usuarioController@guardarCambioClave');
    });
    //*Modulo de validar*/
    /*Route::group(['prefix' => 'validar'], function(){
        Route::post('rif', 'documentoController@rif');
        Route::post('rif/completoP', 'documentoController@rifCompletoP');
        Route::post('rif/completoF', 'documentoController@rifCompletoF');
        Route::post('movil', 'documentoController@movil');
        Route::post('fijo', 'documentoController@fijo');
        Route::post('fax', 'documentoController@fax');
        Route::get('tf/licencia/{id}', 'externoController@tfLicencia');
        Route::get('{id}/guia/sa', 'externoController@guiaSal');
    });*/
});
//*Modulos de Tablas Auxiliares*/
Route::group(['namespace' => 'Auxiliar'], function () {
    //*Modulo de roles*/
    Route::group(['prefix' => 'auxiliar'], function () {
        Route::get('documento', 'documentoController@documento');
        Route::get('cargo', 'documentoController@cargo');
        Route::get('rol', 'documentoController@rol');
        Route::get('ejecutor/todo', 'documentoController@ejecutorTodo');
        Route::get('ac/ae', 'documentoController@acAe');
        Route::post('ac/ae/activo', 'documentoController@acAeActivo');
        Route::post('partida/buscar', 'buscarController@partida');
        Route::get('ejecutor/ambito', 'documentoController@ejecutorAmbito');
        Route::get('ejecutor/tipo', 'documentoController@ejecutorTipo');
        Route::get('objetivo/historico', 'documentoController@objetivoHistorico');
        Route::post('objetivo/nacional', 'documentoController@objetivoNacional');
        Route::post('objetivo/estrategico', 'documentoController@objetivoEstrategico');
        Route::post('objetivo/general', 'documentoController@objetivoGeneral');
        Route::get('plan/area', 'documentoController@planArea');
        Route::post('plan/ambito', 'documentoController@planAmbito');
        Route::post('plan/objetivo', 'documentoController@planObjetivo');
        Route::post('plan/macroproblema', 'documentoController@planMacroproblema');
        Route::post('plan/nudo', 'documentoController@planNudo');
        Route::get('ef', 'documentoController@ejercicioFiscal');
        Route::get('fondo/tipo', 'documentoController@fondoTipo');
        Route::get('recurso/tipo', 'documentoController@recursoTipo');
        Route::post('accion/tipo', 'documentoController@accionTipo');
        Route::get('ejecutor/activo', 'documentoController@ejecutorActivo');
        Route::get('poa/sector', 'documentoController@poaSector');
        Route::post('poa/subsector', 'documentoController@poaSubsector');
        Route::get('poa/situacion', 'documentoController@poaSituacion');
        Route::get('personal/tipo', 'documentoController@personalTipo');
        Route::get('personal/hijo', 'documentoController@personalHijo');
        Route::get('empleado/tipo', 'documentoController@empleadoTipo');
        Route::get('municipio/todo', 'documentoController@municipioTodo');
        Route::post('municipio/todo', 'documentoController@municipioTodoPost');
        Route::post('parroquia/todo', 'documentoController@parroquiaTodo');
        Route::get('municipio', 'documentoController@municipio');
        Route::get('periodo', 'documentoController@periodo');
        Route::post('periodo/tipo', 'documentoController@tipoPeriodo');
        Route::get('lapso', 'documentoController@lapso');
        Route::get('unidadmedida', 'documentoController@unidadmedida');
        Route::get('fuentefinanciamiento', 'documentoController@fuentefinanciamiento');
        Route::get('estado', 'documentoController@estado');
    });
});
//*Modulos de Reportes*/
Route::group(['namespace' => 'Reporte'], function () {
    //*Modulo de roles*/
    Route::group(['prefix' => 'jasper'], function () {
        Route::get('prueba', 'jasperController@prueba');
    });
    //*Modulo de roles*/
    Route::group(['prefix' => 'reporte'], function () {
        Route::get('libro/lista', 'reporteController@lista');
        Route::get('libro/ley', 'leyController@libro');
        Route::get('libro/distribucion', 'distribucionController@libro');
        Route::get('poa/proyecto', 'proyectoController@lista');
        Route::get('poa/proyecto/ubica', 'proyectoController@ubica');
        Route::get('poa/ac', 'acController@lista');
        Route::get('poa/ac/ubica', 'acController@ubica');
        Route::get('poa/fuentefinanciamiento', 'fuentefinanciamientoController@lista');
        Route::get('ac/responsable', 'acresponsableController@responsable');
        Route::get('ac/responsable/todo', 'acresponsableController@responsableTodo');
        Route::get('ac/responsable/exportar', 'acresponsableController@responsableExportar');
        Route::get('ac/responsable/todo/exportar', 'acresponsableController@responsableTodoExportar');
        Route::get('proyecto/responsable', 'proyectoresponsableController@responsable');
        Route::get('proyecto/responsable/todo', 'proyectoresponsableController@responsableTodo');
        Route::get('proyecto/responsable/exportar', 'proyectoresponsableController@responsableExportar');
        Route::get('proyecto/responsable/todo/exportar', 'proyectoresponsableController@responsableTodoExportar');
        Route::get('poa/proyecto/todo/exportar', 'proyectoController@todoExportar');
        Route::get('poa/proyecto/resumen', 'proyectoController@resumen');
        Route::get('poa/ac/resumen', 'acController@resumen');
        Route::get('poa/ac/ubicacion', 'acController@ubicacion');
        Route::get('poa/ac/ubicacion/todo', 'acController@ubicacionTodo');
        Route::get('poa/ac/ubicacion/exportar', 'acController@ubicacionExportar');
        Route::get('poa/ac/ubicacion/todo/exportar', 'acController@ubicacionTodoExportar');
        Route::get('ac/seguimiento/ficha/001/{id}', 'acseguimiento001Controller@ficha001');
        Route::get('ac/seguimiento/ficha/002/{id}', 'acseguimiento002Controller@ficha002');
        Route::get('ac/seguimiento/ficha/002/acumulada/{id}', 'acseguimiento002Controller@ficha002Acumulada');
        Route::get('ac/seguimiento/ficha/002/pendientes/{id}', 'acseguimiento002Controller@pendientes');
        Route::get('ac/seguimiento/ficha/003/{id}', 'acseguimiento003Controller@ficha003');
        Route::get('ac/seguimiento/ficha/003/acumulada/{id}', 'acseguimiento003Controller@ficha003Acumulada');
        Route::get('ac/seguimiento/ficha/003/exportar/{id}', 'acseguimiento003Controller@exportar');
        Route::get('ac/seguimiento/ficha/003/exportar/acumulada/{id}', 'acseguimiento003Controller@exportarAcumulada');
        Route::get('ac/seguimiento/ficha/003/pendientes/{id}', 'acseguimiento003Controller@pendientes');
        Route::get('ac/seguimiento/ficha/004/{id}', 'acseguimiento004Controller@ficha004');
        Route::get('ac/seguimiento/ficha/004/acumulada/{id}', 'acseguimiento004Controller@ficha004Acumulada');
        Route::get('ac/seguimiento/ficha/005/{id}', 'acseguimiento005Controller@ficha005');
        Route::get('ac/seguimiento/ficha/005/acumulada/{id}', 'acseguimiento005Controller@ficha005Acumulada');
        Route::get('ac/seguimiento/ficha/ejecucion/{id_lapso}', 'acseguimientoejecucionController@fichaEjecucion');
        Route::get('ac/seguimiento/ficha/ejecucion/{id_lapso}/{id}', 'acseguimientoejecucionController@fichaEjecucion');
        Route::get('ac/seguimiento/ficha/acumulada/ejecucion/{id_lapso}', 'acseguimientoejecucionController@fichaEjecucionAcumulada');
        Route::get('ac/seguimiento/ficha/acumulada/ejecucion/{id_lapso}/{id}', 'acseguimientoejecucionController@fichaEjecucionAcumulada');
        Route::get('ac/seguimiento/ficha/consolidado/{id_lapso}', 'acseguimientoController@fichaConsolidado');
        Route::get('ac/seguimiento/ficha/consolidado/{id_lapso}/{id}', 'acseguimientoController@fichaConsolidado');
        Route::get('ac/seguimiento/ficha/consolidado/acumulada/{id_lapso}/{id}', 'acseguimientoController@fichaConsolidadoAcumulada');
        Route::get('ac/seguimiento/ficha/primero/{id_lapso}/{id}', 'acseguimientoController@fichaConsolidadoPrimero');
        Route::get('ac/seguimiento/ficha/segundo/{id_lapso}/{id}', 'acseguimientoController@fichaConsolidadoSegundo');
        Route::get('ac/seguimiento/ficha/cuarto/{id_lapso}/{id}', 'acseguimientoController@fichaConsolidadoCuarto');
        Route::get('ac/seguimiento/ficha/quinto/{id_lapso}/{id}', 'acseguimientoController@fichaConsolidadoQuinto');
        Route::get('ac/seguimiento/consolidado/exportar/{id_lapso}', 'acseguimientoController@exportar');
        Route::get('ac/seguimiento/consolidado/exportarA/{id_lapso}', 'acseguimientoController@exportarA');
        Route::get('ac/seguimiento/{id}', 'acseguimientoController@reporte');
        Route::get('proyecto/seguimiento', 'proyectoseguimientoController@reporte');
        Route::get('poa/proyecto/todo', 'proyectoController@poaTodo');
        Route::get('poa/ac/todo', 'acController@poaTodo');
        Route::get('poa/ac/exportacion/ic/pac', 'acController@exportacion_icp_ac');
        Route::get('poa/ac/exportacion/ic/pac/desagregado', 'acController@exportacion_icp_ac_desagregado');
    });
});
//*Modulos de Proyecto*/
Route::group(['namespace' => 'Proyecto'], function () {
    //*Modulo de proyecto*/
    Route::group(['prefix' => 'proyecto'], function () {
        Route::get('nuevo', 'proyectoController@nuevo');
        Route::post('storeLista', 'proyectoController@storeLista');
        Route::post('abrir', 'proyectoController@abrir');
        Route::post('cerrar', 'proyectoController@cerrar');
    });
    //*Modulo de Proyecto Partidas*/
    Route::group(['prefix' => 'proyecto/ae/partida'], function () {
        Route::post('storeLista', 'proyectoaepartidaController@storeLista');
        Route::post('masivo', 'proyectoaepartidaController@procesarMasivo');
        Route::post('individual', 'proyectoaepartidaController@procesarIndividual');
        Route::get('{ae}/bajar', 'proyectoaepartidaController@bajar');
        Route::post('cargar', 'proyectoaedesagregadoController@procesarDesagregado');
        Route::post('desagregado', 'proyectoaedesagregadoController@editar');
        Route::post('desagregado/lista', 'proyectoaedesagregadoController@lista');
        Route::post('desagregado/storeLista', 'proyectoaedesagregadoController@storeLista');
    });
});
//*Modulos de Accion Centralizada*/
Route::group(['namespace' => 'Ac'], function () {
    //*Modulo de Accion Centralizada*/
    Route::group(['prefix' => 'ac'], function () {
        Route::get('nuevo', 'acController@nuevo');
        Route::post('storeLista', 'acController@storeLista');
        Route::post('guardar', 'acController@guardar');
        Route::post('guardar/{id}', 'acController@guardar');
        Route::post('cerrar', 'acController@cerrar');
    });
    //*Modulo de Accion Centralizada*/
    Route::group(['prefix' => 'ac/ae'], function () {
        Route::post('storeLista', 'acaeController@storeLista');
    });
    //*Modulo de Accion Centralizada Partidas*/
    Route::group(['prefix' => 'ac/ae/partida'], function () {
        Route::post('storeLista', 'acaepartidaController@storeLista');
        Route::post('masivo', 'acaepartidaController@procesarMasivo');
        Route::get('{ac}/{ae}/bajar', 'acaepartidaController@bajar');
        Route::post('cargar', 'acaedesagregadoController@procesarDesagregado');
        Route::post('desagregado/lista', 'acaedesagregadoController@lista');
        Route::post('desagregado/storeLista', 'acaedesagregadoController@storeLista');
    });
});
//*Modulos de Mantenimiento*/
Route::group(['namespace' => 'Mantenimiento'], function () {
    //*Modulo de Unidad de Medida*/
    Route::group(['prefix' => 'mantenimiento/unidadmedida'], function () {
        Route::get('lista', 'unidadmedidaController@lista');
        Route::post('storeLista', 'unidadmedidaController@storeLista');
        Route::get('nuevo', 'unidadmedidaController@nuevo');
        Route::get('editar/{id}', 'unidadmedidaController@editar');
        Route::post('guardar', 'unidadmedidaController@guardar');
        Route::post('guardar/{id}', 'unidadmedidaController@guardar');
        Route::post('eliminar', 'unidadmedidaController@eliminar');
        Route::post('habilitar', 'unidadmedidaController@habilitar');
    });
    //*Modulo de Ejecutores*/
    Route::group(['prefix' => 'mantenimiento/ejecutor'], function () {
        Route::get('lista', 'ejecutorController@lista');
        Route::post('storeLista', 'ejecutorController@storeLista');
        Route::get('nuevo', 'ejecutorController@nuevo');
        Route::get('editar/{id}', 'ejecutorController@editar');
        Route::post('guardar', 'ejecutorController@guardar');
        Route::post('guardar/{id}', 'ejecutorController@guardar');
        Route::post('eliminar', 'ejecutorController@eliminar');
        Route::post('habilitar', 'ejecutorController@habilitar');
    });
    //*Modulo de Sectores*/
    Route::group(['prefix' => 'mantenimiento/sector'], function () {
        Route::get('lista', 'sectorController@lista');
        Route::post('storeLista', 'sectorController@storeLista');
        Route::get('nuevo', 'sectorController@nuevo');
        Route::get('editar/{id}', 'sectorController@editar');
        Route::post('guardar', 'sectorController@guardar');
        Route::post('guardar/{id}', 'sectorController@guardar');
        Route::post('eliminar', 'sectorController@eliminar');
        Route::post('habilitar', 'sectorController@habilitar');
    });
    //*Modulo de Sectores*/
    Route::group(['prefix' => 'mantenimiento/objetivo'], function () {
        Route::get('lista', 'objetivoController@lista');
        Route::post('storeLista', 'objetivoController@storeLista');
        Route::get('nuevo', 'objetivoController@nuevo');
        Route::get('editar/{id}', 'objetivoController@editar');
        Route::post('guardar', 'objetivoController@guardar');
        Route::post('guardar/{id}', 'objetivoController@guardar');
        Route::post('eliminar', 'objetivoController@eliminar');
        Route::post('habilitar', 'objetivoController@habilitar');
    });
    //*Modulo de Planes del Zulia*/
    Route::group(['prefix' => 'mantenimiento/planzulia'], function () {
        Route::get('lista', 'planzuliaController@lista');
        Route::post('storeLista', 'planzuliaController@storeLista');
        Route::get('nuevo', 'planzuliaController@nuevo');
        Route::get('editar/{id}', 'planzuliaController@editar');
        Route::post('guardar', 'planzuliaController@guardar');
        Route::post('guardar/{id}', 'planzuliaController@guardar');
        Route::post('eliminar', 'planzuliaController@eliminar');
        Route::post('habilitar', 'planzuliaController@habilitar');
    });
    //*Modulo de Partidas*/
    Route::group(['prefix' => 'mantenimiento/partida'], function () {
        Route::get('lista', 'partidaController@lista');
        Route::post('storeLista', 'partidaController@storeLista');
        Route::get('nuevo', 'partidaController@nuevo');
        Route::get('editar/{id}', 'partidaController@editar');
        Route::post('guardar', 'partidaController@guardar');
        Route::post('guardar/{id}', 'partidaController@guardar');
        Route::post('eliminar', 'partidaController@eliminar');
        Route::post('habilitar', 'partidaController@habilitar');
    });
    //*Modulo de Cargos*/
    Route::group(['prefix' => 'mantenimiento/cargo'], function () {
        Route::get('lista', 'cargoController@lista');
        Route::post('storeLista', 'cargoController@storeLista');
        Route::get('nuevo', 'cargoController@nuevo');
        Route::get('editar/{id}', 'cargoController@editar');
        Route::post('guardar', 'cargoController@guardar');
        Route::post('guardar/{id}', 'cargoController@guardar');
        Route::post('eliminar', 'cargoController@eliminar');
        Route::post('habilitar', 'cargoController@habilitar');
    });
    //*Modulo de Apicaciones*/
    Route::group(['prefix' => 'mantenimiento/aplicacion'], function () {
        Route::get('lista', 'aplicacionController@lista');
        Route::post('storeLista', 'aplicacionController@storeLista');
        Route::get('nuevo', 'aplicacionController@nuevo');
        Route::get('editar/{id}', 'aplicacionController@editar');
        Route::post('guardar', 'aplicacionController@guardar');
        Route::post('guardar/{id}', 'aplicacionController@guardar');
        Route::post('eliminar', 'aplicacionController@eliminar');
        Route::post('habilitar', 'aplicacionController@habilitar');
    });
    //*Modulo de Fuente de Financiamiento*/
    Route::group(['prefix' => 'mantenimiento/fuentefinanciamiento'], function () {
        Route::get('lista', 'fuentefinanciamientoController@lista');
        Route::post('storeLista', 'fuentefinanciamientoController@storeLista');
        Route::get('nuevo', 'fuentefinanciamientoController@nuevo');
        Route::get('editar/{id}', 'fuentefinanciamientoController@editar');
        Route::post('guardar', 'fuentefinanciamientoController@guardar');
        Route::post('guardar/{id}', 'fuentefinanciamientoController@guardar');
        Route::post('eliminar', 'fuentefinanciamientoController@eliminar');
        Route::post('habilitar', 'fuentefinanciamientoController@habilitar');
    });
    //*Modulo de Fondo*/
    Route::group(['prefix' => 'mantenimiento/fondo'], function () {
        Route::get('lista', 'fondoController@lista');
        Route::post('storeLista', 'fondoController@storeLista');
        Route::get('nuevo', 'fondoController@nuevo');
        Route::get('editar/{id}', 'fondoController@editar');
        Route::post('guardar', 'fondoController@guardar');
        Route::post('guardar/{id}', 'fondoController@guardar');
        Route::post('eliminar', 'fondoController@eliminar');
        Route::post('habilitar', 'fondoController@habilitar');
    });
    //*Modulo de Recursos*/
    Route::group(['prefix' => 'mantenimiento/recurso'], function () {
        Route::get('lista', 'recursoController@lista');
        Route::post('storeLista', 'recursoController@storeLista');
        Route::get('nuevo', 'recursoController@nuevo');
        Route::get('editar/{id}', 'recursoController@editar');
        Route::post('guardar', 'recursoController@guardar');
        Route::post('guardar/{id}', 'recursoController@guardar');
        Route::post('eliminar', 'recursoController@eliminar');
        Route::post('habilitar', 'recursoController@habilitar');
    });
    //*Modulo de tipo de accion*/
    Route::group(['prefix' => 'mantenimiento/tipoaccion'], function () {
        Route::get('lista', 'tipoaccionController@lista');
        Route::post('storeLista', 'tipoaccionController@storeLista');
        Route::get('nuevo', 'tipoaccionController@nuevo');
        Route::get('editar/{id}', 'tipoaccionController@editar');
        Route::post('guardar', 'tipoaccionController@guardar');
        Route::post('guardar/{id}', 'tipoaccionController@guardar');
        Route::post('eliminar', 'tipoaccionController@eliminar');
        //*Modulo de tipo de accion especifica*/
        Route::get('ae/lista/{id}', 'tipoaccionaeController@lista');
        Route::post('ae/storeLista', 'tipoaccionaeController@storeLista');
        Route::get('ae/nuevo/{id}', 'tipoaccionaeController@nuevo');
        Route::get('ae/editar/{id}', 'tipoaccionaeController@editar');
        Route::post('ae/guardar', 'tipoaccionaeController@guardar');
        Route::post('ae/guardar/{id}', 'tipoaccionaeController@guardar');
        Route::post('ae/eliminar', 'tipoaccionaeController@eliminar');
        //*Modulo de tipo de accion partidas admitidas*/
        Route::get('partida/lista/{id}', 'tipoaccionpartidaController@lista');
        Route::post('partida/storeLista', 'tipoaccionpartidaController@storeLista');
        Route::get('partida/nuevo/{id}', 'tipoaccionpartidaController@nuevo');
        Route::get('partida/editar/{id}', 'tipoaccionpartidaController@editar');
        Route::post('partida/guardar', 'tipoaccionpartidaController@guardar');
        Route::post('partida/guardar/{id}', 'tipoaccionpartidaController@guardar');
        Route::post('partida/eliminar', 'tipoaccionpartidaController@eliminar');
    });
    //*Modulo de Presupuesto de Ingreso*/
    Route::group(['prefix' => 'mantenimiento/presupuestoingreso'], function () {
        Route::get('lista', 'presupuestoingresoController@lista');
        Route::post('storeLista', 'presupuestoingresoController@storeLista');
        Route::get('nuevo', 'presupuestoingresoController@nuevo');
        Route::get('editar/{id}', 'presupuestoingresoController@editar');
        Route::post('guardar', 'presupuestoingresoController@guardar');
        Route::post('guardar/{id}', 'presupuestoingresoController@guardar');
        Route::post('eliminar', 'presupuestoingresoController@eliminar');
        Route::post('habilitar', 'presupuestoingresoController@habilitar');
    });
    //*Modulo de Tipo de Personal*/
    Route::group(['prefix' => 'mantenimiento/tipopersonal'], function () {
        Route::get('lista', 'tipopersonalController@lista');
        Route::post('storeLista', 'tipopersonalController@storeLista');
        Route::get('nuevo', 'tipopersonalController@nuevo');
        Route::get('editar/{id}', 'tipopersonalController@editar');
        Route::post('guardar', 'tipopersonalController@guardar');
        Route::post('guardar/{id}', 'tipopersonalController@guardar');
        Route::post('eliminar', 'tipopersonalController@eliminar');
        Route::post('habilitar', 'tipopersonalController@habilitar');
    });
    //*Modulo de Clasificados por Tipo*/
    Route::group(['prefix' => 'mantenimiento/clasificadortipo'], function () {
        Route::get('lista', 'clasificadortipoController@lista');
        Route::post('storeLista', 'clasificadortipoController@storeLista');
        Route::get('nuevo', 'clasificadortipoController@nuevo');
        Route::get('editar/{id}', 'clasificadortipoController@editar');
        Route::post('guardar', 'clasificadortipoController@guardar');
        Route::post('guardar/{id}', 'clasificadortipoController@guardar');
        Route::post('eliminar', 'clasificadortipoController@eliminar');
        Route::post('habilitar', 'clasificadortipoController@habilitar');
    });
    //*Modulo de Escala Salarial*/
    Route::group(['prefix' => 'mantenimiento/escalasalarial'], function () {
        Route::get('lista', 'escalasalarialController@lista');
        Route::post('storeLista', 'escalasalarialController@storeLista');
        Route::get('nuevo', 'escalasalarialController@nuevo');
        Route::get('editar/{id}', 'escalasalarialController@editar');
        Route::post('guardar', 'escalasalarialController@guardar');
        Route::post('guardar/{id}', 'escalasalarialController@guardar');
        Route::post('eliminar', 'escalasalarialController@eliminar');
        Route::post('habilitar', 'escalasalarialController@habilitar');
    });
    //*Modulo de Objetivo Sectorial*/
    Route::group(['prefix' => 'mantenimiento/objetivosectorial'], function () {
        Route::get('lista', 'objetivosectorialController@lista');
        Route::post('storeLista', 'objetivosectorialController@storeLista');
        Route::get('nuevo', 'objetivosectorialController@nuevo');
        Route::get('editar/{id}', 'objetivosectorialController@editar');
        Route::post('guardar', 'objetivosectorialController@guardar');
        Route::post('guardar/{id}', 'objetivosectorialController@guardar');
        Route::post('eliminar', 'objetivosectorialController@eliminar');
        Route::post('habilitar', 'objetivosectorialController@habilitar');
    });
    //*Modulo de Distribucion por Municipio*/
    Route::group(['prefix' => 'mantenimiento/distribucionmunicipio'], function () {
        Route::get('lista', 'distribucionController@lista');
        Route::post('storeLista', 'distribucionController@storeLista');
        Route::get('nuevo', 'distribucionController@nuevo');
        Route::get('editar/{id}', 'distribucionController@editar');
        Route::post('guardar', 'distribucionController@guardar');
        Route::post('guardar/{id}', 'distribucionController@guardar');
        Route::post('eliminar', 'distribucionController@eliminar');
        Route::post('habilitar', 'distribucionController@habilitar');
        Route::get('parametro', 'distribucionparametroController@editar');
        Route::post('parametro/guardar', 'distribucionparametroController@guardar');
        Route::post('parametro/guardar/{id}', 'distribucionparametroController@guardar');
    });
    //*Modulo de Ejercicio Fiscal*/
    Route::group(['prefix' => 'mantenimiento/ejercicio'], function () {
        Route::get('lista', 'ejerciciofiscalController@lista');
        Route::post('storeLista', 'ejerciciofiscalController@storeLista');
        Route::get('nuevo', 'ejerciciofiscalController@nuevo');
        Route::get('cronograma/{id}', 'ejerciciofiscalController@cronograma');
        Route::post('guardar', 'ejerciciofiscalController@guardar');
        Route::post('guardar/{id}', 'ejerciciofiscalController@guardar');
        Route::post('cerrar', 'ejerciciofiscalController@cerrar');
        Route::post('habilitar', 'ejerciciofiscalController@habilitar');
        Route::post('cronograma/storeLista', 'ejerciciocronogramaController@storeLista');
        Route::post('cronograma/nuevo', 'ejerciciocronogramaController@nuevo');
        Route::get('cronograma/editar/{id}', 'ejerciciocronogramaController@editar');
        Route::post('cronograma/guardar', 'ejerciciocronogramaController@guardar');
        Route::post('cronograma/guardar/{id}', 'ejerciciocronogramaController@guardar');
        Route::post('cronograma/eliminar', 'ejerciciocronogramaController@eliminar');
    });
    //*Modulo de Cronograma*/
    Route::group(['prefix' => 'mantenimiento/lapso'], function () {
        Route::get('lista', 'lapsoController@lista');
        Route::post('storeLista', 'lapsoController@storeLista');
        Route::get('nuevo', 'lapsoController@nuevo');
        Route::get('editar/{id}', 'lapsoController@editar');
        Route::post('guardar', 'lapsoController@guardar');
        Route::post('guardar/{id}', 'lapsoController@guardar');
        Route::post('eliminar', 'lapsoController@eliminar');
        Route::post('habilitar', 'lapsoController@habilitar');
    });
});
//*Modulos de Accion Centralizada Seguimiento*/
Route::group(['namespace' => 'AcSeguimiento'], function () {
    //*Modulo de Accion Centralizada*/
    Route::group(['prefix' => 'ac/seguimiento'], function () {
        Route::get('lista', 'acController@lista');
        Route::post('storeLista', 'acController@storeLista');
        Route::get('nuevo', 'acController@nuevo');
        Route::post('disponible', 'acController@disponible');
        Route::post('guardar', 'acController@guardar');
        Route::post('detalle', 'acController@detalle');
    });
    //*Modulo de Accion Centralizada Forma 001*/
    Route::group(['prefix' => 'ac/seguimiento/001'], function () {
        Route::get('lista/{id}', 'formaunoController@lista');
        Route::post('storeLista', 'formaunoController@storeLista');
        Route::post('detalle', 'formaunoController@detalle');
        Route::get('editar/{id}', 'formaunoController@datos');
        Route::get('editarSector/{id}', 'formaunoController@datosSector');
        Route::post('guardar', 'formaunoController@guardar');
        Route::post('guardar/{id}', 'formaunoController@guardar');
        Route::post('guardarSector/{id}', 'formaunoController@guardarSector');
        Route::post('enviar/{id}', 'formaunoController@enviar');
        Route::post('eliminar', 'formaunoController@eliminar');
        Route::post('crearPeriodo', 'formaunoController@crearPeriodo');
        Route::post('extender', 'formaunoController@extender');
    });
    //*Modulo de Accion Centralizada Forma 001*/
    Route::group(['prefix' => 'seguimiento/ac/001/cambio'], function () {
        Route::get('lista', 'formaunoController@listaCambio');
        Route::post('storeLista', 'formaunoController@storeListaCambio');
        Route::post('detalle', 'formaunoController@detalleCambio');
        Route::get('editar/{id}', 'formaunoController@datosCambio');
        Route::post('aprobar/{id}', 'formaunoController@aprobar');
        Route::post('negar/{id}', 'formaunoController@negar');
    });
    //*Modulo de Accion Centralizada Forma 002*/
    Route::group(['prefix' => 'ac/seguimiento/002'], function () {
        Route::get('lista/{id}', 'formadosController@lista');
        Route::post('storeLista', 'formadosController@storeLista');
        Route::post('detalle', 'formadosController@detalle');
        Route::get('datos/{id}', 'formadosController@datos');
        Route::get('editar/{id}', 'formadosController@editarAc');
        Route::get('editarAe/{id}', 'formadosController@editarAe');
        Route::post('guardarEditarAc/{id}', 'formadosController@guardarEditarAc');
        Route::post('guardarEditarAe/{id}', 'formadosController@guardarEditarAe');
        Route::post('datos/storeLista', 'formadosController@datosstoreLista');
        Route::get('actividad/lista/{id}', 'formadosController@editar');
        Route::post('actividad/storeLista', 'formadosController@actividadstoreLista');
        Route::get('actividad/editar/{id}', 'formadosController@editarActividad');
        Route::get('actividad/nuevo/{id}', 'formadosController@nuevoActividad');
        Route::post('actividad/guardar', 'formadosController@guardar');
        Route::post('actividad/enviar', 'formadosController@enviar');
        Route::post('actividad/enviar/{id}', 'formadosController@enviar');
        Route::post('actividad/eliminar', 'formadosController@eliminar');
        Route::post('actividad/enviarAprobar', 'formadosController@cargar');
        Route::get('actividad/financiera/nuevo/{id}', 'formadosController@nuevoFinanciera');
        Route::post('actividad/financiera/partida', 'formadosController@partida');
        Route::post('actividad/financiera/fuentefinanciamiento', 'formadosController@fondoTipo');
    });    
    //*Modulo de Accion Centralizada Forma 002*/
    Route::group(['prefix' => 'seguimiento/ac/002/cambio'], function () {
        Route::get('lista', 'formadosController@listaCambio');
        Route::get('listaAe/{id}', 'formadosController@listaCambioAe');
        Route::post('storeLista', 'formadosController@storeListaCambio');
        Route::post('storeListaAe', 'formadosController@storeListaCambioAe');
        Route::post('detalle', 'formadosController@detalleCambio');
        Route::get('editar/{id}', 'formadosController@datosCambio');
        Route::post('aprobar/{id}', 'formadosController@aprobar');
        Route::post('negar/{id}', 'formadosController@negar');
    });    
    //*Modulo de Accion Centralizada Forma 003*/
    Route::group(['prefix' => 'ac/seguimiento/003'], function () {
        Route::get('lista/{id}', 'formatresController@lista');
        Route::get('editar/{id}', 'formatresController@editarAc');
        Route::post('guardar/{id}', 'formatresController@guardarEditarAc');
        Route::post('storeLista', 'formatresController@storeLista');
        Route::post('detalle', 'formatresController@detalle');
        Route::get('datos/{id}', 'formatresController@datos');
        Route::post('datos/storeLista', 'formatresController@datosstoreLista');
        Route::get('actividad/lista/{id}', 'formatresController@editar');
        Route::post('actividad/storeLista', 'formatresController@actividadstoreLista');
        Route::get('actividad/nuevo/{id}', 'formatresController@nuevoActividad');
        Route::get('actividad/editar/{id}', 'formatresController@editarActividad');
        Route::get('actividad/editarFinanciera/{id}', 'formatresController@editarFinanciera');
        Route::post('actividad/financiera/storeLista/{id}', 'formatresController@financierastoreLista');
        Route::get('actividad/financiera/nuevo/{id}', 'formatresController@nuevoFinanciera');
        Route::post('actividad/financiera/guardar', 'formatresController@guardarFinanciera');
        Route::post('actividad/financiera/guardar/{id}', 'formatresController@guardarFinanciera');
        Route::post('actividad/guardar/{id}', 'formatresController@guardar');
        Route::post('actividad/enviarAprobar', 'formatresController@cargar');
    });
    
    //*Modulo de Accion Centralizada Forma 002*/
    Route::group(['prefix' => 'seguimiento/ac/003/cambio'], function () {
        Route::get('lista', 'formatresController@listaCambio');
        Route::get('listaAe/{id}', 'formatresController@listaCambioAe');
        Route::post('storeLista', 'formatresController@storeListaCambio');
        Route::post('storeListaAe', 'formatresController@storeListaCambioAe');
        Route::get('editar/{id}', 'formatresController@datosCambio');
        Route::post('aprobar/{id}', 'formatresController@aprobar');
        Route::post('negar/{id}', 'formatresController@negar');
    });     
    //*Modulo de Accion Centralizada Forma 004*/
    Route::group(['prefix' => 'ac/seguimiento/004'], function () {
        Route::get('lista/{id}', 'formacuatroController@lista');
        Route::post('storeLista', 'formacuatroController@storeLista');
        Route::post('detalle', 'formacuatroController@detalle');
        Route::get('datos/{id}', 'formacuatroController@datos');
        Route::post('datos/storeLista', 'formacuatroController@datosstoreLista');
        Route::get('actividad/lista/{id}', 'formacuatroController@editar');
        Route::post('actividad/storeLista', 'formacuatroController@actividadstoreLista');
        Route::get('actividad/nuevo/{id}', 'formacuatroController@nuevoActividad');
        Route::get('actividad/editar/{id}', 'formacuatroController@editarActividad');
        Route::post('actividad/guardar', 'formacuatroController@guardar');
        Route::post('actividad/guardar/{id}', 'formacuatroController@guardar');
        Route::post('actividad/eliminar', 'formacuatroController@eliminar');
        Route::post('actividad/financiera/storeLista/{id}', 'formacuatroController@financierastoreLista');
        Route::get('actividad/financiera/nuevo/{id}', 'formacuatroController@nuevoFinanciera');
        Route::get('actividad/financiera/editar/{id}', 'formacuatroController@editarFinanciera');
        Route::post('actividad/financiera/guardar', 'formacuatroController@guardarFinanciera');
        Route::post('actividad/financiera/guardar/{id}', 'formacuatroController@guardarFinanciera');
        Route::post('actividad/financiera/partida', 'formacuatroController@partida');
        Route::post('actividad/financiera/eliminar', 'formacuatroController@eliminarFinanciera');
    });
    //*Modulo de Accion Centralizada Forma 005*/
    Route::group(['prefix' => 'ac/seguimiento/005'], function () {
        Route::get('lista/{id}', 'formacincoController@lista');
        Route::post('storeLista', 'formacincoController@storeLista');
        Route::post('datos/storeListaDatos', 'formacincoController@storeListaDatos');
        Route::post('detalle', 'formacincoController@detalle');
        Route::get('nuevo/{id}', 'formacincoController@datosNuevo');
        Route::get('editar/{id}', 'formacincoController@datos');
        Route::get('datos/lista/{id}', 'formacincoController@datosLista');
        Route::post('eliminar', 'formacincoController@eliminar');
        Route::post('guardar', 'formacincoController@guardar');
        Route::post('guardar/{id}', 'formacincoController@guardar');
        Route::post('enviar', 'formacincoController@enviar');
        Route::post('enviar/{id}', 'formacincoController@enviar');
    });
    //*Modulo de Accion Centralizada Forma 005*/
    Route::group(['prefix' => 'seguimiento/ac/005/cambio'], function () {
        Route::get('lista', 'formacincoController@listaCambio');
        Route::post('storeLista', 'formacincoController@storeListaCambio');
        Route::post('detalle', 'formacincoController@detalleCambio');
        Route::get('editar/{id}', 'formacincoController@datosCambio');
        Route::post('aprobar/{id}', 'formacincoController@aprobar');
        Route::post('negar/{id}', 'formacincoController@negar');
    });
    //*Modulo de Accion Centralizada Ejecucion*/
    Route::group(['prefix' => 'ac/seguimiento/ejecucion'], function () {
        Route::get('lista/{id}', 'ejecucionController@lista');
        Route::post('storeLista', 'ejecucionController@storeLista');
        Route::post('detalle', 'ejecucionController@detalle');
    });
});
//*Modulos de Proyectos Seguimiento*/
Route::group(['namespace' => 'PrSeguimiento'], function () {
    //*Modulo de Proyectos*/
    Route::group(['prefix' => 'proyecto/seguimiento'], function () {
        Route::get('lista', 'proyectoController@lista');
        Route::post('storeLista', 'proyectoController@storeLista');
        Route::get('nuevo', 'proyectoController@nuevo');
        Route::post('disponible', 'proyectoController@disponible');
        Route::post('guardar', 'proyectoController@guardar');
        Route::post('detalle', 'proyectoController@detalle');
    });
    //*Modulo de Proyecto Forma 001*/
    Route::group(['prefix' => 'proyecto/seguimiento/001'], function () {
        Route::get('lista', 'formaunoController@lista');
        Route::post('storeLista', 'formaunoController@storeLista');
        Route::post('detalle', 'formaunoController@detalle');
        Route::get('editar/{id}', 'formaunoController@datos');
        Route::post('guardar', 'formaunoController@guardar');
        Route::post('guardar/{id}', 'formaunoController@guardar');
        Route::post('enviar/{id}', 'formaunoController@enviar');
    });
    //*Modulo de Proyecto Forma 001*/
    Route::group(['prefix' => 'seguimiento/proyecto/001/cambio'], function () {
        Route::get('lista', 'formaunoController@listaCambio');
        Route::post('storeLista', 'formaunoController@storeListaCambio');
        Route::post('detalle', 'formaunoController@detalleCambio');
        Route::get('editar/{id}', 'formaunoController@datosCambio');
        Route::post('aprobar/{id}', 'formaunoController@aprobar');
        Route::post('negar/{id}', 'formaunoController@negar');
    });
    //*Modulo de Accion Centralizada Forma 005*/
    Route::group(['prefix' => 'proyecto/seguimiento/005'], function () {
        Route::get('lista', 'formacincoController@lista');
        Route::post('storeLista', 'formacincoController@storeLista');
        Route::post('detalle', 'formacincoController@detalle');
        Route::get('editar/{id}', 'formacincoController@datos');
        Route::post('guardar', 'formacincoController@guardar');
        Route::post('guardar/{id}', 'formacincoController@guardar');
        Route::post('enviar/{id}', 'formacincoController@enviar');
    });
    //*Modulo de Accion Centralizada Forma 005*/
    Route::group(['prefix' => 'seguimiento/proyecto/005/cambio'], function () {
        Route::get('lista', 'formacincoController@listaCambio');
        Route::post('storeLista', 'formacincoController@storeListaCambio');
        Route::post('detalle', 'formacincoController@detalleCambio');
        Route::get('editar/{id}', 'formacincoController@datosCambio');
        Route::post('aprobar/{id}', 'formacincoController@aprobar');
        Route::post('negar/{id}', 'formacincoController@negar');
    });
    //*Modulo de Accion Centralizada Ejecucion*/
    Route::group(['prefix' => 'proyecto/seguimiento/ejecucion'], function () {
        Route::get('lista', 'ejecucionController@lista');
        Route::post('storeLista', 'ejecucionController@storeLista');
        Route::post('detalle', 'ejecucionController@detalle');
    });
});
