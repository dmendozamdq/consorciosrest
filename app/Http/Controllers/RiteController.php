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
         return $this->RiteService->general($id, $data['email']);
         //return $this->RiteService->general($id);

     }

    public function lectura_rite($id)
    {
        $informe = $this->RiteService->lectura_rite($id);

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
