<?php

namespace App\Services;

use App\Repositories\DeudoresRepository;

class DeudoresService
{

    private $DeudoresRep;

    function __construct(DeudoresRepository $DeudoresRep)
    {
        $this->DeudoresRep = $DeudoresRep;
    }
    
   
    public function listado($id)
    {
        try {

            return $this->DeudoresRep->listado($id);

        } catch (Exception $e) {

        }
    }

    public function ver($id, $id_item)
    {
        try {

            return $this->DeudoresRep->ver($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function comunicaciones_deudor($id, $id_item)
    {
        try {

            return $this->DeudoresRep->comunicaciones_deudor($id, $id_item);

        } catch (Exception $e) {

        }
    }
    
    public function comunicaciones($id)
    {
        try {

            return $this->DeudoresRep->comunicaciones($id);

        } catch (Exception $e) {

        }
    }

    public function medios_comunicacion($id)
    {
        try {

            return $this->DeudoresRep->medios_comunicacion($id);

        } catch (Exception $e) {

        }
    }

    public function nuevo_mensaje($id, $id_item, $id_medio, $id_usuario, $mensaje)
    {
        try {

            return $this->DeudoresRep->nuevo_mensaje($id, $id_item, $id_medio, $id_usuario, $mensaje);

        } catch (Exception $e) {

        }
    }

    public function simular_plan($id, $id_item, $importe, $detalle, $cuotas, $interes, $Dia_Tentativo, $Interes_desde_Cuota)
    {
        try {

            return $this->DeudoresRep->simular_plan($id, $id_item, $importe, $detalle, $cuotas, $interes, $Dia_Tentativo, $Interes_desde_Cuota);

        } catch (Exception $e) {

        }
    }

    public function enviar_simulacion_plan($id, $id_item, $importe, $detalle, $cuotas, $interes, $Dia_Tentativo, $Interes_desde_Cuota, $metodo, $id_usuario)
    {
        try {

            return $this->DeudoresRep->enviar_simulacion_plan($id, $id_item, $importe, $detalle, $cuotas, $interes, $Dia_Tentativo, $Interes_desde_Cuota, $metodo, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function confirmar_plan_simulado($id, $id_item, $id_plan, $id_usuario, $cancelacion, $generacion)
    {
        try {

            return $this->DeudoresRep->confirmar_plan_simulado($id, $id_item, $id_plan, $id_usuario, $cancelacion, $generacion);

        } catch (Exception $e) {

        }
    }

    public function confirmar_plan_nuevo($id, $id_item, $importe, $detalle, $cuotas, $interes,$Dia_Tentativo, $Interes_desde_Cuota, $metodo, $id_usuario, $cancelacion, $generacion)
    {
        try {

            return $this->DeudoresRep->confirmar_plan_nuevo($id, $id_item, $importe, $detalle, $cuotas, $interes,$Dia_Tentativo, $Interes_desde_Cuota, $metodo, $id_usuario, $cancelacion, $generacion);

        } catch (Exception $e) {

        }
    }

    public function listado_planes($id)
    {
        try {

            return $this->DeudoresRep->listado_planes($id);

        } catch (Exception $e) {

        }
    }

    public function consulta_plan($id, $id_item)
    {
        try {

            return $this->DeudoresRep->consulta_plan($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function borrar_plan($id, $id_item, $id_usuario)
    {
        try {

            return $this->DeudoresRep->borrar_plan($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function parametros_plan($id)
    {
        try {

            return $this->DeudoresRep->parametros_plan($id);

        } catch (Exception $e) {

        }
    }

    public function ver_cc($id, $id_item, $tipo)
    {
        try {

            return $this->DeudoresRep->ver_cc($id, $id_item, $tipo);

        } catch (Exception $e) {

        }
    }

    public function saldo($id, $id_item)
    {
        try {

            return $this->DeudoresRep->saldo($id, $id_item);

        } catch (Exception $e) {

        }
    }



    public function estadistica_vinculacion($id)
    {
        try {

            return $this->DeudoresRep->estadistica_vinculacion($id);

        } catch (Exception $e) {

        }
    }

  


}
