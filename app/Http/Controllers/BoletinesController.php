<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BoletinesService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class BoletinesController extends Controller
{

    private $BoletinesService;

    function __construct(BoletinesService $BoletinesService)
    {
        $this->BoletinesService = $BoletinesService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     public function general($id, Request $request)
     {

         $data = $request->all();
         return $this->BoletinesService->general($id, $data['email'],  $data['id_institucion']);

     }

    public function lectura_boletin($id, Request $request)
    {

        $data = $request->all();

        $informe = $this->BoletinesService->lectura_boletin($id,  $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }


}
