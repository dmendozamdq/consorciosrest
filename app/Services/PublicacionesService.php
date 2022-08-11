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

    public function general($id,$mail)
    {
        try {

            return $this->PublicacionesRep->general($id,$mail);

        } catch (Exception $e) {

        }
    }

    public function lectura_publicacion($id)
    {
        try {

            return $this->PublicacionesRep->lectura_publicacion($id);

        } catch (Exception $e) {

        }
    }

    public function contenido_publicacion($id)
    {
        try {

            return $this->PublicacionesRep->contenido_publicacion($id);

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
