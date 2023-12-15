<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeudoresService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class DeudoresController extends Controller
{

    private $DeudoresService;

    function __construct(DeudoresService $DeudoresService)
    {
        $this->DeudoresService = $DeudoresService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */

    

   


    public function listado($id)
    {
        $data = $this->DeudoresService->listado($id);
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
        $informe = $this->DeudoresService->ver($id, $data['id']);
        return response()->json([
            'success' => true,

            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function comunicaciones_deudor($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->comunicaciones_deudor($id, $data['id']);
        return response()->json([
            'success' => true,

            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function comunicaciones($id)
    {
        
        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->comunicaciones($id);
        return response()->json([
            'success' => true,

            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    public function medios_comunicacion($id)
    {
        //$data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->medios_comunicacion($id);
        return response()->json([
            'success' => true,

            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function nuevo_mensaje($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->nuevo_mensaje($id, $data['id'], $data['id_medio'], $data['id_usuario'], $data['mensaje']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }


    public function simular_plan($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->simular_plan($id, $data['id'], $data['importe'], $data['detalle'], $data['cuotas'], $data['interes'], $data['dia_tentativo'], $data['interes_desde_cuota']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function enviar_simulacion_plan($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->enviar_simulacion_plan($id, $data['id'], $data['importe'], $data['detalle'], $data['cuotas'], $data['interes'], $data['dia_tentativo'], $data['interes_desde_cuota'], $data['metodo'], $data['id_usuario']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function confirmar_plan_simulado($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->confirmar_plan_simulado($id, $data['id'], $data['id_plan'], $data['id_usuario'], $data['cancelacion'], $data['generacion']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function confirmar_plan_nuevo($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->confirmar_plan_nuevo($id, $data['id'], $data['importe'], $data['detalle'], $data['cuotas'], $data['interes'], $data['dia_tentativo'], $data['interes_desde_cuota'], $data['metodo'], $data['id_usuario'], $data['cancelacion'], $data['generacion']);
                                                                                                                                            
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function listado_planes($id)
    {
        $informe = $this->DeudoresService->listado_planes($id);
        return response()->json([
            'success' => true,

            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    
    public function consulta_plan($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->consulta_plan($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function borrar_plan($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->borrar_plan($id, $data['id'], $data['id_usuario']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    public function parametros_plan($id)
    {
        $informe = $this->DeudoresService->parametros_plan($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    



    public function ver_cc($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->DeudoresService->ver_cc($id, $data['id'], $data['tipo']);
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
        $informe = $this->DeudoresService->saldo($id, $data['id']);
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
