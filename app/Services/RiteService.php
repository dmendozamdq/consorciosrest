<?php

namespace App\Services;

use App\Repositories\RiteRepository;

class RiteService
{

    private $RiteRep;

    function __construct(RiteRepository $RiteRep)
    {
        $this->RiteRep = $RiteRep;
    }

    public function general($id,$mail, $id_institucion)
    {
        try {

            return $this->RiteRep->general($id,$mail, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lectura_rite($id, $id_institucion)
    {
        try {

            return $this->RiteRep->lectura_rite($id, $id_institucion);

        } catch (Exception $e) {

        }
    }



}
