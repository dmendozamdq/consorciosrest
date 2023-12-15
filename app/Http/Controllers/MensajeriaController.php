<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MensajeriaService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class MensajeriaController extends Controller
{

    private $MensajeriaService;

    function __construct(MensajeriaService $MensajeriaService)
    {
        $this->MensajeriaService = $MensajeriaService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     public function general($id, Request $request)
     {

        $data = $request->all();

        $response = $this->MensajeriaService->general($id, $data['email'], $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($response, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

          

     }
    

    public function lectura_mensajeria($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->MensajeriaService->lectura_mensajeria($id, $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function historial_mensajes($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->MensajeriaService->historial_mensajes($id, $data['id_institucion']);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);

    }
    public function enviar_chat($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->MensajeriaService->enviar_chat($id, $data['chat'], $data['id_institucion']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);

    }

    public function nuevo_chat($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->MensajeriaService->nuevo_chat($id, $data['id_alumno'], $data['email'], $data['chat'], $data['id_institucion']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);
    }

    public function destinatarios_chats($id, Request $request)
    {
        //return $this->ReportesService->general($id);

        $data = $request->all();

        $informe = $this->MensajeriaService->destinatarios_chats($id, $data['id_institucion']);

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
