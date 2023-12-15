<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    //Route::post('register', 'AuthController@register');


});
/******************************************************************/
//Ruteo de los usuarios

Route::group(['middleware' => 'auth'], function () {


    //Route::post('mensajeria/{id}', 'MensajeriaController@general');
    //https://jwt-auth.readthedocs.io/en/develop/quick-start/
    //Ruteo de Listado completo de usuarios

    Route::resource('usuario', 'UsuariosController'); //NO PONER ID_INSTITUCION
    //Refresh token
    Route::post('user/tokenRefresh', 'AuthController@refresh');

    Route::resource('user/me', 'AuthController@me'); //NO PONER ID_INSTITUCION




    //DATOS ADMINISTRACION
    Route::get('home/administracion/{id}', 'HomeController@administracion');


    //Ruteo de Comunicados
    Route::post('comunicados/{id}', 'ComunicadosController@general');
    //Ruteo de la lectura de Comunicados
    Route::put('lectura_comunicado/{id}', 'ComunicadosController@lectura_comunicado');
    //Ruteo de la lectura de Comunicados ALTERNATIVA
    Route::put('lectura_comunicado_a/{id}', 'ComunicadosController@lectura_comunicado_a');

    //Ruteo del eventos de agenda
    Route::get('agenda/{id}', 'AgendaController@general');

    //Ruteo de Mensajeria
    Route::post('mensajeria/{id}', 'MensajeriaController@general');
    //Ruteo de la lectura de una cadena de Chat
    Route::put('lectura_mensajeria/{id}', 'MensajeriaController@lectura_mensajeria');
    //Ruteo para obtener el historial de mensajes de un CHAT específico
    Route::get('historial_mensajes/{id}', 'MensajeriaController@historial_mensajes');
    //Ruteo para enviar un mensaje a un chat específico ya existente
    Route::post('enviar_chat/{id}', 'MensajeriaController@enviar_chat');
    //Ruteo para enviar un mensaje a un chat nuevo
    Route::post('nuevo_chat/{id}', 'MensajeriaController@nuevo_chat');
    //Ruteo para solicitar listado de chats
    //Route::post('enviar_chat/{id}', 'MensajeriaController@enviar_chat');
    //Ruteo para obtener el listado de posibles destinatarios de chats
    Route::get('destinatarios_chats/{id}', 'MensajeriaController@destinatarios_chats');

//RUTEO DE HOME
//Ruteo CANTIDAD DE ESTUDIANTES SIN VINCULACIÓN
Route::get('responsables/estadistica_vinculacion/{id}', 'ResponsablesController@estadistica_vinculacion');
//Ruteo de Estadisticas
//Ruta de Total Facturado
Route::get('home/total_facturado/{id}', 'HomeController@total_facturado');
//Ruta de Total Cobrado
Route::get('home/total_cobrado/{id}', 'HomeController@total_cobrado');
//Ruta detalle cobranza
Route::get('home/detalle_cobranza/{id}', 'HomeController@detalle_cobranza');
//Ruta total estudiantes
Route::get('home/total_estudiantes/{id}', 'HomeController@total_estudiantes');
//Ruta Cobranza Evolutiva
Route::get('home/cobranza_evolutiva/{id}', 'HomeController@cobranza_evolutiva');
//Ruta sintesis medios de pago
Route::get('home/sintesis_medios_pago/{id}', 'HomeController@sintesis_medios_pago');
//Ruta cobranzas recientes
Route::get('home/cobranzas_recientes/{id}', 'HomeController@cobranzas_recientes');
//Ruta Notificaciones
Route::get('home/notificaciones/{id}', 'HomeController@notificaciones');

    //Ruteo CAMPAÑAS OK

//Ruteo insertar campañas
Route::post('campanas/agregar_campana/{id}', 'CampanasController@agregar_campana');
//Ruteo modificar campañas (PASO ID DEL campañas)
Route::post('campanas/modificar_campana/{id}', 'CampanasController@modificar_campana');
//Ruteo borrar campañas (PASO ID DEL campañas)
Route::put('campanas/borrar_campana/{id}', 'CampanasController@borrar_campana');
//Ruteo listado completo de campañas (muestro todo DE campañas)
Route::get('campanas/listado_campanas/{id}', 'CampanasController@listado_campanas');
//Ruteo mostrar un campaña en particular (PASO el ID de campañas)
Route::get('campanas/mostrar_campana/{id}', 'CampanasController@mostrar_campana');


//Ruteo CONCEPTOS OK

//Ruteo insertar conceptos
Route::post('conceptos/agregar_concepto/{id}', 'ConceptosController@agregar_conceptos');
//Ruteo modificar conceptos (PASO ID,nombre,importe DEL conceptos)
Route::post('conceptos/modificar_concepto/{id}', 'ConceptosController@modificar_conceptos');
//Ruteo borrar conceptos (PASO ID DEL conceptos)
Route::put('conceptos/borrar_concepto/{id}', 'ConceptosController@borrar_conceptos');
//Ruteo listado completo de conceptos (muestro todo DE conceptos)
Route::get('conceptos/listado_conceptos/{id}', 'ConceptosController@mostrar_conceptos');
//Ruteo mostrar un concepto en particular (PASO el ID de CONCEPTO)
Route::get('conceptos/mostrar_concepto/{id}', 'ConceptosController@mostrar_concepto');


//Ruteo PERIODOS OK

//Ruteo insertar periodos
Route::post('periodos/agregar_periodo/{id}', 'PeriodosController@agregar_periodo');
//Ruteo modificar periodos (PASO ID DEL periodos)
Route::post('periodos/modificar_periodo/{id}', 'PeriodosController@modificar_periodo');
//Ruteo insertar periodos
Route::post('periodos/agregar_subperiodo/{id}', 'PeriodosController@agregar_subperiodo');
//Ruteo modificar periodos (PASO ID DEL periodos)
Route::post('periodos/modificar_subperiodo/{id}', 'PeriodosController@modificar_subperiodo');
//Ruteo borrar periodos (PASO ID DEL periodos)
Route::put('periodos/borrar_periodo/{id}', 'PeriodosController@borrar_periodo');
//Ruteo borrar SUBperiodos (PASO ID DEL periodos)
Route::put('periodos/borrar_subperiodo/{id}', 'PeriodosController@borrar_subperiodo');
//Ruteo listado completo de periodos (muestro todo DE periodos)
Route::get('periodos/listado_periodos/{id}', 'PeriodosController@listado_periodos');
//Ruteo mostrar un periodo en particular (PASO el ID de periodos)
Route::get('periodos/mostrar_periodo/{id}', 'PeriodosController@mostrar_periodo');


//Ruteo MEDIOS DE PAGO OK
//Ruteo insertar medios de pago
Route::post('medios_pago/agregar_medio_pago/{id}', 'Medios_PagoController@agregar_medio_pago');
//Ruteo modificar medios de pago (PASO ID DEL MEDIO DE PAGO)
Route::post('medios_pago/modificar_medio_pago/{id}', 'Medios_PagoController@modificar_medio_pago');
//Ruteo listado medios de pago
Route::get('medios_pago/listado_medios_pago/{id}', 'Medios_PagoController@mostrar_medio_pago');
//Ruteo mostrar medios de pago
Route::get('medios_pago/mostrar_medio_pago/{id}', 'Medios_PagoController@ver_medio_pago');
//Ruteo Desactivar borrar medios de pago (PASO ID DEL MEDIO DE PAGO)
Route::put('medios_pago/desactivar_medio_pago/{id}', 'Medios_PagoController@desactivar_medio_pago');
//Ruteo Activar medios de pago (PASO ID DEL MEDIO DE PAGO)
Route::put('medios_pago/activar_medio_pago/{id}', 'Medios_PagoController@activar_medio_pago');
//Ruteo borrar medios de pago (PASO ID DEL MEDIO DE PAGO)   
Route::put('medios_pago/borrar_medio_pago/{id}', 'Medios_PagoController@borrar_medio_pago');

//Ruteo EMPRESAS
//Ruteo listado de empresas
Route::get('empresa/listado_empresas/{id}', 'EmpresasController@listado_empresas');
//Ruteo agregar empresa
Route::post('empresa/agregar_empresa/{id}', 'EmpresasController@agregar_empresa');
//Ruteo modificar empresa
Route::post('empresa/modificar_empresa/{id}', 'EmpresasController@modificar_empresa');
//Ruteo borrar empresa
Route::put('empresa/borrar_empresa/{id}', 'EmpresasController@borrar_empresa');
//Ruteo ver empresa
Route::get('empresa/ver_empresa/{id}', 'EmpresasController@ver_empresa');
//Ruteo tipos_documentos
//Route::get('empresa/lista_documentos/{id}', 'EmpresasController@lista_doc');

//Ruteo RESPONSABLES ECONÓMICOS
//Ruteo Lista de responsables OK
Route::get('responsables/listado/{id}', 'ResponsablesController@listado');
//Ruteo Ver responsable OK
Route::get('responsables/ver/{id}', 'ResponsablesController@ver');
//Ruteo Agregar Responsable OK
Route::post('responsables/agregar/{id}', 'ResponsablesController@agregar');
//Ruteo Modificar Responsable OK
Route::post('responsables/modificar/{id}', 'ResponsablesController@modificar');
//Ruteo Eliminar Responsable OK
Route::put('responsables/borrar/{id}', 'ResponsablesController@borrar');
//Ruteo Vincular Estudiante OK
Route::post('responsables/vincular_estudiante/{id}', 'ResponsablesController@vincular_estudiante');
//Ruteo DesVincular Estudiante OK
Route::post('responsables/desvincular_estudiante/{id}', 'ResponsablesController@desvincular_estudiante');
//Ruteo de Estudiantes Vinculables OK
Route::get('responsables/estudiantes_vinculables/{id}', 'ResponsablesController@estudiantes_vinculables');
//Ruteo Cuenta Corriente OK Falta Verificar con todos los movimientos
Route::get('responsables/ver_cc/{id}', 'ResponsablesController@ver_cc');

//Ruteo Saldo
Route::get('responsables/saldo/{id}', 'ResponsablesController@saldo');
//Ruteo Generacion Intereses
Route::get('responsables/interes_gen/{id}', 'ResponsablesController@interes_gen');
//Ruteo Cargos Manuales
Route::post('responsables/generar_cargo/{id}', 'ResponsablesController@cargo_gen');
//Ruteo Borrar Cargos Manuales
Route::put('responsables/borrar_cargo/{id}', 'ResponsablesController@borrar_gen');
//Ruteo Lista de Conceptos para cargos
Route::get('responsables/lista_movimientos_cuenta/{id}', 'ResponsablesController@lista_movimientos_cuenta');
//Ruteo Lista de Condiciones de IVA OK
Route::get('responsables/condiciones_iva/{id}', 'ResponsablesController@condiciones_iva');
//Ruteo Generacion Ajuste a Comprobante
Route::post('responsables/generar_ajuste/{id}', 'ResponsablesController@generar_ajuste');





//Ruteo de BENEFICIOS
//Ruteo Lista de beneficios -OK
Route::get('beneficios/listado/{id}', 'BeneficiosController@listado');
//Ruteo Ver Beneficio -OK
Route::get('beneficios/ver/{id}', 'BeneficiosController@ver');
//Ruteo Agregar Beneficio -OK
Route::post('beneficios/agregar/{id}', 'BeneficiosController@agregar');
//Ruteo Modificar Beneficio -OK
Route::post('beneficios/modificar/{id}', 'BeneficiosController@modificar');
//Ruteo Eliminar Beneficio -OK
Route::put('beneficios/borrar/{id}', 'BeneficiosController@borrar');
//Ruteo Asignar Beneficio -OK
Route::post('beneficios/asignar/{id}', 'BeneficiosController@asignar');
//Ruteo Modificar Asignacion OK
Route::post('beneficios/modificar_asignacion/{id}', 'BeneficiosController@modificar_asignacion');
//Ruteo Eliminar Asignacion OK
Route::put('beneficios/borrar_asignacion/{id}', 'BeneficiosController@borrar_asignacion');
//Ruteo Suspender Asignacion OK
Route::put('beneficios/suspender_asignacion/{id}', 'BeneficiosController@suspender_asignacion');
//Ruteo Reactivar Asignacion OK
Route::put('beneficios/reactivar_asignacion/{id}', 'BeneficiosController@reactivar_asignacion');
//Ruteo Ver Beneficio de Alumno -OK
Route::get('beneficios/ver_alumno/{id}', 'BeneficiosController@ver_alumno');

//Ruteo de Generación de LOTES
//Ruteo Lista de Lotes (*verificado)
Route::get('lotes/listado/{id}', 'LotesController@listado');
//Ruteo agregar Lote Paso 1 (*verificado)
Route::post('lotes/agregar_paso1/{id}', 'LotesController@agregar_p1');
//Ruteo Ver Lote Paso 1 (*verificado)
Route::get('lotes/ver_paso1/{id}', 'LotesController@ver_p1');
//Ruteo Períodos Utilizables (*verificado)
Route::get('lotes/periodos_libres/{id}', 'LotesController@periodos_libres');
//Ruteo Períodos completos(*verificado)
Route::get('lotes/periodos/{id}', 'LotesController@periodos');
//Ruteo modificar Lote Paso 1  (*verificado)
Route::post('lotes/modificar_paso1/{id}', 'LotesController@modificar_p1');
//Ruteo eliminar Lote (*Verificado)
Route::put('lotes/borrar/{id}', 'LotesController@borrar');
//Ruteo agregar Lote Paso 2
//Route::post('lotes/agregar_paso2/{id}', 'LotesController@agregar_p2');
//Ruteo Ver Lote Paso 1 (*Verificado)
Route::get('lotes/ver_paso2/{id}', 'LotesController@ver_p2');
//Ruteo modificar Lote Paso 2
Route::post('lotes/modificar_paso2/{id}', 'LotesController@modificar_p2');
//Ruteo agregar Lote Paso 3
//Route::post('lotes/agregar_paso3/{id}', 'LotesController@agregar_p3');
//Ruteo Ver Lote Paso 3
Route::get('lotes/ver_paso3/{id}', 'LotesController@ver_p3');
//Ruteo modificar Lote Paso 3
Route::post('lotes/modificar_paso3/{id}', 'LotesController@modificar_p3');
//Ruteo Confirmar Lote
Route::get('lotes/confirmar/{id}', 'LotesController@confirmar');
//Ruteo Generar Lote
Route::put('lotes/generar/{id}', 'LotesController@generar');
//Ruteo Publicar
Route::get('lotes/publicar/{id}', 'LotesController@publicar');
//Ruteo Ver Lote
Route::get('lotes/ver/{id}', 'LotesController@ver');
//Ruteo Detalle Lote
Route::get('lotes/detalle/{id}', 'LotesController@detalle');
//Ruteo Detalle comprobante
Route::get('lotes/detalle_comprobante/{id}', 'LotesController@detalle_comprobante');
//Eliminar Comprobante
Route::put('lotes/borrar_comprobante/{id}', 'LotesController@borrar_comprobante');
//Eliminar Comprobante
Route::put('lotes/borrar_concepto/{id}', 'LotesController@borrar_concepto');
//Ruteo modificar concepto
Route::post('lotes/modificar_concepto/{id}', 'LotesController@modificar_concepto');
//Ruteo agregar concepto
Route::post('lotes/agregar_concepto/{id}', 'LotesController@agregar_concepto');

//Ruteo de Reenvio de Publicacion + 
Route::get('lotes/republicar_lote/{id}', 'LotesController@republicar_lote');
//Ruteo de Reenvio de Comprobante +
Route::get('lotes/republicar_comprobante/{id}', 'LotesController@republicar_comprobante');
//Ruteo de Facturas Emitidas
Route::get('lotes/facturas_emitidas/{id}', 'LotesController@facturas_emitidas');
//Ruteo de Reenvio de Factura
Route::get('lotes/republicar_factura/{id}', 'LotesController@republicar_factura');

//Ruteo de CAJA Y COBRANZA
//Ruteo Lista de Movimientos diarios OK
Route::get('caja/movimientos_diarios/{id}', 'CajaController@movimientos_diarios');
//Ruteo Lista de Movimientos diarios OK
Route::get('caja/movimientos_historicos/{id}', 'CajaController@movimientos_historicos');
//Ruteo BOrrar Pago OK
Route::put('caja/borrar_pago/{id}', 'CajaController@borrar_pago');
//Ruteo Listado de Cajas OK
Route::get('caja/listado/{id}', 'CajaController@listado');
//Ruteo Listado de Cajas abiertas OK
Route::get('caja/listado_abiertas/{id}', 'CajaController@listado_abiertas');
//Ruteo Enviar Comprobante
Route::get('caja/enviar_comprobante/{id}', 'CajaController@enviar_comprobante');
//Ruteo NUeva Cobranza
//Route::get('caja/nueva_cobranza/{id}', 'CajaController@nueva_cobranza');
//Ruteo Comprobantes Pendientes OK
Route::get('caja/comprobantes_pendientes/{id}', 'CajaController@comprobantes_pendientes');
//Ruteo Recibir Cobranza
Route::post('caja/recibir_cobranza/{id}', 'CajaController@recibir_cobranza');
//Ruteo Recibir Cobranza EFECTIVO OK
Route::post('caja/recibir_cobranza_efectivo/{id}', 'CajaController@recibir_cobranza_efectivo');
//Ruteo Recibir Cobranza TRANSFERENCIA OK
Route::post('caja/recibir_cobranza_transferencia/{id}', 'CajaController@recibir_cobranza_transferencia');
//Ruteo Recibir Cobranza TRANSFERENCIA OK
Route::post('caja/recibir_cobranza_cheque/{id}', 'CajaController@recibir_cobranza_cheque');
//Ruteo Detalle Cobranza
Route::get('caja/detalle_cobranza/{id}', 'CajaController@detalle_cobranza');
//Ruteo Detalle Cobranza
Route::get('caja/detalle_medios_pago/{id}', 'CajaController@detalle_medios_pago');
//Ruteo Planilla Caja OK
Route::get('caja/planilla_caja/{id}', 'CajaController@planilla_caja');
//Ruteo Apertura de Caja
//Route::post('caja/apertura_caja/{id}', 'CajaController@apertura_caja');
//Ruteo Egreso de Caja OK
Route::post('caja/egreso_caja/{id}', 'CajaController@egreso_caja');
//Ruteo Borrar Egreso de Caja OK
Route::put('caja/borrar_egreso_caja/{id}', 'CajaController@borrar_egreso_caja');
//Ruteo Apertura de Caja OK
Route::post('caja/apertura_caja/{id}', 'CajaController@apertura_caja');
//Ruteo Cierre de Caja OK
Route::put('caja/cierre_caja/{id}', 'CajaController@cierre_caja');
//Ruteo Testeo de API Facturante
Route::get('caja/test_facturante/{id}', 'CajaController@test_facturante');
//Ruteo Cambio Estado de Facturacion
Route::get('caja/cambio_estado_facturacion/{id}', 'CajaController@cambio_estado_facturacion');

//Ruteo de FACTURAS
//Ruteo Lista de Factutas Emitidas
Route::get('facturas/listado/{id}', 'FacturacionController@listado');
//Ruteo Lista de Notas de Credito/Debito
Route::get('facturas/listado_notas/{id}', 'FacturacionController@listado_notas');
//Reenvio de Nota de Crédito
Route::get('facturas/reenviar_nota_c/{id}', 'FacturacionController@reenviar_nota_c');
//Reenvio de Débito
Route::get('facturas/reenviar_nota_d/{id}', 'FacturacionController@reenviar_nota_d');
//Reenvio de Factura
Route::get('facturas/reenviar_factura/{id}', 'FacturacionController@reenviar_factura');
//Reenvio de Recibo
Route::get('facturas/reenviar_recibo/{id}', 'FacturacionController@reenviar_recibo');
//Estadisticas de Facturacion
Route::get('facturas/estadisticas/{id}', 'FacturacionController@estadisticas');
//Comprobantes pendientes de facturación
Route::get('facturas/pendientes_facturacion/{id}', 'FacturacionController@pendientes_facturacion');
//Generar Nota de Crédito
Route::post('facturas/generar_nota_credito/{id}', 'FacturacionController@generar_nota_credito');
//Generar Nota de Débito
Route::post('facturas/generar_nota_debito/{id}', 'FacturacionController@generar_nota_debito');
//Generar Nota de Crédito Directa
Route::post('facturas/agregar_nota_credito/{id}', 'FacturacionController@nota_credito');
//Ver Nota de Crédito 
Route::post('facturas/ver_nota_credito/{id}', 'FacturacionController@ver_nota_credito');
//Ver Factura
Route::post('facturas/ver_factura_emitida/{id}', 'FacturacionController@ver_factura_emitida');
//Ver Modelo de Factura
Route::get('facturas/ver_modelo_factura/{id}', 'FacturacionController@ver_modelo_factura');
//Generar Factura
Route::post('facturas/generar_factura/{id}', 'FacturacionController@generar_factura');
//Cerrar Facturación de Operación de Caja
Route::put('facturas/cerrar_factura/{id}', 'FacturacionController@cerrar_factura');
//Listado de Lotes de Intereses
Route::get('facturas/lotes_intereses/{id}', 'FacturacionController@lotes_intereses');
//Nuevo Lote Intereses
Route::post('facturas/generar_lote_interes/{id}', 'FacturacionController@generar_lote_intereses');
//Nuevo ver_lote Intereses
Route::get('facturas/ver_lote_interes/{id}', 'FacturacionController@ver_lote_intereses');
//Modelar Lote Intereses
Route::get('facturas/modelo_lote_interes/{id}', 'FacturacionController@modelo_lote_intereses');
//Listado de Comprobantes
Route::get('facturas/tipos_comprobantes/{id}', 'FacturacionController@tipos_comprobante');
//Solicitud de Libro de Iva
Route::get('facturas/consulta_libro_iva/{id}', 'FacturacionController@consulta_libro_iva');
//Generación de Libro de Iva
Route::post('facturas/generacion_libro_iva/{id}', 'FacturacionController@generacion_libro_iva');
//Generación de Libro de Iva Alicuotas
Route::post('facturas/generacion_libro_iva_alic/{id}', 'FacturacionController@generacion_libro_iva_alicuotas');
//Ver Modelo de Facturas Diarias
Route::get('facturas/ver_modelo_facturas_diarias/{id}', 'FacturacionController@ver_modelo_facturas_diarias');
//Generar Facturas en Lote
Route::post('facturas/generar_lote_facturas/{id}', 'FacturacionController@generar_lote_facturas');


//Ruteo Lista de deudeores OK
Route::get('deudores/listado/{id}', 'DeudoresController@listado');
//Ruteo Lista de comunicaciones enviadas OK
Route::get('deudores/comunicaciones/{id}', 'DeudoresController@comunicaciones');
//Ruteo Lista de comunicaciones enviadas a un usuario OK
Route::get('deudores/comunicaciones_dudor/{id}', 'DeudoresController@comunicaciones_deudor');
//Ruteo Lista de medios_comunicacion OK
Route::get('deudores/medios_comunicaciones/{id}', 'DeudoresController@medios_comunicacion');
//Ruteo Envío de Mensaje OK
Route::post('deudores/nuevo_mensaje/{id}', 'DeudoresController@nuevo_mensaje');
//Ruteo Simulación Plan de Deuda OK
Route::post('deudores/simular_plan/{id}', 'DeudoresController@simular_plan');
//Ruteo Enviar por Mail de Plan de Deuda OK
Route::post('deudores/enviar_simulacion_plan/{id}', 'DeudoresController@enviar_simulacion_plan');
//Ruteo Confirmar Plan de Deuda Nuevo
Route::post('deudores/confirmar_plan_nuevo/{id}', 'DeudoresController@confirmar_plan_nuevo');
//Ruteo Confirmar Plan de Deuda Simulado
Route::post('deudores/confirmar_plan_simulado/{id}', 'DeudoresController@confirmar_plan_simulado');
//Ruteo Listado Planes OK
Route::get('deudores/listado_planes/{id}', 'DeudoresController@listado_planes');
//Ruteo Consulta Plan OK
Route::get('deudores/consulta_plan/{id}', 'DeudoresController@consulta_plan');
//Ruteo Borrar Plan
Route::put('deudores/borrar_plan/{id}', 'DeudoresController@borrar_plan');
//Ruteo Parametros PLanes de Pago
Route::get('deudores/parametros_plan/{id}', 'DeudoresController@parametros_plan');

});


Route::get('usuarios', 'UsuariosController@index'); //NO PONER ID_INSTITUCION
//Ruteo login usuario TESTED
Route::post('user/login', 'AuthController@login');
//Route::post('user/login', 'UsuariosController@login'); //NO PONER ID_INSTITUCION

//Route::post('user/refresh', 'AuthController@refresh'); //NO PONER ID_INSTITUCION
Route::post('user/logout', 'AuthController@logout'); //NO PONER ID_INSTITUCION
//Ruteo para las notificaciones Push
Route::post('pusher/{id}', 'PusherController@sendNotification'); //NO PONER ID_INSTITUCION

//Ruteo Alternativo para enviar las notificaciones Push
Route::post('pusher_notification/{id}', 'PusherController@sendNotification_a'); //NO PONER ID_INSTITUCION


//Ruteo ESTUDIANTES
Route::get('responsables/estudiantes_listado/{id}', 'ResponsablesController@estudiantes_listado');