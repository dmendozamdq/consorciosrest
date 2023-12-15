<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Medios_PagoService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class Medios_PagoController extends Controller
{

    private $Medios_PagoService;

    function __construct(Medios_PagoService $Medios_PagoService)
    {
        $this->Medios_PagoService = $Medios_PagoService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
    public function agregar_medio_pago($id, Request $request)
    {

        $data = $request->all();
        $response = $this->Medios_PagoService->agregar_medio_pago($id, $data['nombre']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);


    }

    
    public function modificar_medio_pago($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->Medios_PagoService->modificar_medio_pago($id,  $data['nombre'],$data['estado'],$data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function activar_medio_pago($id, Request $request)
    {
        $data = $request->all();

        $informe = $this->Medios_PagoService->activar_medio_pago($id, $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function desactivar_medio_pago($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->Medios_PagoService->desactivar_medio_pago($id, $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function borrar_medio_pago($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->Medios_PagoService->borrar_medio_pago($id, $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    
    public function mostrar_medio_pago($id)
    {
        //$data = $request->all();       
        $informe = $this->Medios_PagoService->mostrar_medio_pago($id);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
        
    }
    
    public function ver_medio_pago($id, Request $request)
    {
        $data = $request->all();  
        $informe = $this->Medios_PagoService->ver_medio_pago($id, $data['id']);
        
        
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
        //return $this->Medios_PagoService->mostrar_medio_pago($data);
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
