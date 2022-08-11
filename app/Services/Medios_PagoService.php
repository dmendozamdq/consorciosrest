<?php

namespace App\Services;

use App\Repositories\Medios_PagoRepository;

class Medios_PagoService
{

    private $Medios_PagoRep;

    function __construct(Medios_PagoRepository $Medios_PagoRep)
    {
        $this->Medios_PagoRep = $Medios_PagoRep;
    }

    

    public function agregar_medio_pago($nombre)
    {
        try {

            return $this->Medios_PagoRep->agregar_medio_pago($nombre);

        } catch (Exception $e) {

        }
    }

    public function borrar_medio_pago($id)
    {
        try {

            return $this->Medios_PagoRep->borrar_medio_pago($id);

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
