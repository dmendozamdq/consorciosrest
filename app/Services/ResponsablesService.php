<?php

namespace App\Services;

use App\Repositories\ResponsablesRepository;

class ResponsablesService
{

    private $ResponsablesRep;

    function __construct(ResponsablesRepository $ResponsablesRep)
    {
        $this->ResponsablesRep = $ResponsablesRep;
    }
    public function agregar($id,$nombre,$apellido,$dni,$domicilio,$telefono,$email,$id_usuario,$nombre_fiscal,$cuit,$tipo_factura,$saldo_inicial,$plan,$facturable,$condicion_iva)
    {
        try {


            return $this->ResponsablesRep->agregar($id,$nombre,$apellido,$dni,$domicilio,$telefono,$email,$id_usuario,$nombre_fiscal,$cuit,$tipo_factura,$saldo_inicial,$plan,$facturable,$condicion_iva);

        } catch (Exception $e) {

        }
    }

    public function modificar($id,$id_item,$nombre,$apellido,$dni,$domicilio,$telefono,$email,$id_usuario,$nombre_fiscal,$cuit,$tipo_factura,$saldo_inicial,$plan,$facturable,$condicion_iva)
    {
        try {

            return $this->ResponsablesRep->modificar($id,$id_item,$nombre,$apellido,$dni,$domicilio,$telefono,$email,$id_usuario,$nombre_fiscal,$cuit,$tipo_factura,$saldo_inicial,$plan,$facturable,$condicion_iva);

        } catch (Exception $e) {

        }
    }

    public function borrar($id, $id_item)
    {
        try {

            return $this->ResponsablesRep->borrar($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function listado($id)
    {
        try {

            return $this->ResponsablesRep->listado($id);

        } catch (Exception $e) {

        }
    }

    public function ver($id, $id_item)
    {
        try {

            return $this->ResponsablesRep->ver($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function vincular_estudiante($id, $id_item, $id_alumno, $id_usuario)
    {
        try {

            return $this->ResponsablesRep->vincular_estudiante($id, $id_item, $id_alumno, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function desvincular_estudiante($id, $id_item)
    {
        try {

            return $this->ResponsablesRep->desvincular_estudiante($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function estudiantes_vinculables($id)
    {
        try {

            return $this->ResponsablesRep->estudiantes_vinculables($id);

        } catch (Exception $e) {

        }
    }

    public function estudiantes_listado($id)
    {
        try {

            return $this->ResponsablesRep->estudiantes_listado($id);

        } catch (Exception $e) {

        }
    }

    public function ver_cc($id, $id_item, $tipo)
    {
        try {

            return $this->ResponsablesRep->ver_cc($id, $id_item, $tipo);

        } catch (Exception $e) {

        }
    }

    public function saldo($id, $id_item)
    {
        try {

            return $this->ResponsablesRep->saldo($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function interes_gen($id)
    {
        try {

            return $this->ResponsablesRep->interes_gen($id);

        } catch (Exception $e) {

        }
    }

    public function cargo_gen($id, $id_item, $id_movimiento, $descripcion, $importe, $id_usuario, $fecha, $id_alumno, $interes, $id_empresa)
    {
        try {

            return $this->ResponsablesRep->cargo_gen($id, $id_item, $id_movimiento, $descripcion, $importe, $id_usuario, $fecha, $id_alumno, $interes, $id_empresa);

        } catch (Exception $e) {

        }
    }

    public function generar_ajuste($id, $id_item, $id_responsable, $importe, $descripcion, $id_usuario)
    {
        try {

            return $this->ResponsablesRep->generar_ajuste($id, $id_item, $id_responsable, $importe, $descripcion, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function borrar_gen($id, $id_item, $id_usuario)
    {
        try {

            return $this->ResponsablesRep->borrar_gen($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function lista_movimientos_cuenta($id)
    {
        try {

            return $this->ResponsablesRep->lista_movimientos_cuenta($id);

        } catch (Exception $e) {

        }
    }

    public function estadistica_vinculacion($id)
    {
        try {

            return $this->ResponsablesRep->estadistica_vinculacion($id);

        } catch (Exception $e) {

        }
    }

    public function condiciones_iva($id)
    {
        try {

            return $this->ResponsablesRep->condiciones_iva($id);

        } catch (Exception $e) {

        }
    }


}
