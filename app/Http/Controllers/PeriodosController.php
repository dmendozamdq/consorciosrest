<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PeriodosService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class PeriodosController extends Controller
{

    private $PeriodosService;

    function __construct(PeriodosService $PeriodosService)
    {
        $this->PeriodosService = $PeriodosService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     /*
    public function agregar_periodo($id, Request $request)
    {

        $data = $request->all();
        $response = $this->PeriodosService->agregar_periodo($id, $data['nombre'], $data['id_ciclo'], $data['subperiodo']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

   
    }
*/
public function agregar_periodo($id, Request $request)
{
    //return $this->ReportesService->general($id);

    $data = $request->all();
    $response = $this->PeriodosService->agregar_periodo($id, $data['nombre'], $data['id_ciclo'], $data['subperiodo']);
    //return $this->Medios_PagoService->agregar_medio_pago($nombre);
    return response()->json([
        'success' => true,
        'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
        'messages' => ''
    ]);

}

    public function modificar_periodo($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();
        $response = $this->PeriodosService->modificar_periodo($id, $data['nombre'], $data['id_ciclo'], $data['id']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

    }

    public function agregar_subperiodo($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();
        $response = $this->PeriodosService->agregar_subperiodo($id, $data['mes'], $data['interes'], $data['fecha'], $data['id']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

    }

    public function modificar_subperiodo($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();
        $response = $this->PeriodosService->modificar_subperiodo($id, $data['mes'], $data['interes'], $data['fecha'], $data['id']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

    }

    public function borrar_subperiodo($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();
        $response = $this->PeriodosService->borrar_subperiodo($id, $data['id']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

    }

    public function borrar_periodo($id, Request $request)
    {
        //return $this->ReportesService->general($id);
        $data = $request->all();
        $response = $this->PeriodosService->borrar_periodo($id, $data['id']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

    }

    

    

    public function listado_periodos($id)
    {
        //return $this->ReportesService->general($id);
        $data =  $this->PeriodosService->listado_periodos($id);
        return response()->json([
            'success' => true,
            'data'    => $data,
            'messages' => ''
        ]);
        //return $this->ConceptosService->mostrar_conceptos($data);
    }

    public function mostrar_periodo($id, Request $request)
    {
        $data = $request->all();
        $response = $this->PeriodosService->mostrar_periodo($id, $data['id']);
        //return $this->Medios_PagoService->agregar_medio_pago($nombre);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
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
