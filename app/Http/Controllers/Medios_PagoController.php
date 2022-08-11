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
     
    public function agregar_medio_pago($nombre)
    {

        //$data = $request->all();
        return $this->Medios_PagoService->agregar_medio_pago($nombre);

    }

    public function borrar_medio_pago($id)
    {
        //return $this->ReportesService->general($id);

        $informe = $this->Medios_PagoService->borrar_medio_pago($id);

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
