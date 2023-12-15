<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResponsablesService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class ResponsablesController extends Controller
{

    private $ResponsablesService;

    function __construct(ResponsablesService $ResponsablesService)
    {
        $this->ResponsablesService = $ResponsablesService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */

    public function agregar($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->ResponsablesService->agregar($id, $data['nombre'], $data['apellido'], $data['dni'], $data['domicilio'], $data['telefono'], $data['email'], $data['id_usuario'], $data['nombre_fiscal'],$data['cuit'],$data['tipo_factura'],$data['saldo'],$data['plan'],$data['facturable'],$data['condicion_iva']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);

    }

    public function modificar($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->ResponsablesService->modificar($id, $data['id'],$data['nombre'], $data['apellido'], $data['dni'], $data['domicilio'], $data['telefono'], $data['email'], $data['id_usuario'], $data['nombre_fiscal'],$data['cuit'],$data['tipo_factura'],$data['saldo'],$data['plan'],$data['facturable'],$data['condicion_iva']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function borrar($id, Request $request)
    {
        $data = $request->all();

        //$informe = $this->ConceptosService->borrar_conceptos($id);
        $informe = $this->ResponsablesService->borrar($id, $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function listado($id)
    {
        $data = $this->ResponsablesService->listado($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function ver($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->ver($id, $data['id']);
        return response()->json([
            'success' => true,

            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }



    public function vincular_estudiante($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->vincular_estudiante($id, $data['id'], $data['id_alumno'], $data['id_usuario']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function desvincular_estudiante($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->desvincular_estudiante($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function estudiantes_vinculables($id)
    {
        $data = $this->ResponsablesService->estudiantes_vinculables($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function estudiantes_listado($id)
    {
        $data = $this->ResponsablesService->estudiantes_listado($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function ver_cc($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->ver_cc($id, $data['id'], $data['tipo']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    

    public function saldo($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->saldo($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function interes_gen($id)
    {
        $data = $this->ResponsablesService->interes_gen($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function lista_movimientos_cuenta($id)
    {
        $data = $this->ResponsablesService->lista_movimientos_cuenta($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function cargo_gen($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->cargo_gen($id, $data['id'], $data['id_movimiento'], $data['descripcion'], $data['importe'], $data['id_usuario'], $data['fecha'], $data['id_alumno'], $data['interes'],$data['id_empresa']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function generar_ajuste($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->generar_ajuste($id, $data['id'], $data['id_responsable'], $data['importe'], $data['descripcion'], $data['id_usuario']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function borrar_gen($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ResponsablesService->borrar_gen($id, $data['id'], $data['id_usuario']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }
    
    public function estadistica_vinculacion($id)
    {
        $informe = $this->ResponsablesService->estadistica_vinculacion($id);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function condiciones_iva($id)
    {
        $data = $this->ResponsablesService->condiciones_iva($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


}
