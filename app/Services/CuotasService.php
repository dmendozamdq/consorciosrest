<?php

namespace App\Services;

use App\Repositories\CuotasRepository;

class CuotasService
{

    private $CuotasRep;

    function __construct(CuotasRepository $CuotasRep)
    {
        $this->CuotasRep = $CuotasRep;
    }

    public function general($id,$mail, $id_institucion)
    {
        try {

            return $this->CuotasRep->general($id,$mail, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lectura_cuota($id, $id_institucion)
    {
        try {

            return $this->CuotasRep->lectura_cuotas($id, $id_institucion);

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
