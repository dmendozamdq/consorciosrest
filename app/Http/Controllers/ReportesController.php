<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportesService;
use Illuminate\Support\Facades\Validator;
use App\Models\Reporte;

class ReportesController extends Controller
{

    private $ReportesService;

    function __construct(ReportesService $ReportesService)
    {
        $this->ReportesService = $ReportesService;
    }

    public function lista_informes($id, Request $request)
    {
        $data = $request->all();

        $response = $this->ReportesService->lista_informes($id, $data['email']);

        return response()->json([
           'success' => true,
           'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
           'messages' => ''
       ]);
    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
    }

    public function lectura_informe($id, Request $request)
      {
          //return $this->ReportesService->general($id);
          $data = $request->all();

          $informe = $this->ReportesService->lectura_informe($id, $data['email']);


          return response()->json([
              'success' => true,
              'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
              'messages' => ''


          ]);
      }

    public function general($id)
    {
        //return $this->ReportesService->general($id);

        $informe = $this->ReportesService->general($id);

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
