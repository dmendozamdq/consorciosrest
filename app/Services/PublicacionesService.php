<?php

namespace App\Services;

use App\Repositories\PublicacionesRepository;

class PublicacionesService
{

    private $PublicacionesRep;

    function __construct(PublicacionesRepository $PublicacionesRep)
    {
        $this->PublicacionesRep = $PublicacionesRep;
    }

    public function general($id,$mail, $id_institucion)
    {
        try {

            return $this->PublicacionesRep->general($id,$mail, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lectura_publicacion($id, $id_institucion)
    {
        try {

            return $this->PublicacionesRep->lectura_publicacion($id, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function contenido_publicacion($id, $id_institucion)
    {
        try {

            return $this->PublicacionesRep->contenido_publicacion($id, $id_institucion);

        } catch (Exception $e) {

        }
    }



}
