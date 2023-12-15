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

    

    public function agregar_medio_pago($id,$nombre)
    {
        try {

            return $this->Medios_PagoRep->agregar_medio_pago($id,$nombre);

        } catch (Exception $e) {

        }
    }

    public function modificar_medio_pago($id, $nombre,$estado,$id_medio_pago)
    {
        try {

            return $this->Medios_PagoRep->modificar_medio_pago($id, $nombre,$estado,$id_medio_pago);

        } catch (Exception $e) {

        }
    }

    public function activar_medio_pago($id,$id_medio_pago)
    {
        try {

            return $this->Medios_PagoRep->activar_medio_pago($id,$id_medio_pago);

        } catch (Exception $e) {

        }
    }

    public function desactivar_medio_pago($id,$id_medio_pago)
    {
        try {

            return $this->Medios_PagoRep->desactivar_medio_pago($id,$id_medio_pago);

        } catch (Exception $e) {

        }
    }

    public function borrar_medio_pago($id,$id_medio_pago)
    {
        try {

            return $this->Medios_PagoRep->borrar_medio_pago($id,$id_medio_pago);

        } catch (Exception $e) {

        }
    }

    public function mostrar_medio_pago($id)
    {
        try {

            return $this->Medios_PagoRep->mostrar_medio_pago($id);

        } catch (Exception $e) {

        }
    }


    public function ver_medio_pago($id,$id_medio_pago)
    {
        try {

            return $this->Medios_PagoRep->ver_medio_pago($id,$id_medio_pago);

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
