<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HomeService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class HomeController extends Controller
{

    private $HomeService;

    function __construct(HomeService $HomeService)
    {
        $this->HomeService = $HomeService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
    
     public function administracion($id)
     {
         //return $this->ReportesService->general($id);
         $data =  $this->HomeService->administracion($id);
         return response()->json([
             'success' => true,
             'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
             'messages' => ''
         ]);
         //return $this->ConceptosService->mostrar_conceptos($data);
     }
    
    public function total_facturado($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->total_facturado($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function total_cobrado($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->total_cobrado($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function detalle_cobranza($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->detalle_cobranza($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function total_estudiantes($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->total_estudiantes($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function cobranza_evolutiva($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->cobranza_evolutiva($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    public function sintesis_medios_pago($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->sintesis_medios_pago($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    public function cobranzas_recientes($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->cobranzas_recientes($id,  $data['filtro']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function notificaciones($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->HomeService->notificaciones($id,  $data['id_usuario']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Insert de Reportes
     *
     * @param  Request  $request
     * @return Json Response [success, data, messages]
     */
    public function store(Request $request)
    {

    }

    /**
     * Select de Reportes
     *
     * @param  $id
     * @return Json Response [success, data, messages]
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update de Reportes
     *
     * @param  Request  $request, $id
     * @return Json Response [success, data, messages]
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Delete de Reportes
     *
     * @param  $id
     * @return Json Response [success, data, messages]
     */
    public function destroy($id)
    {

    }

}
