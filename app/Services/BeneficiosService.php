<?php


namespace App\Services;

use App\Repositories\BeneficiosRepository;

class BeneficiosService
{

    private $BeneficiosRep;

    function __construct(BeneficiosRepository $BeneficiosRep)
    {
        $this->BeneficiosRep = $BeneficiosRep;
    }
    public function agregar($id,$nombre,$id_categoria,$tipo,$descuento,$aplica,$conceptos)
    {
        try {

           
            return $this->BeneficiosRep->agregar($id,$nombre,$id_categoria,$tipo,$descuento,$aplica,$conceptos);

        } catch (Exception $e) {

        }
    }

    public function modificar($id,$id_item,$nombre,$id_categoria,$tipo,$descuento,$aplica,$conceptos)
    {
        try {

            return $this->BeneficiosRep->modificar($id,$id_item,$nombre,$id_categoria,$tipo,$descuento,$aplica,$conceptos);

        } catch (Exception $e) {

        }
    }

    public function borrar($id, $id_item)
    {
        try {

            return $this->BeneficiosRep->borrar($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function listado($id)
    {
        try {

            return $this->BeneficiosRep->listado($id);

        } catch (Exception $e) {

        }
    }
    
    public function ver($id, $id_item)
    {
        try {

            return $this->BeneficiosRep->ver($id, $id_item);

        } catch (Exception $e) {

        }
    }

     
    public function ver_alumno($id, $id_item)
    {
        try {

            return $this->BeneficiosRep->ver_alumno($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function asignar($id, $id_item, $id_alumno, $id_usuario, $vencimiento)
    {
        try {

            return $this->BeneficiosRep->asignar($id, $id_item, $id_alumno, $id_usuario, $vencimiento);

        } catch (Exception $e) {

        }
    }

    public function modificar_asignacion($id, $id_item, $vencimiento)
    {
        try {

            return $this->BeneficiosRep->modificar_asignacion($id, $id_item, $vencimiento);

        } catch (Exception $e) {

        }
    }

    public function borrar_asignacion($id, $id_item)
    {
        try {

            return $this->BeneficiosRep->borrar_asignacion($id, $id_item);

        } catch (Exception $e) {

        }
    }
    
    public function suspender_asignacion($id, $id_item)
    {
        try {

            return $this->BeneficiosRep->suspender_asignacion($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function reactivar_asignacion($id, $id_item)
    {
        try {

            return $this->BeneficiosRep->reactivar_asignacion($id, $id_item);

        } catch (Exception $e) {

        }
    }


    

}
