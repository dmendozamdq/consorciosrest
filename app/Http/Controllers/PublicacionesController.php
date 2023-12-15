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

         $response = $this->PublicacionesService->general($id, $data['email'],  $data['id_institucion']);

         return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);


     }

    public function lectura_publicacion($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->PublicacionesService->lectura_publicacion($id, $data['id_institucion']);


        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
    }

    public function contenido_publicacion($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->PublicacionesService->contenido_publicacion($id, $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

}
