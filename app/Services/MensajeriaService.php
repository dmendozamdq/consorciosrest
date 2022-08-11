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

    public function general($id,$mail)
    {
        try {

            return $this->MensajeriaRep->general($id,$mail);

        } catch (Exception $e) {

        }
    }
    public function lectura_mensajeria($id)
    {
        try {

            return $this->MensajeriaRep->lectura_mensajeria($id);

        } catch (Exception $e) {

        }
    }

    public function historial_mensajes($id)
    {
        try {

            return $this->MensajeriaRep->historial_mensajes($id);

        } catch (Exception $e) {

        }
    }

    public function enviar_chat($id,$chat)
    {
        try {

            return $this->MensajeriaRep->enviar_chat($id,$chat);

        } catch (Exception $e) {

        }
    }

    public function nuevo_chat($id,$id_alumno,$mail,$chat)
    {
        try {

            return $this->MensajeriaRep->nuevo_chat($id,$id_alumno,$mail,$chat);

        } catch (Exception $e) {

        }
    }

    public function destinatarios_chats($id)
    {
        try {

            return $this->MensajeriaRep->destinatarios_chats($id);

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
