<?php


namespace App\Services;

use App\Repositories\CajaRepository;

class CajaService
{

    private $CajaRep;

    function __construct(CajaRepository $CajaRep)
    {
        $this->CajaRep = $CajaRep;
    }
    public function movimientos_diarios($id,$id_caja,$fecha)
    {
        try {
           
            return $this->CajaRep->movimientos_diarios($id,$id_caja,$fecha);

        } catch (Exception $e) {

        }
    }

    public function movimientos_historicos($id,$id_caja)
    {
        try {
           
            return $this->CajaRep->movimientos_historicos($id,$id_caja);

        } catch (Exception $e) {

        }
    }

    public function borrar_pago($id,$id_item,$id_usuario)
    {
        try {

           
            return $this->CajaRep->borrar_pago($id,$id_item,$id_usuario);

        } catch (Exception $e) {

        }
    }

    public function listado($id)
    {
        try {

           
            return $this->CajaRep->listado($id);

        } catch (Exception $e) {

        }
    }

    public function listado_abiertas($id, $id_caja)
    {
        try {

           
            return $this->CajaRep->listado_abiertas($id, $id_caja);

        } catch (Exception $e) {

        }
    }

    public function enviar_comprobante($id,$id_item)
    {
        try {

           
            return $this->CajaRep->enviar_comprobante($id,$id_item);

        } catch (Exception $e) {

        }
    }

    public function nueva_cobranza($id)
    {
        try {

            return $this->CajaRep->nueva_cobranza($id);

        } catch (Exception $e) {

        }
    }

    public function comprobantes_pendientes($id, $id_item)
    {
        try {

            return $this->CajaRep->comprobantes_pendientes($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function recibir_cobranza($id, $id_item, $id_responsable, $observaciones, $id_medio_pago, $importe, $factura, $detalle_medio_pago, $detalle_imputaciones, $id_usuario)
    {
        try {

            return $this->CajaRep->recibir_cobranza($id, $id_item, $id_responsable, $observaciones, $id_medio_pago, $importe, $factura, $detalle_medio_pago, $detalle_imputaciones, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function recibir_cobranza_efectivo($id, $id_item, $fecha, $id_responsable, $observaciones, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo)
    {
        try {

            return $this->CajaRep->recibir_cobranza_efectivo($id, $id_item, $fecha, $id_responsable, $observaciones, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo);

        } catch (Exception $e) {

        }
    }
    public function recibir_cobranza_transferencia($id, $id_item, $fecha, $id_responsable, $observaciones, $banco, $referencia, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo)
    {
        try {

            return $this->CajaRep->recibir_cobranza_transferencia($id, $id_item, $fecha, $id_responsable, $observaciones, $banco, $referencia, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo);

        } catch (Exception $e) {

        }
    }

    public function recibir_cobranza_cheque($id, $id_item, $fecha, $id_responsable, $observaciones, $banco, $referencia, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo)
    {
        try {

            return $this->CajaRep->recibir_cobranza_cheque($id, $id_item, $fecha, $id_responsable, $observaciones, $banco, $referencia, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo);

        } catch (Exception $e) {

        }
    }

    public function test_facturante($id)
    {
        try {

            return $this->CajaRep->test_facturante($id);

        } catch (Exception $e) {

        }
    }

    public function detalle_cobranza($id, $periodo)
    {
        try {

            return $this->CajaRep->detalle_cobranza($id, $periodo);

        } catch (Exception $e) {

        }
    }

    public function detalle_medios_pago($id, $periodo)
    {
        try {

            return $this->CajaRep->detalle_medios_pago($id, $periodo);

        } catch (Exception $e) {

        }
    }

    public function planilla_caja($id, $id_item, $id_fecha)
    {
        try {

            return $this->CajaRep->planilla_caja($id, $id_item, $id_fecha);

        } catch (Exception $e) {

        }
    }


    
    public function apertura_caja($id, $id_item, $saldo_incial, $id_usuario)
    {
        try {

            return $this->CajaRep->apertura_caja($id, $id_item, $saldo_incial, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function cierre_caja($id, $id_item, $id_usuario)
    {
        try {

            return $this->CajaRep->cierre_caja($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }
    
    public function egreso_caja($id, $id_item, $descripcion, $importe, $id_usuario)
    {
        try {

            return $this->CajaRep->egreso_caja($id, $id_item, $descripcion, $importe, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function borrar_egreso_caja($id, $id_item, $id_usuario)
    {
        try {

            return $this->CajaRep->borrar_egreso_caja($id, $id_item, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function cambio_estado_facturacion($id, $id_item)
    {
        try {

            return $this->CajaRep->cambio_estado_facturacion($id, $id_item);

        } catch (Exception $e) {

        }
    }
    
    



    

}
