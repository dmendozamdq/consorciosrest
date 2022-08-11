<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PusherService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;
use Response;


class PusherController extends Controller
{

    private $PusherService;

    function __construct(PusherService $PusherService)
    {
        $this->PusherService = $PusherService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * 
     * @return response()
     */
     public function sendNotification($id, Request $request)
     {
        $data = $request->all();

        $informe = $this->PusherService->sendNotification($id, $data['token'],  $data['title'],  $data['body']);


        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''


        ]);

     }

     public function sendNotification_a($id, Request $request)
     {
        $data = $request->all();

        $informe = $this->PusherService->sendNotification_a($id, $data['token'],  $data['title'],  $data['body']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
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
