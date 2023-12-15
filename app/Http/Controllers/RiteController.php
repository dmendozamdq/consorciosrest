<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RiteService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class RiteController extends Controller
{

    private $RiteService;

    function __construct(RiteService $RiteService)
    {
        $this->RiteService = $RiteService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     public function general($id, Request $request)
     {

         $data = $request->all();
         return $this->RiteService->general($id, $data['email'],  $data['id_institucion']);
         //return $this->RiteService->general($id);

     }

    public function lectura_rite($id, Request $request)
    {
        $informe = $this->RiteService->lectura_rite($id,  $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }


}
