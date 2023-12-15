<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ConceptosService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class ConceptosController extends Controller
{

    private $ConceptosService;

    function __construct(ConceptosService $ConceptosService)
    {
        $this->ConceptosService = $ConceptosService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
    public function agregar_conceptos($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->ConceptosService->agregar_conceptos($id, $data['nombre'], $data['importe'], $data['alcance']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function modificar_conceptos($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->ConceptosService->modificar_conceptos($id, $data['nombre'], $data['importe'], $data['alcance'], $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function borrar_conceptos($id, Request $request)
    {
        $data = $request->all();

        //$informe = $this->ConceptosService->borrar_conceptos($id);
        $informe = $this->ConceptosService->borrar_conceptos($id, $data['id']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function mostrar_conceptos($id)
    {
        $data = $this->ConceptosService->mostrar_conceptos($id);
        return response()->json([
            'success' => true,
            'data'    => $data,
            'messages' => ''
        ]);
    }

    public function mostrar_concepto($id, Request $request)
    {
        $data = $request->all();

        //$data = $this->ConceptosService->mostrar_concepto($id);
        $informe = $this->ConceptosService->mostrar_concepto($id, $data['id']);
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
