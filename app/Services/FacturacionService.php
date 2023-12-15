<?php


namespace App\Services;

use App\Repositories\FacturacionRepository;

class FacturacionService
{

    private $FactuRep;

    function __construct(FacturacionRepository $FactuRep)
    {
        $this->FactuRep = $FactuRep;
    }
    public function listado($id)
    {
        try {
           
            return $this->FactuRep->listado($id);

        } catch (Exception $e) {

        }
    }

    public function listado_notas($id)
    {
        try {
           
            return $this->FactuRep->listado_notas($id);

        } catch (Exception $e) {

        }
    }

    public function pendientes_facturacion($id)
    {
        try {
           
            return $this->FactuRep->pendientes_facturacion($id);

        } catch (Exception $e) {

        }
    }

    public function reenviar_factura($id,$id_item)
    {
        try {

           
            return $this->FactuRep->reenviar_factura($id,$id_item);

        } catch (Exception $e) {

        }
    }

    
    public function reenviar_recibo($id,$id_item)
    {
        try {

           
            return $this->FactuRep->reenviar_recibo($id,$id_item);

        } catch (Exception $e) {

        }
    }

    public function reenviar_nota_c($id,$id_item)
    {
        try {

           
            return $this->FactuRep->reenviar_nota_c($id,$id_item);

        } catch (Exception $e) {

        }
    }

    public function reenviar_nota_d($id,$id_item)
    {
        try {

           
            return $this->FactuRep->reenviar_nota_d($id,$id_item);

        } catch (Exception $e) {

        }
    }

    

    public function estadisticas($id, $id_empresa, $desde, $hasta)
    {
        try {

            return $this->FactuRep->estadisticas($id, $id_empresa, $desde, $hasta);

        } catch (Exception $e) {

        }
    }

    public function generar_nota_credito($id, $id_comprobante, $id_usuario)
    {
        try {

            return $this->FactuRep->generar_nota_credito($id, $id_comprobante, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function generar_nota_debito($id, $id_comprobante, $id_usuario)
    {
        try {

            return $this->FactuRep->generar_nota_debito($id, $id_comprobante, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function nota_credito($id, $id_comprobante)
    {
        try {

            return $this->FactuRep->nota_credito($id, $id_comprobante);

        } catch (Exception $e) {

        }
    }

    public function ver_nota_credito($id, $numero_comprobante, $id_empresa)
    {
        try {

            return $this->FactuRep->ver_nota_credito($id, $numero_comprobante, $id_empresa);

        } catch (Exception $e) {

        }
    }

    public function ver_factura_emitida($id, $numero_comprobante, $id_empresa)
    {
        try {

            return $this->FactuRep->ver_factura_emitida($id, $numero_comprobante, $id_empresa);

        } catch (Exception $e) {

        }
    }

    public function ver_modelo_factura($id, $id_movimiento_caja)
    {
        try {

            return $this->FactuRep->ver_modelo_factura($id, $id_movimiento_caja);

        } catch (Exception $e) {

        }
    }

    public function ver_modelo_facturas_diarias($id, $fecha)
    {
        try {

            return $this->FactuRep->ver_modelo_facturas_diarias($id, $fecha);

        } catch (Exception $e) {

        }
    }
    
    public function generar_factura($id, $id_responsable, $id_empresa, $id_pto_vta, $id_periodo, $id_operacion, $importe, $conceptos, $id_usuario, $id_alumno)
    {
        try {

            return $this->FactuRep->generar_factura($id, $id_responsable, $id_empresa, $id_pto_vta, $id_periodo, $id_operacion, $importe, $conceptos, $id_usuario, $id_alumno);

        } catch (Exception $e) {

        }
    }

    public function generar_lote_facturas($id, $id_usuario, $lote)
    {
        try {

            return $this->FactuRep->generar_lote_facturas($id, $id_usuario, $lote);

        } catch (Exception $e) {

        }
    }

    public function cerrar_factura($id, $id_operacion)
    {
        try {
           
            return $this->FactuRep->cerrar_factura($id, $id_operacion);

        } catch (Exception $e) {

        }
    }

    public function lotes_intereses($id)
    {
        try {
           
            return $this->FactuRep->lotes_intereses($id);

        } catch (Exception $e) {

        }
    }

    public function modelo_lote_intereses($id, $id_operacion, $orden)
    {
        try {
           
            return $this->FactuRep->modelo_lote_intereses($id, $id_operacion, $orden);

        } catch (Exception $e) {

        }
    }

    public function ver_lote_intereses($id, $id_item)
    {
        try {
           
            return $this->FactuRep->ver_lote_intereses($id, $id_item);

        } catch (Exception $e) {

        }
    }

    public function generar_lote_intereses($id, $id_periodo, $orden, $fecha, $interes, $arreglo, $id_usuario)
    {
        try {
           
            return $this->FactuRep->generar_lote_intereses($id, $id_periodo, $orden, $fecha, $interes, $arreglo, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function tipos_comprobante($id)
    {
        try {
           
            return $this->FactuRep->tipos_comprobante($id);

        } catch (Exception $e) {

        }
    }

    public function consulta_libro_iva($id, $id_empresa)
    {
        try {
           
            return $this->FactuRep->consulta_libro_iva($id, $id_empresa);

        } catch (Exception $e) {

        }
    }

    public function generacion_libro_iva($id, $arreglo, $id_usuario, $id_empresa)
    {
        try {
           
            return $this->FactuRep->generacion_libro_iva($id, $arreglo, $id_usuario, $id_empresa);

        } catch (Exception $e) {

        }
    }
    public function generacion_libro_iva_alicuotas($id, $arreglo, $id_usuario, $id_empresa)
    {
        try {
           
            return $this->FactuRep->generacion_libro_iva_alicuotas($id, $arreglo, $id_usuario, $id_empresa);

        } catch (Exception $e) {

        }
    }

    

}
