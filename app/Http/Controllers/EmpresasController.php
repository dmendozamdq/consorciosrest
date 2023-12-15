<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmpresasService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class EmpresasController extends Controller
{

    private $EmpresasService;

    function __construct(EmpresasService $EmpresasService)
    {
        $this->EmpresasService = $EmpresasService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
    public function agregar_empresa($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->EmpresasService->agregar_empresa($id, $data['nombre'], $data['tipo_documento'], $data['documento'], $data['telefono'], $data['email'], $data['user'], $data['password'],$data['cuit'],$data['iibb'],$data['inicio_actividad'],$data['pto_vta']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function modificar_empresa($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->EmpresasService->modificar_empresa($id, $data['id_empresa'],$data['nombre'], $data['tipo_documento'], $data['documento'], $data['telefono'], $data['email'], $data['user'], $data['password'],$data['cuit'],$data['iibb'],$data['inicio_actividad'],$data['pto_vta']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function borrar_empresa($id, Request $request)
    {
        $data = $request->all();

        //$informe = $this->ConceptosService->borrar_conceptos($id);
        $informe = $this->EmpresasService->borrar_empresa($id, $data['id_empresa']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function listado_empresas($id)
    {
        $data = $this->EmpresasService->listado_empresas($id);
        return response()->json([
            'success' => true,
            'data'    => $data,
            'messages' => ''
        ]);
    }

    public function ver_empresa($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->EmpresasService->ver_empresa($id, $data['id_empresa']);
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
