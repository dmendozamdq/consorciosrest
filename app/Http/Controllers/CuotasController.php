<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CuotasService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class CuotasController extends Controller
{

    private $CuotasService;

    function __construct(CuotasService $CuotasService)
    {
        $this->CuotasService = $CuotasService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     public function general($id, Request $request)
     {

         $data = $request->all();

         return $this->CuotasService->general($id, $data['email'],  $data['id_institucion']);

     }

    public function lectura_cuota($id, Request $request)
    {
        $data = $request->all();
        
        $informe = $this->CuotasService->lectura_cuota($id,  $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }


}
