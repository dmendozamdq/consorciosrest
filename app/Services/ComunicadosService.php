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

    public function general($id,$mail)
    {
        try {

            return $this->ComunicadosRep->general($id,$mail);

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

    public function lectura_comunicado($id,$tipo,$mail)
    {
        try {

            return $this->ComunicadosRep->lectura_comunicado($id,$tipo,$mail);

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
