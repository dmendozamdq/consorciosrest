<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BeneficiosService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class BeneficiosController extends Controller
{

    private $BeneficiosService;

    function __construct(BeneficiosService $BeneficiosService)
    {
        $this->BeneficiosService = $BeneficiosService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
    public function agregar($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->BeneficiosService->agregar($id, $data['nombre'], $data['id_categoria'], $data['tipo'], $data['descuento'], $data['aplica_total'], $data['conceptos']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function modificar($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->BeneficiosService->modificar($id, $data['id'],$data['nombre'], $data['id_categoria'], $data['tipo'], $data['descuento'], $data['aplica_total'], $data['conceptos']);

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
        $informe = $this->BeneficiosService->borrar($id, $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function listado($id)
    {
        $data = $this->BeneficiosService->listado($id);
        return response()->json([
            'success' => true,
            'data'    => $data,
            'messages' => ''
        ]);
    }

    public function ver($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->BeneficiosService->ver($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function ver_alumno($id, Request $request)
    {
        $data = $request->all();

       
        $informe = $this->BeneficiosService->ver_alumno($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    

    public function asignar($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->BeneficiosService->asignar($id, $data['id'], $data['id_alumno'], $data['id_usuario'], $data['vencimiento']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function modificar_asignacion($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->BeneficiosService->modificar_asignacion($id, $data['id'], $data['vencimiento']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function borrar_asignacion($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->BeneficiosService->borrar_asignacion($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function suspender_asignacion($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->BeneficiosService->suspender_asignacion($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function reactivar_asignacion($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->BeneficiosService->reactivar_asignacion($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    

}
