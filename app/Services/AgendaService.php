<?php

namespace App\Services;

use App\Repositories\AgendaRepository;

class AgendaService
{

    private $AgendaRep;

    function __construct(AgendaRepository $AgendaRep)
    {
        $this->AgendaRep = $AgendaRep;
    }

    public function general($id, $id_institucion)
    {
        try {

            return $this->AgendaRep->general($id, $id_institucion);

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
