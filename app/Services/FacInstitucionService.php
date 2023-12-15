<?php

namespace App\Services;

use App\Repositories\FacInstitucionRepository;

class FacInstitucionService
{

    private $FacInstitucionRep;

    function __construct(FacInstitucionRepository $FacInstitucionRep)
    {
        $this->FacInstitucionRep = $FacInstitucionRep;
    }

    public function listado_niveles($id)
    {
        try {

            return $this->FacInstitucionRep->listado_niveles($id);

        } catch (Exception $e) {

        }
    }

    public function listado_cursos($id,$id_institucion)
    {
        try {

            return $this->FacInstitucionRep->listado_cursos($id,$id_institucion);

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
