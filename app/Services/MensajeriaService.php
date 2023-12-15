<?php

namespace App\Services;

use App\Repositories\MensajeriaRepository;

class MensajeriaService
{

    private $MensajeriaRep;

    function __construct(MensajeriaRepository $MensajeriaRep)
    {
        $this->MensajeriaRep = $MensajeriaRep;
    }

    public function general($id,$mail, $id_institucion)
    {
        try {

            return $this->MensajeriaRep->general($id,$mail, $id_institucion);

        } catch (Exception $e) {

        }
    }
    public function lectura_mensajeria($id, $id_institucion)
    {
        try {

            return $this->MensajeriaRep->lectura_mensajeria($id, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function historial_mensajes($id, $id_institucion)
    {
        try {

            return $this->MensajeriaRep->historial_mensajes($id, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function enviar_chat($id,$chat, $id_institucion)
    {
        try {

            return $this->MensajeriaRep->enviar_chat($id,$chat, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function nuevo_chat($id,$id_alumno,$mail,$chat, $id_institucion)
    {
        try {

            return $this->MensajeriaRep->nuevo_chat($id,$id_alumno,$mail,$chat, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function destinatarios_chats($id, $id_institucion)
    {
        try {

            return $this->MensajeriaRep->destinatarios_chats($id, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function show($id)
    {

    }

    public function store($data)
    {

    }

    public function destroy($id)
    {

    }


    public function update($data, $id)
    {

    }


}
