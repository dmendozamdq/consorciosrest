<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PublicacionesService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;
use Response;


class PublicacionesController extends Controller
{

    private $PublicacionesService;

    function __construct(PublicacionesService $PublicacionesService)
    {
        $this->PublicacionesService = $PublicacionesService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     public function general($id, Request $request)
     {
         $data = $request->all();

         $response = $this->PublicacionesService->general($id, $data['email']);

         return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);


     }

    public function lectura_publicacion($id)
    {
        //return $this->ReportesService->general($id);

        $informe = $this->PublicacionesService->lectura_publicacion($id);


        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function contenido_publicacion($id)
    {
        //return $this->ReportesService->general($id);

        $informe = $this->PublicacionesService->contenido_publicacion($id);

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
