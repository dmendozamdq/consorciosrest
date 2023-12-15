<?php

namespace App\Services;

use App\Repositories\FacEstudiantesRepository;

class FacEstudiantesService
{

    private $FacEstudiantesRep;

    function __construct(FacEstudiantesRepository $FacEstudiantesRep)
    {
        $this->FacEstudiantesRep = $FacEstudiantesRep;
    }

    public function listado_estudiantes($id)
    {
        try {

            return $this->FacEstudiantesRep->listado_estudiantes($id);

        } catch (Exception $e) {

        }
    }

    /*public function lectura_comunicado_a($id)
    {
        try {

            return $this->ComunicadosRep->lectura_comunicado_a($id);

        } catch (Exception $e) {

        }
    }
    */

    /*
    public function lectura_comunicado($id,$tipo,$mail)
    {
        try {

            return $this->ComunicadosRep->lectura_comunicado($id,$tipo,$mail);

        } catch (Exception $e) {

        }
    }
    */

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
