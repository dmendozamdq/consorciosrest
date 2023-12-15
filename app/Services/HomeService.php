<?php

namespace App\Services;

use App\Repositories\HomeRepository;

class HomeService
{

    private $HomeRep;

    function __construct(HomeRepository $HomeRep)
    {
        $this->HomeRep = $HomeRep;
    }

    public function administracion($id)
    {
        try {

            return $this->HomeRep->administracion($id);

        } catch (Exception $e) {

        }
    }

    public function total_facturado($id, $filtro)
    {
        try {

            return $this->HomeRep->total_facturado($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function total_cobrado($id, $filtro)
    {
        try {

            return $this->HomeRep->total_cobrado($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function total_cobranza($id, $filtro)
    {
        try {

            return $this->HomeRep->total_cobranza($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function detalle_cobranza($id, $filtro)
    {
        try {

            return $this->HomeRep->detalle_cobranza($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function total_estudiantes($id, $filtro)
    {
        try {

            return $this->HomeRep->total_estudiantes($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function cobranza_evolutiva($id, $filtro)
    {
        try {

            return $this->HomeRep->cobranza_evolutiva($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function sintesis_medios_pago($id, $filtro)
    {
        try {

            return $this->HomeRep->sintesis_medios_pago($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function cobranzas_recientes($id, $filtro)
    {
        try {

            return $this->HomeRep->cobranzas_recientes($id, $filtro);

        } catch (Exception $e) {

        }
    }

    public function notificaciones($id, $id_usuario)
    {
        try {

            return $this->HomeRep->notificaciones($id, $id_usuario);

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
