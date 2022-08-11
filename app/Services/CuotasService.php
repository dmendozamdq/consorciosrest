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

    public function general($id,$mail)
    {
        try {

            return $this->CuotasRep->general($id,$mail);

        } catch (Exception $e) {

        }
    }

    public function lectura_cuota($id)
    {
        try {

            return $this->CuotasRep->lectura_cuotas($id);

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
