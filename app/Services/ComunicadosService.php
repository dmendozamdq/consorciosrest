<?php

namespace App\Services;

use App\Repositories\ComunicadosRepository;

class ComunicadosService
{

    private $ComunicadosRep;

    function __construct(ComunicadosRepository $ComunicadosRep)
    {
        $this->ComunicadosRep = $ComunicadosRep;
    }

    public function general($id,$mail,$id_institucion)
    {
        try {

            return $this->ComunicadosRep->general($id,$mail,$id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lectura_comunicado_a($id)
    {
        try {

            return $this->ComunicadosRep->lectura_comunicado_a($id);

        } catch (Exception $e) {

        }
    }

    public function lectura_comunicado($id,$tipo,$mail,$id_institucion)
    {
        try {

            return $this->ComunicadosRep->lectura_comunicado($id,$tipo,$mail,$id_institucion);

        } catch (Exception $e) {

        }
    }


}
