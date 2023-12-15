<?php


namespace App\Services;

use App\Repositories\LotesRepository;

class LotesService
{

    private $LotesRep;

    function __construct(LotesRepository $LotesRep)
    {
        $this->LotesRep = $LotesRep;
    }
    public function agregar_p1($id,$nombre,$id_empresa,$tipo_facturacion,$id_campana,$id_periodo,$vencimiento1,$vencimiento2,$vencimiento3,$id_usuario,$interes)
    {
        try {
           
            return $this->LotesRep->agregar_p1($id,$nombre,$id_empresa,$tipo_facturacion,$id_campana,$id_periodo,$vencimiento1,$vencimiento2,$vencimiento3,$id_usuario,$interes);

        } catch (Exception $e) {

        }
    }

    public function modificar_p1($id,$id_item,$nombre,$id_empresa,$tipo_facturacion,$id_campana,$id_periodo,$vencimiento1,$vencimiento2,$vencimiento3,$id_usuario,$interes)
    {
        try {

           
            return $this->LotesRep->modificar_p1($id,$id_item,$nombre,$id_empresa,$tipo_facturacion,$id_campana,$id_periodo,$vencimiento1,$vencimiento2,$vencimiento3,$id_usuario,$interes);

        } catch (Exception $e) {

        }
    }

    public function periodos_libres($id, $id_item)
    {
        try {

            return $this->LotesRep->periodos_libres($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function periodos($id, $id_item)
    {
        try {

            return $this->LotesRep->periodos($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function borrar($id, $id_item, $id_usuario)
    {
        try {

            return $this->LotesRep->borrar($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function ver_p1($id, $id_item)
    {
        try {

            return $this->LotesRep->ver_p1($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function listado($id)
    {
        try {

            return $this->LotesRep->listado($id);

        } catch (Exception $e) {

        }
    }
    
    /*
    public function agregar_p2($id, $id_item, $conceptos)
    {
        try {

            return $this->LotesRep->agregar_p2($id, $id_item, $conceptos);

        } catch (Exception $e) {

        }
    }
    */

    
    public function modificar_p2($id, $id_item, $conceptos)
    {
        try {

            return $this->LotesRep->modificar_p2($id, $id_item, $conceptos);

        } catch (Exception $e) {

        }
    }
    
    public function ver_p2($id, $id_item)
    {
        try {

            return $this->LotesRep->ver_p2($id, $id_item);

        } catch (Exception $e) {

        }
    }
    /*
    public function agregar_p3($id, $id_item, $estudiantes)
    {
        try {

            return $this->LotesRep->agregar_p3($id, $id_item, $estudiantes);

        } catch (Exception $e) {

        }
    }
    */
    public function modificar_p3($id, $id_item, $estudiantes)
    {
        try {

            return $this->LotesRep->modificar_p3($id, $id_item, $estudiantes);

        } catch (Exception $e) {

        }
    }

    public function ver_p3($id, $id_item)
    {
        try {

            return $this->LotesRep->ver_p3($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function confirmar($id, $id_item)
    {
        try {

            return $this->LotesRep->confirmar($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function generar($id, $id_item, $id_usuario)
    {
        try {

            return $this->LotesRep->generar($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function publicar($id, $id_item, $id_usuario)
    {
        try {

            return $this->LotesRep->publicar($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function republicar_lote($id, $id_item)
    {
        try {

            return $this->LotesRep->republicar_lote($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function republicar_comprobante($id, $id_item)
    {
        try {

            return $this->LotesRep->publicar($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function ver($id, $id_item)
    {
        try {

            return $this->LotesRep->ver($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function detalle($id, $id_item)
    {
        try {

            return $this->LotesRep->detalle($id, $id_item);

        } catch (Exception $e) {

        }
    }
    public function detalle_comprobante($id, $id_item)
    {
        try {

            return $this->LotesRep->detalle_comprobante($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function borrar_comprobante($id, $id_item, $id_usuario)
    {
        try {

            return $this->LotesRep->borrar_comprobante($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function borrar_concepto($id, $id_item, $id_usuario)
    {
        try {

            return $this->LotesRep->borrar_concepto($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function modificar_concepto($id, $id_item, $descripcion, $importe, $id_usuario)
    {
        try {

            return $this->LotesRep->modificar_concepto($id, $id_item, $descripcion, $importe, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function agregar_concepto($id, $id_item, $id_concepto, $id_tipo_concepto, $descripcion, $importe, $id_usuario)
    {
        try {

            return $this->LotesRep->agregar_concepto($id, $id_item, $id_concepto, $id_tipo_concepto, $descripcion, $importe, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function facturas_emitidas($id, $id_item)
    {
        try {

            return $this->LotesRep->facturas_emitidas($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function republicar_factura($id, $id_item)
    {
        try {

            return $this->LotesRep->republicar_factura($id, $id_item);

        } catch (Exception $e) {

        }
    }

    

}
