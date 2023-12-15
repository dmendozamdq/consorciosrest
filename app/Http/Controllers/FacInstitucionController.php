<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacInstitucionService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;
use Response;


class FacInstitucionController extends Controller
{

    private $FacInstitucionService;

    function __construct(FacInstitucionService $FacInstitucionService)
    {
        $this->FacInstitucionService = $FacInstitucionService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     public function listado_niveles($id)
     {
        $response = $this->FacInstitucionService->listado_niveles($id);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 
        
        
     }

     public function listado_cursos($id, Request $request)
     {
        $data = $request->all();
        $response  = $this->FacInstitucionService->listado_cursos($id, $data['id_institucion']);
        
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 
        
        
     }
    
/*
  public function lectura_comunicado($id, Request $request)
    {
        //return $this->ReportesService->general($id);
        $data = $request->all();

        $informe = $this->ComunicadosService->lectura_comunicado($id, $data['tipo'],  $data['email']);


        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''


        ]);
    }
*/

/*
public function lectura_comunicado_a($id)
{
    //return $this->ReportesService->general($id);


    $informe = $this->ComunicadosService->lectura_comunicado_a($id);


    return response()->json([
        'success' => true,
        'data'    => $informe,
        'messages' => ''
    ]);
}
*/

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
