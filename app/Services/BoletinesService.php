<?php

namespace App\Services;

use App\Repositories\BoletinesRepository;

class BoletinesService
{

    private $BoletinesRep;

    function __construct(BoletinesRepository $BoletinesRep)
    {
        $this->BoletinesRep = $BoletinesRep;
    }

    public function general($id,$mail, $id_institucion)
    {
        try {

            return $this->BoletinesRep->general($id,$mail, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lectura_boletin($id, $id_institucion)
    {
        try {

            return $this->BoletinesRep->lectura_boletin($id, $id_institucion);

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
