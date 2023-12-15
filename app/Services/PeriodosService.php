<?php

namespace App\Services;

use App\Repositories\PeriodosRepository;

class PeriodosService
{

    private $PeriodosRep;

    function __construct(PeriodosRepository $PeriodosRep)
    {
        $this->PeriodosRep = $PeriodosRep;
    }

    

    public function agregar_periodo($id, $nombre, $ciclo, $subperiodo)
    {
        try {

            return $this->PeriodosRep->agregar_periodo($id, $nombre, $ciclo, $subperiodo);

        } catch (Exception $e) {

        }
    }

    public function modificar_periodo($id,$nombre, $ciclo, $id_periodo)
    {
        try {

            return $this->PeriodosRep->modificar_periodo($id,$nombre, $ciclo, $id_periodo);

        } catch (Exception $e) {

        }
    }

    public function agregar_subperiodo($id, $mes, $interes, $vencimiento, $id_periodo)
    {
        try {

            return $this->PeriodosRep->agregar_subperiodo($id, $mes, $interes, $vencimiento, $id_periodo);

        } catch (Exception $e) {

        }
    }

    public function modificar_subperiodo($id, $mes, $interes, $vencimiento, $id_subperiodo)
    {
        try {

            return $this->PeriodosRep->modificar_subperiodo($id, $mes, $interes, $vencimiento, $id_subperiodo);

        } catch (Exception $e) {

        }
    }

    public function borrar_subperiodo($id, $id_subperiodo)
    {
        try {

            return $this->PeriodosRep->borrar_subperiodo($id, $id_subperiodo);

        } catch (Exception $e) {

        }
    }

    public function borrar_periodo($id, $id_periodo)
    {
        try {

            return $this->PeriodosRep->borrar_periodo($id, $id_periodo);

        } catch (Exception $e) {

        }
    }


    public function listado_periodos($id)
    {
        try {

            return $this->PeriodosRep->listado_periodos($id);

        } catch (Exception $e) {

        }
    }

    public function mostrar_periodo($id, $id_periodo)
    {
        try {

            return $this->PeriodosRep->mostrar_periodo($id, $id_periodo);

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
