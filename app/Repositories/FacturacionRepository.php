<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;
use Afip;
use Illuminate\Support\Str;

class FacturacionRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
        {
            $this->Alumno = $Alumno;
            $this->dataBaseService = $dataBaseService;
        }




        
    
        public function reenviar_factura($id, $id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();


          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ID,MailD
                          FROM envio_comprobantes
                          WHERE B=0 and ID_Comprobante={$id_item} and Tipo_Comprobante=1
                        
                      ");
          $ID_Envio= $listado[0]->ID;
          $MailD= $listado[0]->MailD;
          
          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE envio_comprobantes
                                    SET Envio=0, Leido=0
                                    WHERE Id={$ID_Envio}
                                ");
        
            $resultado='La factura se ha reenviado exitosamente al correo electrónico '.$MailD;
         
          return $resultado;
        }

        public function reenviar_recibo($id, $id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();


          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ID,MailD
                          FROM envio_comprobantes
                          WHERE B=0 and ID_Comprobante={$id_item} and Tipo_Comprobante=9
                        
                      ");
          $ID_Envio= $listado[0]->ID;
          $MailD= $listado[0]->MailD;
          
          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE envio_comprobantes
                                    SET Envio=0, Leido=0
                                    WHERE Id={$ID_Envio}
                                ");
        
            $resultado='El Recibo se ha reenviado exitosamente al correo electrónico '.$MailD;
         
          return $resultado;
        }



    
    
    public function listado($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $resultado=array();
          $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
          SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
          FROM institucion inst
          WHERE inst.Id=1
              ");
            $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
            $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;
          $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT fe.Id, fe.Numero, fe.Tipo_Factura, fe.Fecha, fe.Importe, fe.Observaciones, emp.Empresa, fe.Pto_Vta, re.Apellido, re.Nombre, fe.ID_Operacion, fe.ID_Anulacion
                                    FROM facturas_emitidas fe
                                    INNER JOIN empresas emp ON fe.ID_Empresa=emp.ID
                                    INNER JOIN responsabes_economicos re ON fe.ID_Responsable=re.ID
                                    WHERE fe.B=0 and fe.Tipo_Factura=11
                                    ORDER BY fe.Id desc

                                        ");

           $ctrl_movimientos=count($lista_movientos);
           if(empty($ctrl_movimientos))
            {
                
            }
        else
            {
                for ($j=0; $j < count($lista_movientos); $j++)
                    {
                        $ID_Factura=$lista_movientos[$j]->Id;
                        $Numero_Factura=$lista_movientos[$j]->Numero;
                        $ID_Tipo_Factura=$lista_movientos[$j]->Tipo_Factura;
                        $Responsable_A = $lista_movientos[$j]->Apellido;
                        $Responsable_N = $lista_movientos[$j]->Nombre;
                        $Responsable=$Responsable_A.', '.$Responsable_N;
                        $ID_Operacion_Caja = $lista_movientos[$j]->ID_Operacion;
                        $ID_Anulacion=$lista_movientos[$j]->ID_Anulacion;
                        if($ID_Anulacion>=1)
                            {
                                $Enlace_Nota_Credito='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice_nc.php?id='.$ID_Anulacion;
                            }
                        else
                            {
                                $Enlace_Nota_Credito='';
                            }
                        if($ID_Tipo_Factura==11)
                            {
                                $TipoyNro_Factura='C';
                            }
                        $TipoyNro_Factura=$TipoyNro_Factura.'-'.$Numero_Factura;
                        $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice.php?id='.$ID_Factura;

                        $lista_movimientos_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mc.Fecha, mp.Nombre, mc.Importe, mc.Detalle, mc.ID_Caja, us.name
                                    FROM movimientos_caja mc
                                    INNER JOIN medios_pago mp ON mc.ID_Medio_Pago=mp.Id
                                    INNER JOIN users us ON mc.ID_Usuario=us.id
                                    WHERE mc.B=0 and mc.Id={$ID_Operacion_Caja}

                                        ");
                        
                        $ctrl_movimientos_caja=count($lista_movimientos_caja);
                        if(empty($ctrl_movimientos_caja))
                            {
                                $Descripcion_Movimiento='<p>No se ha podido determinar el movimiento de caja Asociado a la factura';
                            }
                        else
                            {
                                for ($z=0; $z < count($lista_movimientos_caja); $z++)
                                    {
                                        $Fecha=$lista_movimientos_caja[$z]->Fecha;
                                        $Importe=$lista_movimientos_caja[$z]->Importe;
                                        $Observaciones=trim(utf8_decode($lista_movimientos_caja[$z]->Detalle));
                                        $Usuario=trim(utf8_decode($lista_movimientos_caja[$z]->name));
                                        $ID_Caja=$lista_movimientos_caja[$z]->ID_Caja;
                                        $lista_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT Nombre
                                            FROM caja
                                            WHERE Id={$ID_Caja}

                                                ");
                                        $Caja=trim(utf8_decode($lista_caja [0]->Nombre));


                                    }
                                    $Descripcion_Movimiento='<p>Fecha: '.$Fecha.'<p>Caja: '.$Caja.'<p>Usuario: '.$Usuario.'<p>Importe: $'.$Importe.'<p>Observaciones: '.$Observaciones;
                            }
                        //EXPLORO LECTURA

                        $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                        FROM envio_comprobantes ec
                        WHERE ec.B=0 and ec.Id_Comprobante={$ID_Factura} and ec.Tipo_Comprobante=1

                            ");
                        $Control_Envio=count($busqueda_lecturas);
                        if(empty($Control_Envio))
                            {
                                $Leido=0;
                                $Detalle_Lectura='';
                            }
                        else
                            {
                                for ($y=0; $y < count($busqueda_lecturas); $y++)
                                    {
                                        $Destinatario=$busqueda_lecturas[$y]->Destinatario;
                                        $Mail=$busqueda_lecturas[$y]->MailD;
                                        $Envio=$busqueda_lecturas[$y]->Envio;
                                        $Fecha_Envio=$busqueda_lecturas[$y]->Fecha_Envio;
                                        $Hora_Envio=$busqueda_lecturas[$y]->Hora_Envio;
                                        $Leido=$busqueda_lecturas[$y]->Leido;
                                        $Hora_Leido=$busqueda_lecturas[$y]->Hora_Leido;
                                        $Fecha_Leido=$busqueda_lecturas[$y]->Fecha_Leido;
                                        if($Envio==0)
                                            {
                                                $Detalla_Lectura='<p>En Proceso de Envío al destinatario';
                                            }
                                        if($Envio==1)
                                            {
                                                $Detalla_Lectura='<p>Enviado a '.$Mail.' el '.$Fecha_Envio.' a las '.$Hora_Envio;
                                                if($Leido==1)
                                                    {
                                                        $Detalla_Lectura=$Detalla_Lectura.'<p> Última lectura realizada el '.$Fecha_Leido.' a las '.$Hora_Leido;
                                                    }
                                                else
                                                    {
                                                        $Detalla_Lectura=$Detalla_Lectura.'<p> Aún no ha sido leído por el destinatario';
                                                    }
                                            }
                                        if($Envio==2)
                                            {
                                                $Leido=2;
                                                $Detalla_Lectura='<p>En Proceso de Envío ha fallado, se está intentando volver a enviar el comprobante';
                                            }
                                        if($Envio==3)
                                            {
                                                $Leido=3;
                                                $Detalla_Lectura='<p>La dirección de correo electrónico definida para el responsable ('.$Mail.') es INVALIDA';
                                            }
                                        
                                          
                                        

                                    }
                            }
                        
                        $resultado[$j] = array(
                                                                                     
                            'id'=> $ID_Factura,
                            'fecha'=> $lista_movientos[$j]->Fecha,
                            'tipoynumero'=> $TipoyNro_Factura,
                            'empresa'=> trim(utf8_decode($lista_movientos[$j]->Empresa)),
                            'pto_vta'=> $lista_movientos[$j]->Pto_Vta,
                            'responsable'=> trim(utf8_decode($Responsable)),
                            'importe'=> $lista_movientos[$j]->Importe,
                            'referencia'=> trim(utf8_decode($lista_movientos[$j]->Observaciones)),
                            'enlace'=> $Enlace,
                            'movimiento_caja'=> $Descripcion_Movimiento,
                            'leido'=> $Leido,
                            'detalle_lectura'=> trim(utf8_decode($Detalla_Lectura)),
                            'enlace_nota_credito'=> $Enlace_Nota_Credito

                      );

                    }
                  
            }
          return $resultado;
        }

        public function listado_notas($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $resultado=array();
          $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
          SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
          FROM institucion inst
          WHERE inst.Id=1
              ");
            $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
            $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;
          $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT fe.Id, fe.Numero, fe.Tipo_Factura, fe.Fecha, fe.Importe, fe.Observaciones, emp.Empresa, fe.Pto_Vta, re.Apellido, re.Nombre, fe.ID_Operacion, fe.ID_Anulacion, fe.ID_ND
                                    FROM facturas_emitidas fe
                                    INNER JOIN empresas emp ON fe.ID_Empresa=emp.ID
                                    INNER JOIN responsabes_economicos re ON fe.ID_Responsable=re.ID
                                    WHERE fe.B=0 and fe.Tipo_Factura>=12
                                    ORDER BY fe.Id desc

                                        ");

           $ctrl_movimientos=count($lista_movientos);
           if(empty($ctrl_movimientos))
            {
                
            }
        else
            {
                for ($j=0; $j < count($lista_movientos); $j++)
                    {
                        $ID_Factura=$lista_movientos[$j]->Id;
                        $Numero_Factura=$lista_movientos[$j]->Numero;
                        $ID_Tipo_Factura=$lista_movientos[$j]->Tipo_Factura;
                        $Responsable_A = $lista_movientos[$j]->Apellido;
                        $Responsable_N = $lista_movientos[$j]->Nombre;
                        $Responsable=$Responsable_A.', '.$Responsable_N;
                        $ID_Operacion_Caja = $lista_movientos[$j]->ID_Operacion;
                        $ID_Anulacion = $lista_movientos[$j]->ID_Anulacion;
                        $ID_Nota_Debito = $lista_movientos[$j]->ID_ND;

                        if($ID_Tipo_Factura==13)
                            {
                                $TipoyNro_Factura='NC-C-';
                                $Codigo_Nota=1;
                                $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice_nc.php?id='.$ID_Factura;
                                $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                                    FROM envio_comprobantes ec
                                    WHERE ec.B=0 and ec.Id_Comprobante={$ID_Factura} and ec.Tipo_Comprobante=2

                                        ");
                                if(empty($ID_Nota_Debito))
                                        {
                                            $Enlace_Nota_Debito='';
                                            
                                        }
                                    else
                                        {
                                            $Enlace_Nota_Debito='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice_nd.php?id='.$ID_Nota_Debito;
                                        }
                                
                            }
                        if($ID_Tipo_Factura==12)
                            {
                                $TipoyNro_Factura='ND-C-';
                                $Codigo_Nota=2;
                                $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice_nd.php?id='.$ID_Factura;
                                $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                                    FROM envio_comprobantes ec
                                    WHERE ec.B=0 and ec.Id_Comprobante={$ID_Factura} and ec.Tipo_Comprobante=3

                                        ");
                                $Enlace_Nota_Debito='';
                            }
                        $TipoyNro_Factura=$TipoyNro_Factura.'-'.$Numero_Factura;
                        
                        
                        $Descripcion_Movimiento='<p>No se ha podido determinar el movimiento de caja Asociado a la Nota de Crédito/Débito';
                       
                        //EXPLORO LECTURA
                        
                        $Control_Envio=count($busqueda_lecturas);
                        if(empty($Control_Envio))
                            {
                                $Leido=0;
                                $Detalla_Lectura='';
                            }
                        else
                            {
                                for ($y=0; $y < count($busqueda_lecturas); $y++)
                                    {
                                        $Destinatario=$busqueda_lecturas[$y]->Destinatario;
                                        $Mail=$busqueda_lecturas[$y]->MailD;
                                        $Envio=$busqueda_lecturas[$y]->Envio;
                                        $Fecha_Envio=$busqueda_lecturas[$y]->Fecha_Envio;
                                        $Hora_Envio=$busqueda_lecturas[$y]->Hora_Envio;
                                        $Leido=$busqueda_lecturas[$y]->Leido;
                                        $Hora_Leido=$busqueda_lecturas[$y]->Hora_Leido;
                                        $Fecha_Leido=$busqueda_lecturas[$y]->Fecha_Leido;
                                        if($Envio==1)
                                            {
                                                $Detalla_Lectura='<p>Enviado a '.$Mail.' el '.$Fecha_Envio.' a las '.$Hora_Envio;
                                                if($Leido==1)
                                                    {
                                                        $Detalla_Lectura=$Detalla_Lectura.'<p> Última lectura realizada el '.$Fecha_Leido.' a las '.$Hora_Leido;
                                                    }
                                                else
                                                    {
                                                        $Detalla_Lectura=$Detalla_Lectura.'<p> Aún no ha sido leído por el destinatario';
                                                    }
                                            }
                                        else
                                            {
                                                $Detalla_Lectura='<p>En Proceso de Envío al destinatario';
                                            }
                                        

                                    }
                            }
                        
                        $resultado[$j] = array(
                                                                                     
                            'id'=> $ID_Factura,
                            'fecha'=> $lista_movientos[$j]->Fecha,
                            'tipoynumero'=> $TipoyNro_Factura,
                            'codigo_nota'=> $Codigo_Nota,
                            'empresa'=> trim(utf8_decode($lista_movientos[$j]->Empresa)),
                            'pto_vta'=> $lista_movientos[$j]->Pto_Vta,
                            'responsable'=> trim(utf8_decode($Responsable)),
                            'importe'=> $lista_movientos[$j]->Importe,
                            'referencia'=> trim(utf8_decode($lista_movientos[$j]->Observaciones)),
                            'enlace'=> $Enlace,
                            'movimiento_caja'=> $Descripcion_Movimiento,
                            'leido'=> $Leido,
                            'detalle_lectura'=> $Detalla_Lectura,
                            'enlace_nota_debito'=> $Enlace_Nota_Debito

                      );

                    }
                  
            }
          return $resultado;
        }

        public function pendientes_facturacion($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $resultado=array();
          $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mc.Id,ca.Nombre, mc.Fecha, re.Apellido, mc.Importe, mc.ID_Medio_Pago, mc.Detalle
                                    FROM movimientos_caja mc
                                    INNER JOIN caja ca ON mc.ID_Caja=ca.Id
                                    INNER JOIN responsabes_economicos re ON mc.ID_Responsable=re.Id
                                    WHERE mc.B=0 and mc.Facturado=1
                                    ORDER BY mc.ID desc

                                        ");

           $ctrl_movimientos=count($lista_movientos);
           if(empty($ctrl_movimientos))
            {
                
            }
        else
            {
                for ($j=0; $j < count($lista_movientos); $j++)
                    {
                        $ID_Movimiento=$lista_movientos[$j]->Id;
                        $Caja=trim(utf8_decode($lista_movientos[$j]->Nombre));
                        $Fecha=$lista_movientos[$j]->Fecha;
                        $Responsable_A = trim(utf8_decode($lista_movientos[$j]->Apellido));
                        $Importe = $lista_movientos[$j]->Importe;
                        $ID_Medio_Pago = $lista_movientos[$j]->ID_Medio_Pago;
                        $Detalle = trim(utf8_decode($lista_movientos[$j]->Detalle));
                        $c_medio_pago= $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mp.Nombre
                                    FROM medios_pago mp
                                    WHERE mp.Id={$ID_Medio_Pago}

                                        ");
                        $Medio_Pago=$c_medio_pago[0]->Nombre;

                        $Enlace='http://geofacturacion.com.ar/sancayetano/reporting/ver_detalle.php?id='.$ID_Movimiento;
                        $resultado[$j] = array(
                                                                                     
                            'id'=> $ID_Movimiento,
                            'fecha'=> $Fecha,
                            'caja'=> $Caja,
                            'responsable'=> $Responsable_A,
                            'importe'=> $Importe,
                            'medio_pago'=> trim(utf8_decode($Medio_Pago)),
                            'detalle'=> $Detalle,
                            'enlace_simulacion'=> $Enlace


                            

                      );

                    }
                  
            }
          return $resultado;
        }


      public function generar_nota_credito($id, $id_comprobante, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          //VERIFICO QUE EL COMPROBANTE NO TIENE NOTA DE CREDITO
          $verificacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT fe.Importe, fe.Numero, fe.Tipo_Factura, cc.Comprobante, cc.Anulacion, fe.ID_Empresa, fe.Pto_Vta, fe.ID_Responsable, fe.Responsable, fe.Domicilio, fe.Documento
                    FROM facturas_emitidas fe
                    INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                    WHERE fe.B=0 and fe.ID_Anulacion=0 and fe.Id={$id_comprobante}
                    ");
        
            $ctrl_anulacion=count($verificacion);
            if($ctrl_anulacion>=1)
                {
                    //NO TIENE ANULACIONES
                    $importe_o=$verificacion[0]->Importe;
                    $numero_o=$verificacion[0]->Numero;
                    $tipo_factura_o=$verificacion[0]->Tipo_Factura;
                    $id_empresa_o=$verificacion[0]->ID_Empresa;
                    $pto_vta_o=$verificacion[0]->Pto_Vta;
                    $id_responsable_o=$verificacion[0]->ID_Responsable;
                    $responsable_o=$verificacion[0]->Responsable;
                    $domicilio_o=$verificacion[0]->Domicilio;
                    $documento_o=$verificacion[0]->Documento;
                    $Letra_Comprobante=$verificacion[0]->Comprobante;
                    $Observacion='Nota de Crédito por Anulación de Comprobante '.$Letra_Comprobante.'-'.$numero_o;
                    $Observacion=trim(utf8_encode($Observacion));

                    /*
                    $response = $this->nota_credito($id_institucion, $id_comprobante);
                    if (is_array($response)) {
                        $responseData = $response;
                    } else {
                        // Decodificar la respuesta JSON
                        $responseData = json_decode($response, true);
                    }
                    
                    $data = $responseData['data'];
                    if (!empty($data)) {
                        // Obtener los valores de "CAE", "CAEFchVto" y "Numero"
                        $cae = $data[0]['CAE'];
                        $caefchvto = $data[0]['CAEFchVto'];
                        $numero_nuevo  = $data[0]['Numero'];

                    } 

                    //$data = $res;

                    //$CAE_Obtenido = $data['CAE'];
                    //$Fecha_Obtenida = $data['CAEFchVto'];
                    */


                    $comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fe.Numero, fe.Tipo_Factura, fe.ID_Empresa, fe.Pto_Vta, fe.ID_Responsable, fe.Importe
                            FROM facturas_emitidas fe
                            WHERE fe.B=0 and fe.Id={$id_comprobante}
                            ");
                    $numero_comprobante=$comprobantes[0]->Numero;
                    $tipo_comprobante=$comprobantes[0]->Tipo_Factura;
                    $id_empresa=$comprobantes[0]->ID_Empresa;
                    $pto_vta=$comprobantes[0]->Pto_Vta;
                    $ID_Responsable=$comprobantes[0]->ID_Responsable;
                    $Importe=$comprobantes[0]->Importe;

                    $empresas = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT emp.CUIT, emp.Afip_key, emp.Afip_scr, emp.Produccion
                                FROM empresas emp
                                WHERE emp.B=0 and emp.ID={$id_empresa}
                                ");
                    $cuit=$empresas[0]->CUIT;
                    $key=$empresas[0]->Afip_key;
                    $cert=$empresas[0]->Afip_scr;
                    $produccion=$empresas[0]->Produccion;

                    $responsables = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT re.DNI, re.CUIT
                                FROM responsabes_economicos re
                                WHERE re.Id={$ID_Responsable}
                                ");
                    $dni_r=$responsables[0]->DNI;
                    $cuit_r=$responsables[0]->CUIT;
                    


                    $afip = new Afip(array(
                        'CUIT' => $cuit,
                        //'CUIT' => '23284542819',
                        'cert' 		=> $cert,
                        'key' 		=> $key
                        //'production' => $produccion
                        //'access_token' => 'UDbn6i9Yho7YGcG1tuZNSMN4BfQ8dWJrHACbLVZdTJ5uLtKncSbmrgn9RqOIxCFB'
                        ));
                    
                    $punto_de_venta = $pto_vta;

                    /**
                     * Tipo de Nota de Crédito
                     **/
                    $tipo_de_nota = 13; // 13 = Nota de Crédito C
                    
                    /**
                     * Número de la ultima Nota de Crédito C
                     **/
                    $last_voucher = $afip->ElectronicBilling->GetLastVoucher($punto_de_venta, $tipo_de_nota);
                    
                    /**
                     * Numero del punto de venta de la Factura 
                     * asociada a la Nota de Crédito
                     **/
                    $punto_factura_asociada = $pto_vta;
                    
                    /**
                     * Tipo de Factura asociada a la Nota de Crédito
                     **/
                    $tipo_factura_asociada = $tipo_comprobante; // 11 = Factura C
                    
                    /**
                     * Numero de Factura asociada a la Nota de Crédito
                     **/
                    $numero_factura_asociada = $numero_comprobante;
                    
                    /**
                     * Concepto de la Nota de Crédito
                     *
                     * Opciones:
                     *
                     * 1 = Productos 
                     * 2 = Servicios 
                     * 3 = Productos y Servicios
                     **/
                    $concepto = 2;
                    
                    /**
                     * Tipo de documento del comprador
                     *
                     * Opciones:
                     *
                     * 80 = CUIT 
                     * 86 = CUIL 
                     * 96 = DNI
                     * 99 = Consumidor Final 
                     **/
                    if($cuit_r==0)
                        {
                            $tipo_de_documento = 96;
                            $numero_de_documento = $dni_r;
                        }
                    else
                        {
                            $tipo_de_documento = 80;
                            $numero_de_documento = $cuit_r;
                        }
                
                    
                    /**
                     * Numero de comprobante
                     **/
                    $numero_de_nota = $last_voucher+1;
                    
                    /**
                     * Fecha de la Nota de Crédito en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
                     **/
                    $fecha = date('Y-m-d');
                    
                    /**
                     * Importe de la Nota de Crédito
                     **/
                    $importe_total = $Importe;
                    
                    /**
                     * Los siguientes campos solo son obligatorios para los conceptos 2 y 3
                     **/
                    if ($concepto === 2 || $concepto === 3) {
                        /**
                         * Fecha de inicio de servicio en formato aaaammdd
                         **/
                        $fecha_servicio_desde = intval(date('Ymd'));
                    
                        /**
                         * Fecha de fin de servicio en formato aaaammdd
                         **/
                        $fecha_servicio_hasta = intval(date('Ymd'));
                    
                        /**
                         * Fecha de vencimiento del pago en formato aaaammdd
                         **/
                        $fecha_vencimiento_pago = intval(date('Ymd'));
                    }
                    else {
                        $fecha_servicio_desde = null;
                        $fecha_servicio_hasta = null;
                        $fecha_vencimiento_pago = null;
                    }
                    
                    
                    $data = array(
                        'CantReg' 	=> 1, // Cantidad de Notas de Crédito a registrar
                        'PtoVta' 	=> $punto_de_venta,
                        'CbteTipo' 	=> $tipo_de_nota, 
                        'Concepto' 	=> $concepto,
                        'DocTipo' 	=> $tipo_de_documento,
                        'DocNro' 	=> $numero_de_documento,
                        'CbteDesde' => $numero_de_nota,
                        'CbteHasta' => $numero_de_nota,
                        'CbteFch' 	=> intval(str_replace('-', '', $fecha)),
                        'FchServDesde'  => $fecha_servicio_desde,
                        'FchServHasta'  => $fecha_servicio_hasta,
                        'FchVtoPago'    => $fecha_vencimiento_pago,
                        'ImpTotal' 	=> $importe_total,
                        'ImpTotConc'=> 0, // Importe neto no gravado
                        'ImpNeto' 	=> $importe_total, // Importe neto
                        'ImpOpEx' 	=> 0, // Importe exento al IVA
                        'ImpIVA' 	=> 0, // Importe de IVA
                        'ImpTrib' 	=> 0, //Importe total de tributos
                        'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante ('PES' = pesos argentinos) 
                        'MonCotiz' 	=> 1, // Cotización de la moneda usada (1 para pesos argentinos)  
                        'CbtesAsoc' => array( //Factura asociada
                            array(
                                'Tipo' 		=> $tipo_factura_asociada,
                                'PtoVta' 	=> $punto_factura_asociada,
                                'Nro' 		=> $numero_factura_asociada,
                            )
                        )
                    );
                    
                    /** 
                     * Creamos la Nota de Crédito 
                     **/
                    $res = $afip->ElectronicBilling->CreateVoucher($data);
                    $res["Numero"] = $numero_de_nota;

                    $data = $res;

                    $numero_nuevo = $numero_de_nota;
                    $cae = $data['CAE'];
                    $caefchvto = $data['CAEFchVto'];
        
        
                    
                    //if ($response['success']) {
                        // Obtener los valores de CAE y CAEFchVto
                        //$data0 = json_decode($response, true);
                        //$data = $response['data'];
                        //$data = json_decode($response['data'], true);
                        //$cae = $data['CAE'];
                        //$caefchvto = $data['CAEFchVto'];
                        //$numero_nuevo = $data['Numero'];
                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO facturas_emitidas
                                    (Numero,Tipo_Factura,Fecha,Importe,CAE,Vto,Observaciones,ID_Empresa,Pto_Vta,ID_Responsable,Responsable,Domicilio,Documento,ID_Anulacion,ID_Usuario)
                                    VALUES ({$numero_nuevo},'13','{$FechaActual}','{$Importe}','{$cae}','{$caefchvto}','{$Observacion}',{$id_empresa_o},{$pto_vta_o},{$id_responsable_o},'{$responsable_o}','{$domicilio_o}','{$documento_o}',{$id_comprobante},{$id_usuario})
                                ");
                        //CONSULTO ID NUEVO
                        $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fe.Id, re.Email
                            FROM facturas_emitidas fe
                            INNER JOIN responsabes_economicos re ON fe.ID_Responsable=re.Id
                            WHERE fe.B=0 and fe.Tipo_Factura=13 and fe.Numero={$numero_nuevo} and fe.CAE='{$cae}'
                            ");
                        $id_nota_credito=$check_insercion[0]->Id;
                        $email_r=$check_insercion[0]->Email;
                        //ACTUALIZO COMPROBANTE ANULADO
                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE facturas_emitidas
                                    SET ID_Anulacion={$id_nota_credito}
                                    WHERE Id={$id_comprobante}
                                ");
                        //GENERO ENVIO POR CORREO
                        $cadenaAleatoria = Str::random(25);
                        /*
                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO envio_comprobantes
                                    (ID_Comprobante,Tipo_Comprobante,Fecha,Hora,MailD,Destinatario,Aleatorio)
                                    VALUES ({$id_nota_credito},'2','{$FechaActual}','{$HoraActual}','{$email_r}','{$responsable_o}','{$cadenaAleatoria}')
                                ");
                        */
                        $resultado='La nota de Crédito ha sido emitida con éxito. El número asignado es '.$numero_nuevo.' y su CAE '.$cae;
                    /*
                    } else {
                        $resultado='error';
                    }
                    */

                }
            else
                {
                    //YA FUE ANULADO
                    $resultado='error';
                }
                    
          return $resultado;
        }

        public function generar_nota_debito($id, $id_comprobante, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          //VERIFICO QUE EL COMPROBANTE NO TIENE NOTA DE CREDITO
          $verificacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT fe.Importe, fe.Numero, fe.Tipo_Factura, cc.Comprobante, cc.Anulacion, fe.ID_Empresa, fe.Pto_Vta, fe.ID_Responsable, fe.Responsable, fe.Domicilio, fe.Documento
                    FROM facturas_emitidas fe
                    INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                    WHERE fe.B=0 and fe.Id={$id_comprobante}
                    ");
        
            $ctrl_anulacion=count($verificacion);
            if($ctrl_anulacion>=1)
                {
                    //NO TIENE ANULACIONES
                    $importe_o=$verificacion[0]->Importe;
                    $numero_o=$verificacion[0]->Numero;
                    $tipo_factura_o=$verificacion[0]->Tipo_Factura;
                    $id_empresa_o=$verificacion[0]->ID_Empresa;
                    $pto_vta_o=$verificacion[0]->Pto_Vta;
                    $id_responsable_o=$verificacion[0]->ID_Responsable;
                    $responsable_o=$verificacion[0]->Responsable;
                    $domicilio_o=$verificacion[0]->Domicilio;
                    $documento_o=$verificacion[0]->Documento;
                    $Letra_Comprobante=$verificacion[0]->Comprobante;
                    $Observacion='Nota de Débito por Anulación de Nota de Crédito '.$Letra_Comprobante.'-'.$numero_o;
                    $Observacion=trim(utf8_encode($Observacion));

                    /*
                    $response = $this->nota_credito($id_institucion, $id_comprobante);
                    if (is_array($response)) {
                        $responseData = $response;
                    } else {
                        // Decodificar la respuesta JSON
                        $responseData = json_decode($response, true);
                    }
                    
                    $data = $responseData['data'];
                    if (!empty($data)) {
                        // Obtener los valores de "CAE", "CAEFchVto" y "Numero"
                        $cae = $data[0]['CAE'];
                        $caefchvto = $data[0]['CAEFchVto'];
                        $numero_nuevo  = $data[0]['Numero'];

                    } 

                    //$data = $res;

                    //$CAE_Obtenido = $data['CAE'];
                    //$Fecha_Obtenida = $data['CAEFchVto'];
                    */


                    $comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fe.Numero, fe.Tipo_Factura, fe.ID_Empresa, fe.Pto_Vta, fe.ID_Responsable, fe.Importe
                            FROM facturas_emitidas fe
                            WHERE fe.B=0 and fe.Id={$id_comprobante}
                            ");
                    $numero_comprobante=$comprobantes[0]->Numero;
                    $tipo_comprobante=$comprobantes[0]->Tipo_Factura;
                    $id_empresa=$comprobantes[0]->ID_Empresa;
                    $pto_vta=$comprobantes[0]->Pto_Vta;
                    $ID_Responsable=$comprobantes[0]->ID_Responsable;
                    $Importe=$comprobantes[0]->Importe;

                    $empresas = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT emp.CUIT, emp.Afip_key, emp.Afip_scr, emp.Produccion
                                FROM empresas emp
                                WHERE emp.B=0 and emp.ID={$id_empresa}
                                ");
                    $cuit=$empresas[0]->CUIT;
                    $key=$empresas[0]->Afip_key;
                    $cert=$empresas[0]->Afip_scr;
                    $produccion=$empresas[0]->Produccion;

                    $responsables = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT re.DNI, re.CUIT
                                FROM responsabes_economicos re
                                WHERE re.Id={$ID_Responsable}
                                ");
                    $dni_r=$responsables[0]->DNI;
                    $cuit_r=$responsables[0]->CUIT;
                    


                    $afip = new Afip(array(
                        'CUIT' => $cuit,
                        //'CUIT' => '23284542819',
                        'cert' 		=> $cert,
                        'key' 		=> $key
                        //'production' => $produccion
                        //'access_token' => 'UDbn6i9Yho7YGcG1tuZNSMN4BfQ8dWJrHACbLVZdTJ5uLtKncSbmrgn9RqOIxCFB'
                        ));
                    
                    $punto_de_venta = $pto_vta;

                    /**
                     * Tipo de Nota de Crédito
                     **/
                    $tipo_de_nota = 12; // 12 = Nota de Débito C
                    
                    /**
                     * Número de la ultima Nota de Débito C
                     **/
                    $last_voucher = $afip->ElectronicBilling->GetLastVoucher($punto_de_venta, $tipo_de_nota);
                    
                    /**
                     * Numero del punto de venta de la Factura 
                     * asociada a la Nota de Crédito
                     **/
                    $punto_factura_asociada = $pto_vta;
                    
                    /**
                     * Tipo de Factura asociada a la Nota de Crédito
                     **/
                    $tipo_factura_asociada = $tipo_comprobante; // 13 = Nota Crédito
                    
                    /**
                     * Numero de Factura asociada a la Nota de Crédito
                     **/
                    $numero_factura_asociada = $numero_comprobante;
                    
                    /**
                     * Concepto de la Nota de Crédito
                     *
                     * Opciones:
                     *
                     * 1 = Productos 
                     * 2 = Servicios 
                     * 3 = Productos y Servicios
                     **/
                    $concepto = 2;
                    
                    /**
                     * Tipo de documento del comprador
                     *
                     * Opciones:
                     *
                     * 80 = CUIT 
                     * 86 = CUIL 
                     * 96 = DNI
                     * 99 = Consumidor Final 
                     **/
                    if($cuit_r==0)
                        {
                            $tipo_de_documento = 96;
                            $numero_de_documento = $dni_r;
                        }
                    else
                        {
                            $tipo_de_documento = 80;
                            $numero_de_documento = $cuit_r;
                        }
                
                    
                    /**
                     * Numero de comprobante
                     **/
                    $numero_de_nota = $last_voucher+1;
                    
                    /**
                     * Fecha de la Nota de Crédito en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
                     **/
                    $fecha = date('Y-m-d');
                    
                    /**
                     * Importe de la Nota de Crédito
                     **/
                    $importe_total = $Importe;
                    
                    /**
                     * Los siguientes campos solo son obligatorios para los conceptos 2 y 3
                     **/
                    if ($concepto === 2 || $concepto === 3) {
                        /**
                         * Fecha de inicio de servicio en formato aaaammdd
                         **/
                        $fecha_servicio_desde = intval(date('Ymd'));
                    
                        /**
                         * Fecha de fin de servicio en formato aaaammdd
                         **/
                        $fecha_servicio_hasta = intval(date('Ymd'));
                    
                        /**
                         * Fecha de vencimiento del pago en formato aaaammdd
                         **/
                        $fecha_vencimiento_pago = intval(date('Ymd'));
                    }
                    else {
                        $fecha_servicio_desde = null;
                        $fecha_servicio_hasta = null;
                        $fecha_vencimiento_pago = null;
                    }
                    
                    
                    $data = array(
                        'CantReg' 	=> 1, // Cantidad de Notas de Crédito a registrar
                        'PtoVta' 	=> $punto_de_venta,
                        'CbteTipo' 	=> $tipo_de_nota, 
                        'Concepto' 	=> $concepto,
                        'DocTipo' 	=> $tipo_de_documento,
                        'DocNro' 	=> $numero_de_documento,
                        'CbteDesde' => $numero_de_nota,
                        'CbteHasta' => $numero_de_nota,
                        'CbteFch' 	=> intval(str_replace('-', '', $fecha)),
                        'FchServDesde'  => $fecha_servicio_desde,
                        'FchServHasta'  => $fecha_servicio_hasta,
                        'FchVtoPago'    => $fecha_vencimiento_pago,
                        'ImpTotal' 	=> $importe_total,
                        'ImpTotConc'=> 0, // Importe neto no gravado
                        'ImpNeto' 	=> $importe_total, // Importe neto
                        'ImpOpEx' 	=> 0, // Importe exento al IVA
                        'ImpIVA' 	=> 0, // Importe de IVA
                        'ImpTrib' 	=> 0, //Importe total de tributos
                        'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante ('PES' = pesos argentinos) 
                        'MonCotiz' 	=> 1, // Cotización de la moneda usada (1 para pesos argentinos)  
                        'CbtesAsoc' => array( //Factura asociada
                            array(
                                'Tipo' 		=> $tipo_factura_asociada,
                                'PtoVta' 	=> $punto_factura_asociada,
                                'Nro' 		=> $numero_factura_asociada,
                            )
                        )
                    );
                    
                    /** 
                     * Creamos la Nota de Débito
                     **/
                    $res = $afip->ElectronicBilling->CreateVoucher($data);
                    $res["Numero"] = $numero_de_nota;

                    $data = $res;

                    $numero_nuevo = $numero_de_nota;
                    $cae = $data['CAE'];
                    $caefchvto = $data['CAEFchVto'];
        
        
                    
                    //if ($response['success']) {
                        // Obtener los valores de CAE y CAEFchVto
                        //$data0 = json_decode($response, true);
                        //$data = $response['data'];
                        //$data = json_decode($response['data'], true);
                        //$cae = $data['CAE'];
                        //$caefchvto = $data['CAEFchVto'];
                        //$numero_nuevo = $data['Numero'];
                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO facturas_emitidas
                                    (Numero,Tipo_Factura,Fecha,Importe,CAE,Vto,Observaciones,ID_Empresa,Pto_Vta,ID_Responsable,Responsable,Domicilio,Documento,ID_Anulacion,ID_Usuario)
                                    VALUES ({$numero_nuevo},'12','{$FechaActual}','{$Importe}','{$cae}','{$caefchvto}','{$Observacion}',{$id_empresa_o},{$pto_vta_o},{$id_responsable_o},'{$responsable_o}','{$domicilio_o}','{$documento_o}',{$id_comprobante},{$id_usuario})
                                ");
                        //CONSULTO ID NUEVO
                        $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fe.Id, re.Email
                            FROM facturas_emitidas fe
                            INNER JOIN responsabes_economicos re ON fe.ID_Responsable=re.Id
                            WHERE fe.B=0 and fe.Tipo_Factura=12 and fe.Numero={$numero_nuevo} and fe.CAE='{$cae}'
                            ");
                        $id_nota_credito=$check_insercion[0]->Id;
                        $email_r=$check_insercion[0]->Email;
                        //ACTUALIZO COMPROBANTE ANULADO
                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE facturas_emitidas
                                    SET ID_Anulacion={$id_nota_credito}
                                    WHERE Id={$id_comprobante}
                                ");
                        //GENERO ENVIO POR CORREO
                        $cadenaAleatoria = Str::random(25);
                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO envio_comprobantes
                                    (ID_Comprobante,Tipo_Comprobante,Fecha,Hora,MailD,Destinatario,Aleatorio)
                                    VALUES ({$id_nota_credito},'3','{$FechaActual}','{$HoraActual}','{$email_r}','{$responsable_o}','{$cadenaAleatoria}')
                                ");
                        $resultado='La nota de Débito ha sido emitida con éxito. El número asignado es '.$numero_nuevo.' y su CAE '.$cae;
                    /*
                    } else {
                        $resultado='error';
                    }
                    */

                }
            else
                {
                    //YA FUE ANULADO
                    $resultado='error';
                }
                    
          return $resultado;
        }


        public function estadisticas($id, $id_empresa, $desde, $hasta)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;
          $f_inicio=$desde;
          $f_fin=$hasta;

          $filtros=''; 
            if ($id_empresa!=0) $filtros.="fe.ID_Empresa='$id_empresa' AND "; 
            if ($desde!='') $filtros.="fe.Fecha>='$desde' AND ";
            if ($hasta!='') $filtros.="fe.Fecha<='$hasta' AND ";

          if ($filtros!='') 
            { 
                $filtros=substr($filtros,0,strlen($filtros)-5); //se quita ultimo -AND-
            }
            
            $total_emitido=0;
            $filtros2=''; 
            
            if ($desde!='') $filtros2.="Fecha>='$desde' AND ";
            if ($hasta!='') $filtros2.="Fecha<='$hasta' AND ";

            if ($filtros2!='') 
                { 
                    $filtros2=substr($filtros2,0,strlen($filtros2)-5); //se quita ultimo -AND-
                    $consulta_emision_comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT SUM(Importe) AS Total
                            FROM cuenta_corriente
                            WHERE B=0 AND Id_Tipo_Comprobante=2 AND $filtros2
                        ");
                    $total_comprobantes=$consulta_emision_comprobantes[0]->Total;
                    $consulta_emision_recargos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM cuenta_corriente
                                WHERE B=0 AND Id_Tipo_Comprobante=7 AND $filtros2
                            ");
                    $total_intereses=$consulta_emision_recargos[0]->Total;
                    $consulta_emision_adicionales = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM cuenta_corriente
                                WHERE B=0 AND Id_Tipo_Comprobante>=9 AND $filtros2
                            ");
                    $total_adicionales=$consulta_emision_adicionales[0]->Total;
                }
            else
                {
                    $consulta_emision_comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT SUM(Importe) AS Total
                            FROM cuenta_corriente
                            WHERE B=0 AND Id_Tipo_Comprobante=2
                        ");
                    $total_comprobantes=$consulta_emision_comprobantes[0]->Total;
                    $consulta_emision_recargos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM cuenta_corriente
                                WHERE B=0 AND Id_Tipo_Comprobante=7
                            ");
                    $total_intereses=$consulta_emision_recargos[0]->Total;
                    $consulta_emision_adicionales = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM cuenta_corriente
                                WHERE B=0 AND Id_Tipo_Comprobante>=9
                            ");
                    $total_adicionales=$consulta_emision_adicionales[0]->Total;
                }
            
            

            $total_emitido=$total_comprobantes+$total_intereses+$total_adicionales;
            

            //TOTAL FACTURADO
            if ($filtros!='')
                {
                    $consulta_emision_facturas = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(fe.Importe) AS Total
                        FROM facturas_emitidas fe
                        WHERE fe.B=0 AND $filtros
                    ");
                    //$total_facturado=$consulta_emision_facturas[0]->Total;
                }
            else
                {
                    $consulta_emision_facturas = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT SUM(fe.Importe) AS Total
                    FROM facturas_emitidas fe
                    WHERE fe.B=0
                ");
                //$total_facturado=$consulta_emision_facturas[0]->Total;
                }

            $total_facturado=$consulta_emision_facturas[0]->Total;
            if($total_facturado>=1)
                {

                }
            else
                {
                    $total_facturado=0;
                }
            //$total_emitido=1;
            if($total_emitido>=1)
                {
                    $coef_facturacion=$total_facturado/$total_emitido;
                    $p_facturacion=round($coef_facturacion*100);
                }
            else
                {
                    
                    $p_facturacion=0;
                }
            

            $resultado[0] = array(
                'total_emitido' => $total_emitido,
                'total_facturado'=> $total_facturado,
                'porcentaje_facturado'=> $p_facturacion
          );
       
          return $resultado;
        }


    public function nota_credito($id, $id_comprobante)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
        
          $comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT fe.Numero, fe.Tipo_Factura, fe.ID_Empresa, fe.Pto_Vta, fe.ID_Responsable, fe.Importe
                    FROM facturas_emitidas fe
                    WHERE fe.B=0 and fe.Id={$id_comprobante}
                    ");
           $numero_comprobante=$comprobantes[0]->Numero;
           $tipo_comprobante=$comprobantes[0]->Tipo_Factura;
           $id_empresa=$comprobantes[0]->ID_Empresa;
           $pto_vta=$comprobantes[0]->Pto_Vta;
           $ID_Responsable=$comprobantes[0]->ID_Responsable;
           $Importe=$comprobantes[0]->Importe;

           $empresas = $this->dataBaseService->selectConexion($id_institucion)->select("
           SELECT emp.CUIT, emp.Afip_key, emp.Afip_scr, emp.Produccion
           FROM empresas emp
           WHERE emp.B=0 and emp.ID={$id_empresa}
           ");
            $cuit=$empresas[0]->CUIT;
            $key=$empresas[0]->Afip_key;
            $cert=$empresas[0]->Afip_scr;
            $produccion=$empresas[0]->Produccion;

           $responsables = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT re.DNI, re.CUIT
                    FROM responsabes_economicos re
                    WHERE re.Id={$ID_Responsable}
                    ");
           $dni_r=$responsables[0]->DNI;
           $cuit_r=$responsables[0]->CUIT;
           


          $afip = new Afip(array(
            'CUIT' => $cuit,
            //'CUIT' => '23284542819',
            'cert' 		=> $cert,
            'key' 		=> $key
            //'production' => $produccion
            //'access_token' => 'UDbn6i9Yho7YGcG1tuZNSMN4BfQ8dWJrHACbLVZdTJ5uLtKncSbmrgn9RqOIxCFB'
            ));
        
        $punto_de_venta = $pto_vta;

            /**
             * Tipo de Nota de Crédito
             **/
            $tipo_de_nota = 13; // 13 = Nota de Crédito C
            
            /**
             * Número de la ultima Nota de Crédito C
             **/
            $last_voucher = $afip->ElectronicBilling->GetLastVoucher($punto_de_venta, $tipo_de_nota);
            
            /**
             * Numero del punto de venta de la Factura 
             * asociada a la Nota de Crédito
             **/
            $punto_factura_asociada = $pto_vta;
            
            /**
             * Tipo de Factura asociada a la Nota de Crédito
             **/
            $tipo_factura_asociada = $tipo_comprobante; // 11 = Factura C
            
            /**
             * Numero de Factura asociada a la Nota de Crédito
             **/
            $numero_factura_asociada = $numero_comprobante;
            
            /**
             * Concepto de la Nota de Crédito
             *
             * Opciones:
             *
             * 1 = Productos 
             * 2 = Servicios 
             * 3 = Productos y Servicios
             **/
            $concepto = 2;
            
            /**
             * Tipo de documento del comprador
             *
             * Opciones:
             *
             * 80 = CUIT 
             * 86 = CUIL 
             * 96 = DNI
             * 99 = Consumidor Final 
             **/
            if($cuit_r==0)
                {
                    $tipo_de_documento = 96;
                    $numero_de_documento = $dni_r;
                }
            else
                {
                    $tipo_de_documento = 80;
                    $numero_de_documento = $cuit_r;
                }
           
            
            /**
             * Numero de comprobante
             **/
            $numero_de_nota = $last_voucher+1;
            
            /**
             * Fecha de la Nota de Crédito en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
             **/
            $fecha = date('Y-m-d');
            
            /**
             * Importe de la Nota de Crédito
             **/
            $importe_total = $Importe;
            
            /**
             * Los siguientes campos solo son obligatorios para los conceptos 2 y 3
             **/
            if ($concepto === 2 || $concepto === 3) {
                /**
                 * Fecha de inicio de servicio en formato aaaammdd
                 **/
                $fecha_servicio_desde = intval(date('Ymd'));
            
                /**
                 * Fecha de fin de servicio en formato aaaammdd
                 **/
                $fecha_servicio_hasta = intval(date('Ymd'));
            
                /**
                 * Fecha de vencimiento del pago en formato aaaammdd
                 **/
                $fecha_vencimiento_pago = intval(date('Ymd'));
            }
            else {
                $fecha_servicio_desde = null;
                $fecha_servicio_hasta = null;
                $fecha_vencimiento_pago = null;
            }
            
            
            $data = array(
                'CantReg' 	=> 1, // Cantidad de Notas de Crédito a registrar
                'PtoVta' 	=> $punto_de_venta,
                'CbteTipo' 	=> $tipo_de_nota, 
                'Concepto' 	=> $concepto,
                'DocTipo' 	=> $tipo_de_documento,
                'DocNro' 	=> $numero_de_documento,
                'CbteDesde' => $numero_de_nota,
                'CbteHasta' => $numero_de_nota,
                'CbteFch' 	=> intval(str_replace('-', '', $fecha)),
                'FchServDesde'  => $fecha_servicio_desde,
                'FchServHasta'  => $fecha_servicio_hasta,
                'FchVtoPago'    => $fecha_vencimiento_pago,
                'ImpTotal' 	=> $importe_total,
                'ImpTotConc'=> 0, // Importe neto no gravado
                'ImpNeto' 	=> $importe_total, // Importe neto
                'ImpOpEx' 	=> 0, // Importe exento al IVA
                'ImpIVA' 	=> 0, // Importe de IVA
                'ImpTrib' 	=> 0, //Importe total de tributos
                'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante ('PES' = pesos argentinos) 
                'MonCotiz' 	=> 1, // Cotización de la moneda usada (1 para pesos argentinos)  
                'CbtesAsoc' => array( //Factura asociada
                    array(
                        'Tipo' 		=> $tipo_factura_asociada,
                        'PtoVta' 	=> $punto_factura_asociada,
                        'Nro' 		=> $numero_factura_asociada,
                    )
                )
            );
            
            /** 
             * Creamos la Nota de Crédito 
             **/
            $res = $afip->ElectronicBilling->CreateVoucher($data);
            $res["Numero"] = $numero_de_nota;


            return $res;
        }

        public function ver_factura_emitida($id, $numero_comprobante, $id_empresa)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
        
          $empresas = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT emp.CUIT, emp.Afip_key, emp.Afip_scr, emp.Pto_Vta, emp.Produccion
                    FROM empresas emp
                    WHERE emp.B=0 and emp.ID={$id_empresa}
                    ");
           $cuit=$empresas[0]->CUIT;
           $key=$empresas[0]->Afip_key;
           $cert=$empresas[0]->Afip_scr;
           $produccion=$empresas[0]->Produccion;
           $pto_vta=$empresas[0]->Pto_Vta;
           
           
          $afip = new Afip(array(
            'CUIT' => $cuit,
            'cert' 		=> $cert,
            'key' 		=> $key
            //'production' => $produccion
            //'access_token' => 'UDbn6i9Yho7YGcG1tuZNSMN4BfQ8dWJrHACbLVZdTJ5uLtKncSbmrgn9RqOIxCFB'
            ));
                
            /**
         * Numero de factura
         **/
        $numero_de_factura = $numero_comprobante;

        /**
         * Numero del punto de venta
         **/
        $punto_de_venta = $pto_vta;

        /**
         * Tipo de comprobante
         **/
        $tipo_de_comprobante = 11; // 11 = Factura C 13 Nota de Credito C

        /**
         * Informacion de la factura
         **/
        $informacion = $afip->ElectronicBilling->GetVoucherInfo($numero_de_factura, $punto_de_venta, $tipo_de_comprobante); 

        if($informacion === NULL){
            echo 'La factura no existe';
        }
        else{
            /**
             * Mostramos por pantalla la información de la factura
             **/
            var_dump($informacion);
        }



                    return $res;
                }

        public function ver_nota_credito($id, $numero_comprobante, $id_empresa)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
        
          $empresas = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT emp.CUIT, emp.Afip_key, emp.Afip_scr, emp.Pto_Vta, emp.Produccion
                    FROM empresas emp
                    WHERE emp.B=0 and emp.ID={$id_empresa}
                    ");
           $cuit=$empresas[0]->CUIT;
           $key=$empresas[0]->Afip_key;
           $cert=$empresas[0]->Afip_scr;
           $produccion=$empresas[0]->Produccion;
           $pto_vta=$empresas[0]->Pto_Vta;
           
           
          $afip = new Afip(array(
            'CUIT' => $cuit,
            'cert' 		=> $cert,
            'key' 		=> $key
            //'production' => $produccion
            //'access_token' => 'UDbn6i9Yho7YGcG1tuZNSMN4BfQ8dWJrHACbLVZdTJ5uLtKncSbmrgn9RqOIxCFB'
            ));
                
            /**
         * Numero de factura
         **/
        $numero_de_factura = $numero_comprobante;

        /**
         * Numero del punto de venta
         **/
        $punto_de_venta = $pto_vta;

        /**
         * Tipo de comprobante
         **/
        $tipo_de_comprobante = 13; // 11 = Factura C 13 Nota de Credito C

        /**
         * Informacion de la factura
         **/
        $informacion = $afip->ElectronicBilling->GetVoucherInfo($numero_de_factura, $punto_de_venta, $tipo_de_comprobante); 

        if($informacion === NULL){
            echo 'La factura no existe';
        }
        else{
            /**
             * Mostramos por pantalla la información de la factura
             **/
            $res = $informacion;
        }


                    return $res;
                }


    public function ver_modelo_factura($id, $id_movimiento_caja)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $ID_Movimiento_Caja=$id_movimiento_caja;
          $array_facturacion=array();
          $Importe_Restante=0;

          //CONSULTA DETOS DEL MOVIMIENTO DE CAJA
          $datos_movimiento = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT mc.Fecha,mc.ID_Tipo_Movimiento,mc.ID_Responsable,mc.Importe,mc.ID_Medio_Pago,mc.Detalle,mc.Facturado, re.Nombre, re.Apellido
                FROM movimientos_caja mc
                INNER JOIN responsabes_economicos re ON mc.ID_Responsable=re.Id
                WHERE mc.ID=$ID_Movimiento_Caja
                ");
          $Sumatoria_Conceptos=0;
          $Fecha_Operacion=$datos_movimiento[0]->Fecha;
          $ID_Tipo_Movimiento=$datos_movimiento[0]->ID_Tipo_Movimiento;
          $Apellido_R=trim(utf8_decode($datos_movimiento[0]->Apellido));
          $Nombre_R=trim(utf8_decode($datos_movimiento[0]->Nombre));
          $Responsable=$Apellido_R.', '.$Nombre_R;
          $ID_Responsable=$datos_movimiento[0]->ID_Responsable;
          $Importe_Cobrado=$datos_movimiento[0]->Importe;
          $ID_Medio_Pago=$datos_movimiento[0]->ID_Medio_Pago;
          $Detalle=trim(utf8_decode($datos_movimiento[0]->Detalle));
          $Facturado=$datos_movimiento[0]->Facturado;

          $datos_tipo_movimiento = $this->dataBaseService->selectConexion($id_institucion)->select("
          SELECT ctm.Nombre
          FROM caja_tipo_movimiento ctm
          WHERE ctm.Id=$ID_Tipo_Movimiento
          ");
          $Tipo_Movimiento=$datos_tipo_movimiento[0]->Nombre;

          $datos_medio_pago = $this->dataBaseService->selectConexion($id_institucion)->select("
          SELECT mp.Nombre
          FROM medios_pago mp
          WHERE mp.Id=$ID_Medio_Pago
          ");
          $Medio_Pago=$datos_medio_pago[0]->Nombre;

          $resultado[0] = array(
                    'fecha' => $Fecha_Operacion,
                    'responsable'=> $Responsable,
                    'importe_cobrado'=> $Importe_Cobrado,
                    'observaciones'=> $Detalle,
                    'facturado'=> $Facturado,
                    'medio_pago'=> $Medio_Pago
            );

          $array_estudiantes=array();
          
          $datos_alumnos_vinculados = $this->dataBaseService->selectConexion($id_institucion)->select("
          SELECT av.Id_Alumno, av.Id
          FROM alumnos_vinculados av
          WHERE av.ID_Responsable=$ID_Responsable and av.B=0
          ");
          $Cantidad_Alumnos_Vinculados=count($datos_alumnos_vinculados);
          
          $Numero_Alumno_Vinculado=0;
          for ($j=0; $j < count($datos_alumnos_vinculados); $j++)
            {
                $Numero_Alumno_Vinculado++;
                $ID_Alumno_Vinculado=$datos_alumnos_vinculados[$j]->Id_Alumno;
                if($Cantidad_Alumnos_Vinculados==1)
                    {
                        $ID_Alumno_Default=$ID_Alumno_Vinculado;
                    }
                else
                    {
                        $ID_Alumno_Default=$datos_alumnos_vinculados[0]->Id_Alumno;
                    }
                //CONSULTO DATOS DE ESTUDIANTE
                $headers = [
                    'Content-Type: application/json',
                ];
                $curl = curl_init();
                $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno_Vinculado;
                curl_setopt($curl, CURLOPT_URL,  $ruta_api);
                //curl_setopt($curl, CURLOPT_URL,  'http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/3?id=1');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); /**Permitimos recibir respuesta*/
                curl_setopt($curl, CURLOPT_HTTPGET,true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_POST, false);
                //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); /** Archivo donde guardamos datos de sesion */
                $data = curl_exec($curl); /** Ejecutamos petición*/
                curl_close($curl);

                //$data=dd($data);
                $data = json_decode($data, true);
                $datos_alumno = $data['data'];

                foreach($datos_alumno as $estudiante) {

                    $Nombre_A=$estudiante["nombre"];
                    $Apellido_A=$estudiante["apellido"];
                    $Curso_A=$estudiante["curso"];
                    $ID_Curso_A=$estudiante["id_curso"];
                    $ID_Nivel_A=$estudiante["id_nivel"];

                }

                $resultado[0]['estudiantes'][$j] = array(
                                                   'id'=> $datos_alumnos_vinculados[$j]->Id,
                                                    'id_alumno'=> $ID_Alumno_Vinculado,
                                                    'apellido'=> $Apellido_A,
                                                    'nombre'=> $Nombre_A,
                                                    'id_curso' => $ID_Curso_A,
                                                    'curso' => $Curso_A,
                                                    'id_nivel' => $ID_Nivel_A,
                                                    'numero_alumno' => $Numero_Alumno_Vinculado
                                              );
            }

            //DETALLES DE LA FACTURA
            //CONSULTA IMPUTACIONES
            $datos_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT ci.ID_Comprobante, ci.ID_Cta_Cte, ci.Importe, ci.Cancela, ci.ID
                FROM comprobantes_imputaciones ci
                WHERE ci.ID_Movimiento=$ID_Movimiento_Caja and ci.B=0 and ci.Facturado=0
                ");
            $datos_imputaciones_resueltas = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT ci.Importe
                FROM comprobantes_imputaciones ci
                WHERE ci.ID_Movimiento=$ID_Movimiento_Caja and ci.B=0 and ci.Facturado>=1
                ");
            $Cantidad_Imputaciones_Totales=count(($datos_imputaciones));
            $Cantidad_Imputaciones_Resueltas=count($datos_imputaciones_resueltas);
            if($Cantidad_Imputaciones_Resueltas>=1)
                {
                    $Importe_ya_cobrado=0;
                    for ($ji=0; $ji < count($datos_imputaciones_resueltas); $ji++)
                        {
                            //$ID_Comprobante=$datos_imputaciones[$ji]->ID_Comprobante;
                            //$ID_Cta_Cte=$datos_imputaciones[$ji]->ID_Cta_Cte;
                            $Importe_Imputado=$datos_imputaciones_resueltas[$ji]->Importe;
                            $Importe_ya_cobrado=$Importe_ya_cobrado+$Importe_Imputado;
                            //$Importe_Cobrado=$Importe_Cobrado-$Importe_Imputado;
                        }
                     $Importe_Restante=$Importe_Cobrado-$Importe_ya_cobrado;
                }
            
            $Cantidad_Imputaciones=count($datos_imputaciones);
            if($Cantidad_Imputaciones==0)
                {
                    $Observaciones='';
                    //CONSULTO OTRO MOVIMIENTO PARA DEFINIR EMPRESA QUE FACTURA
                    $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote
                        FROM cuenta_corriente cc
                        INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                        INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                        INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                        WHERE cc.ID_Responsable=$ID_Responsable and cc.Id_Tipo_Comprobante=2 and cc.B=0
                        ORDER BY cc.Id desc LIMIT 1
                        ");
                    if($ID_Alumno_Default>=1)
                        {
                            $ID_Estudiante=$ID_Alumno_Default;
                        }
                    else
                        {
                            $ID_Estudiante=0;
                        }
                    $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                    $Empresa=trim(utf8_decode($datos_extra_empresa[0]->Empresa));
                    $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                    $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                    $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                    $resultado[0]['conceptos'][0] = array(
                         'id_empresa'=> $ID_Empresa,
                         'empresa'=> $Empresa,
                         'id_pto_vta'=> $Pto_Vta,
                         'id_responsable'=> $ID_Responsable,
                         'id_estudiante'=> $ID_Estudiante,
                         'id_nivel' => $ID_Nivel_A,
                         'descripcion'=> $Detalle,
                         'importe'=> $Importe_Cobrado,
                         'observaciones' => $Observaciones,
                         'id_periodo' => 3,
                         'id' => $ID_Movimiento_Caja,
                         'tipo' => 0,
                         'alarma'=> 0,
                         'descripcion_alarma'=> ''

                   );
                    
                }
            else
                {
                    if($Cantidad_Imputaciones_Resueltas>=1)
                        {

                        }
                    else
                        {
                            $Importe_Restante=$Importe_Cobrado;
                        }
                    
                    
                    $Observaciones='';
                    $Sumatoria_Conceptos=0;
                    $contador_conceptos=0;
                    $Cant_Cuotas_Detalle=0;

                    $datos_alumnos_vinculados = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT av.Id_Alumno, av.Id
                    FROM alumnos_vinculados av
                    WHERE av.ID_Responsable=$ID_Responsable and av.B=0
                    ");
                    $Cantidad_Alumnos_Vinculados=count($datos_alumnos_vinculados);
                    

                    for ($p=0; $p < count($datos_alumnos_vinculados); $p++)
                        {
                            $ID_Alumno_Vinculado=$datos_alumnos_vinculados[$p]->Id_Alumno;
                            $Total_Comprobantes_por_alumno=0;
                            for ($j=0; $j < count($datos_imputaciones); $j++)
                                {
                                    $ID_Tip_Com=$datos_imputaciones[$j]->ID_Cta_Cte;
                                    $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT cc.Id_Tipo_Comprobante
                                        FROM cuenta_corriente cc
                                        WHERE cc.Id=$ID_Tip_Com and cc.B=0 and cc.Facturado=0 and Id_Tipo_Comprobante=2 and ID_Alumno={$ID_Alumno_Vinculado}
                                        ");
                                    $Ctrl_Cuta=count($datos_cuenta_corriente);
                                    if($Ctrl_Cuta>=1)
                                        {
                                            $Total_Comprobantes_por_alumno++;
                                        }

                                }
                            
                            $alumnosArray[] = array(
                                'id_alumno' => $ID_Alumno_Vinculado,
                                'cant_comprobante' => $Total_Comprobantes_por_alumno
                            );

                        }

                        
                    for ($j=0; $j < count($datos_imputaciones); $j++)
                        {
                            $ID_Tip_Com=$datos_imputaciones[$j]->ID_Cta_Cte;
                            $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cc.Id_Tipo_Comprobante
                                FROM cuenta_corriente cc
                                WHERE cc.Id=$ID_Tip_Com and cc.B=0 and cc.Facturado=0 and Id_Tipo_Comprobante=2
                                ");
                            $Ctrl_Cuta=count($datos_cuenta_corriente);
                            if($Ctrl_Cuta>=1)
                                {
                                    $Cant_Cuotas_Detalle++;
                                }

                        }



                    for ($j=0; $j < count($datos_imputaciones); $j++)
                        {
                            $ID_Comprobante=$datos_imputaciones[$j]->ID_Comprobante;
                            $ID_Cta_Cte=$datos_imputaciones[$j]->ID_Cta_Cte;
                            $Importe_Imputado=$datos_imputaciones[$j]->Importe;
                            //$sumatoria_conceptos=$sumatoria_conceptos+$Importe_Imputado;
                            $Estado_Cancelacion=$datos_imputaciones[$j]->Cancela;
                            $ID_Imputacion=$datos_imputaciones[$j]->ID;
                            $Importe_Restante=$Importe_Restante-$Importe_Imputado;
                            $consulta_imputaciones_anteriores = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT SUM(Importe) AS Total
                                                    FROM comprobantes_imputaciones
                                                    WHERE B=0 AND ID_Cta_Cte={$ID_Cta_Cte}
                                                ");
                                            
                                            $total_imputado=$consulta_imputaciones_anteriores[0]->Total;

                                            $consulta_imputaciones_anteriores = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT ID
                                                    FROM comprobantes_imputaciones
                                                    WHERE B=0 AND ID_Cta_Cte={$ID_Cta_Cte}
                                                ");

                                            $Cantidad_Imputaciones_Detectadas=count($consulta_imputaciones_anteriores);

                            /*
                            $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cc.ID_Alumno, cc.Fecha, cc.Id_Tipo_Comprobante, cc.Descripcion, cc.Id_Comprobante, cc.Importe, cc.Cancelado, cc.ID_Periodo, cc.ID_Empresa, cc.Facturado
                                FROM cuenta_corriente cc
                                WHERE cc.Id=$ID_Cta_Cte and cc.B=0 and cc.Facturado=0
                                ");
                            */
                            $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cc.ID_Alumno, cc.Fecha, cc.Id_Tipo_Comprobante, cc.Descripcion, cc.Id_Comprobante, cc.Importe, cc.Cancelado, cc.ID_Periodo, cc.ID_Empresa, cc.Facturado
                                FROM cuenta_corriente cc
                                WHERE cc.Id=$ID_Cta_Cte and cc.B=0
                                ");
                            $Facturado_State=$datos_cuenta_corriente[0]->Facturado;
                            if($Facturado_State==1)
                                {

                                }
                            else
                                {
                                    $ID_Estudiante=$datos_cuenta_corriente[0]->ID_Alumno;
                                    $ID_Estu=$ID_Estudiante;
                                    $Fecha_Cta=$datos_cuenta_corriente[0]->Fecha;
                                    $ID_Tipo_Comprobante=$datos_cuenta_corriente[0]->Id_Tipo_Comprobante;
                                    $Descripcion_Movimiento_Cta=trim(utf8_decode($datos_cuenta_corriente[0]->Descripcion));
                                    $ID_Comprobante_Cta=$datos_cuenta_corriente[0]->Id_Comprobante;
                                    $Importe_Movimiento_Cta=$datos_cuenta_corriente[0]->Importe;
                                    //$Estado_Cancelacion=$datos_cuenta_corriente[0]->Cancelado;
                                    $ID_Periodo=$datos_cuenta_corriente[0]->ID_Periodo;
                                    if($ID_Periodo==0)
                                        {
                                            $list_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT Id
                                                    FROM periodos_detalle
                                                    WHERE '{$Fecha_Cta}' BETWEEN Inicio AND Fin
                                                ");

                                            $ID_Periodo=$list_periodo[0]->Id;
                                            if($ID_Periodo>=1)
                                                {
                                                    //ACTUALIZO PERIODO
                                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                                        UPDATE cuenta_corriente
                                                        SET ID_Periodo={$ID_Periodo}
                                                        WHERE ID={$ID_Cta_Cte}
                                                    ");

                                                }
                                        }
                                    $ID_Empresa=$datos_cuenta_corriente[0]->ID_Empresa;
                                    if(empty($ID_Estudiante))
                                        {
                                            //BUSCO EN OTRO MOVIMIENTO LA EMPRESA
                                            if($ID_Alumno_Default>=1)
                                                {
                                                    $ID_Estudiante=$ID_Alumno_Default;
                                                }
                                            else
                                                {
                                                    $ID_Estudiante=0;
                                                }
                                            if(empty($ID_Empresa))
                                                {
                                                    $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                        SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote
                                                        FROM cuenta_corriente cc
                                                        INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                                                        INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                                                        INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                        WHERE cc.ID_Responsable=$ID_Responsable and cc.Id_Tipo_Comprobante=2 and cc.B=0
                                                        ORDER BY cc.Id LIMIT 1
                                                        ");
                                                    $Control_Empresa=count($datos_extra_empresa);
                                                    if(empty($Control_Empresa))
                                                        {
                                                            //BUSCO LA EMPRESA QUE FACTURA CAMPAANA
                                                            $datos_vinculos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                SELECT fac.Id_Empresa, emp.Empresa, emp.Pto_Vta
                                                                FROM campanas_alcance ca
                                                                INNER JOIN facturacion fac ON ca.Id_Campana=fac.Id_Campana
                                                                INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                                WHERE ca.Id_Nivel={$ID_Nivel_A} and ca.B=0
                                                                ");
                                                            $ID_Empresa=$datos_vinculos[0]->Id_Empresa;
                                                            $Empresa=$datos_vinculos[0]->Empresa;
                                                            $Pto_Vta=$datos_vinculos[0]->Pto_Vta;
        
        
        
                                                        }
                                                    else
                                                        {
                                                            $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                                                            $Empresa=$datos_extra_empresa[0]->Empresa;
                                                            $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                                                            $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                                                            $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                                                            //$ID_Periodo=$datos_extra_empresa[0]->ID_Periodo;
                                                        }
                                                }
                                            else
                                                {
                                                    $datos_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                SELECT emp.Empresa, emp.Pto_Vta
                                                                FROM empresas emp
                                                                WHERE emp.ID={$ID_Empresa}
                                                                ");
                                                            
                                                            $Empresa=$datos_empresa[0]->Empresa;
                                                            $Pto_Vta=$datos_empresa[0]->Pto_Vta;
                                                }
                                            
                                            
                                        }
                                    else
                                        {
                                            $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote, cc.ID_Periodo
                                                FROM cuenta_corriente cc
                                                INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                                                INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                                                INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                WHERE cc.ID_Responsable=$ID_Responsable and cc.ID_Alumno=$ID_Estudiante and cc.Id_Tipo_Comprobante=2 and cc.B=0
                                                ORDER BY cc.Id LIMIT 1
                                                ");
                                            if(empty($ID_Empresa))
                                                {
                                                    $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                                                    $Empresa=$datos_extra_empresa[0]->Empresa;
                                                    $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                                                    $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                                                    $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                                                    //$ID_Periodo=$datos_extra_empresa[0]->ID_Periodo;
                                                }
                                            else
                                                {
                                                    $datos_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                SELECT emp.Empresa, emp.Pto_Vta
                                                                FROM empresas emp
                                                                WHERE emp.ID={$ID_Empresa}
                                                                ");    
                                                            $Empresa=$datos_empresa[0]->Empresa;
                                                            $Pto_Vta=$datos_empresa[0]->Pto_Vta;
                                                }
                                            
                                            
                                        }

                                        if($ID_Tipo_Comprobante==2)
                                        {
                                            //BUSCO EL IMPORTE TOTAL DE COMPROBANTE
                                            
                                            $datos_comprobante= $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT Importe
                                            FROM comprobantes
                                            WHERE Id={$ID_Comprobante} and Id_Tipo=2 and B=0
                                            ");
                                            $Importe_Original=$datos_comprobante[0]->Importe;
                                            

                                            if($Importe_Original==$total_imputado)
                                                {
                                                    $Estado_Cancelacion=2;
                                                }
                                            else
                                                {
                                                    $Estado_Cancelacion=1;
                                                }
        
                                        }
        
                                    if($Estado_Cancelacion==1)
                                        {
                                            
                                            
                                            $Detalle='A CUENTA DE ';
                                            //$Detalle=trim(utf8_decode($Detalle));
                                            $Detalle=$Detalle.' - '.$Descripcion_Movimiento_Cta;
                                            $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                            $resultado[0]['conceptos'][$contador_conceptos] = array(
                                                'id_empresa'=> $ID_Empresa,
                                                'empresa'=> trim(utf8_decode($Empresa)),
                                                'id_pto_vta'=> $Pto_Vta,
                                                'id_responsable'=> $ID_Responsable,
                                                'id_estudiante'=> $ID_Estudiante,
                                                'id_nivel' => $ID_Nivel_A,
                                                'descripcion'=> $Detalle,
                                                'importe'=> $Importe_Imputado,
                                                'observaciones' => $Observaciones,
                                                'id_periodo' => $ID_Periodo,
                                                'id' => $ID_Imputacion,
                                                'tipo' => 1,
                                                'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle,
                                                'alumnos_array' => $alumnosArray,
                                                
                                            );
                                            $contador_conceptos++;
                                        }
                                    if($Estado_Cancelacion==2)
                                        {
                                            if($Cantidad_Imputaciones_Detectadas>=2)
                                                {
                                                    $Detalle='CANCELACION DE ';
                                                    //$Detalle=trim(utf8_decode($Detalle));
                                                    $Detalle=$Detalle.' - '.$Descripcion_Movimiento_Cta;
                                                    $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                    $resultado[0]['conceptos'][$contador_conceptos] = array(
                                                        'id_empresa'=> $ID_Empresa,
                                                        'empresa'=> trim(utf8_decode($Empresa)),
                                                        'id_pto_vta'=> $Pto_Vta,
                                                        'id_responsable'=> $ID_Responsable,
                                                        'id_estudiante'=> $ID_Estudiante,
                                                        'id_nivel' => $ID_Nivel_A,
                                                        'descripcion'=> $Detalle,
                                                        'importe'=> $Importe_Imputado,
                                                        'observaciones' => $Observaciones,
                                                        'id_periodo' => $ID_Periodo,
                                                        'id' => $ID_Imputacion,
                                                        'tipo' => 1,
                                                        'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle,
                                                        'alumnos_array' => $alumnosArray,
                                                        
                                                    );
                                                    $contador_conceptos++;
                                                }
                                            else
                                                {
                                                    if($ID_Tipo_Comprobante==2)
                                                    {
                                                        foreach ($alumnosArray as $alumno) {
                                                            $id_alumno_en_array = $alumno['id_alumno'];
                                                            $cant_comprobantes_en_array = $alumno['cant_comprobante'];
                                                            if($id_alumno_en_array==$ID_Estu)
                                                                {
                                                                    if($cant_comprobantes_en_array>=2)
                                                                        {
                                                                            $Cant_Cuotas_Detalle=3;
                                                                        }
                                                                    else
                                                                        {
                                                                            $Cant_Cuotas_Detalle=1;
                                                                        }
                                                                }
                                                           
                                                        
                                                           /* // Aquí puedes realizar la verificación que necesitas para cant_comprobante
                                                            if (($cant_comprobantes_en_array>=2) and ($id_alumno_en_array==$ID_Estudiante)) {
                                                                //$Cant_Cuotas_Detalle++;
                                                                $Cant_Cuotas_Detalle=2;
                                                            } else {
                                                                $Cant_Cuotas_Detalle=1;
                                                            }
                                                            */
                                                        }
                                                        
                                                        if($Cant_Cuotas_Detalle>=2)
                                                            {
                                                                $Detalle=$Descripcion_Movimiento_Cta;
                                                                //$Detalle=utf8_decode($Detalle);
                                                                $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                                $resultado[0]['conceptos'][$contador_conceptos] = array(
                                                                    'id_empresa'=> $ID_Empresa,
                                                                    'empresa'=> trim(utf8_decode($Empresa)),
                                                                    'id_pto_vta'=> $Pto_Vta,
                                                                    'id_responsable'=> $ID_Responsable,
                                                                    'id_estudiante'=> $ID_Estudiante,
                                                                    'id_nivel' => $ID_Nivel_A,
                                                                    'descripcion'=> $Detalle,
                                                                    'importe'=> $Importe_Imputado,
                                                                    'observaciones' => $Observaciones,
                                                                    'id_periodo' => $ID_Periodo,
                                                                    'id' => $ID_Cta_Cte,
                                                                    'tipo' => 3,
                                                                    'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle,
                                                                    'alumnos_array' => $alumnosArray,
                                                                );
                                                                $contador_conceptos++;
                                                            }
                                                        else
                                                            {
                                                                
                                                                $detalle_comprobante = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                SELECT cd.Descripcion, cd.Importe, cd.ID_Tipo_Concepto, cd.Id
                                                                FROM comprobantes_detalles cd
                                                                WHERE cd.ID_Comprobante=$ID_Comprobante and cd.B=0 and cd.Facturado=0
                                                                ");
                                                                for ($p=0; $p < count($detalle_comprobante); $p++)
                                                                    {
                                                                        $Descripcion_Concepto=trim(utf8_decode($detalle_comprobante[$p]->Descripcion));
                                                                        $Importe_Concepto=$detalle_comprobante[$p]->Importe;
                                                                        $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Concepto;
                                                                        $ID_Tipo_Concepto=$detalle_comprobante[$p]->ID_Tipo_Concepto;
                                                                        $ID_Item_Concepto=$detalle_comprobante[$p]->Id;
                                                                        if($ID_Tipo_Concepto==2)
                                                                            {
                                                                                $Importe_Concepto=0-$Importe_Concepto;
                                                                            }
                                                                        $resultado[0]['conceptos'][$contador_conceptos] = array(
                                                                                'id_empresa'=> $ID_Empresa,
                                                                                'empresa'=> trim(utf8_decode($Empresa)),
                                                                                'id_pto_vta'=> $Pto_Vta,
                                                                                'id_responsable'=> $ID_Responsable,
                                                                                'id_estudiante'=> $ID_Estudiante,
                                                                                'id_nivel' => $ID_Nivel_A,
                                                                                'descripcion'=> $Descripcion_Concepto,
                                                                                'importe'=> $Importe_Concepto,
                                                                                'observaciones' => $Observaciones,
                                                                                'id_periodo' => $ID_Periodo,
                                                                                'id' => $ID_Item_Concepto,
                                                                                'tipo' => 2,
                                                                                'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle,
                                                                                'alumnos_array' => $alumnosArray,
                                                                            );
                                                                        $contador_conceptos++;
                                                                    }
                                                              /*
                                                              $Detalle=$Descripcion_Movimiento_Cta;
                                                                $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                                $resultado[0]['conceptos'][$contador_conceptos] = array(
                                                                    'id_empresa'=> $ID_Empresa,
                                                                    'empresa'=> trim(utf8_decode($Empresa)),
                                                                    'id_pto_vta'=> $Pto_Vta,
                                                                    'id_responsable'=> $ID_Responsable,
                                                                    'id_estudiante'=> $ID_Estudiante,
                                                                    'id_nivel' => $ID_Nivel_A,
                                                                    'descripcion'=> $Detalle,
                                                                    'importe'=> $Importe_Imputado,
                                                                    'observaciones' => $Observaciones,
                                                                    'id_periodo' => $ID_Periodo,
                                                                    'id' => $ID_Cta_Cte,
                                                                    'tipo' => 3
                                                                );
                                                                $contador_conceptos++;
                                                                */
                                                            }
    
                                                          
            
                                                    }
                                                else
                                                    {
                                                        
                                                        $Detalle=$Descripcion_Movimiento_Cta;
                                                        //$Detalle=utf8_decode($Detalle);
                                                        $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                        $resultado[0]['conceptos'][$contador_conceptos] = array(
                                                            'id_empresa'=> $ID_Empresa,
                                                            'empresa'=> trim(utf8_decode($Empresa)),
                                                            'id_pto_vta'=> $Pto_Vta,
                                                            'id_responsable'=> $ID_Responsable,
                                                            'id_estudiante'=> $ID_Estudiante,
                                                            'id_nivel' => $ID_Nivel_A,
                                                            'descripcion'=> $Detalle,
                                                            'importe'=> $Importe_Imputado,
                                                            'observaciones' => $Observaciones,
                                                            'id_periodo' => $ID_Periodo,
                                                            'id' => $ID_Cta_Cte,
                                                            'tipo' => 3,
                                                            'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle,
                                                            'alumnos_array' => $alumnosArray,
                                                        );
                                                        $contador_conceptos++;
                                                    }
                                                }
                                            
                                        }
        

                                }

                        }
                }
        if($Importe_Restante>=1)
                {
                    //QUEDA UN MONTO A CUENTA
                    $Observaciones='';
                    $Detalle='A CUENTA DE PROXIMOS PERIODOS';
                    //$Detalle=trim(utf8_encode($Detalle));
                    //CONSULTO OTRO MOVIMIENTO PARA DEFINIR EMPRESA QUE FACTURA
                    $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote
                        FROM cuenta_corriente cc
                        INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                        INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                        INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                        WHERE cc.ID_Responsable=$ID_Responsable and cc.Id_Tipo_Comprobante=2 and cc.B=0
                        ORDER BY cc.Id LIMIT 1
                        ");
                    $Control_Empresa=count($datos_extra_empresa);
                    if(empty($Control_Empresa))
                        {
                            //BUSCO LA EMPRESA QUE FACTURA CAMPAANA
                            $datos_vinculos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT fac.Id_Empresa, emp.Empresa, emp.Pto_Vta
                                FROM campanas_alcance ca
                                INNER JOIN facturacion fac ON ca.Id_Campana=fac.Id_Campana
                                INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                WHERE ca.Id_Nivel={$ID_Nivel_A} and ca.B=0
                                ");
                            $ID_Empresa=$datos_vinculos[0]->Id_Empresa;
                            $Empresa=$datos_vinculos[0]->Empresa;
                            $Pto_Vta=$datos_vinculos[0]->Pto_Vta;



                        }
                    else
                        {
                            $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                            $Empresa=$datos_extra_empresa[0]->Empresa;
                            $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                            $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                            $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                            //$ID_Periodo=$datos_extra_empresa[0]->ID_Periodo;
                        }

                    if($ID_Alumno_Default>=1)
                        {
                            $ID_Estudiante=$ID_Alumno_Default;
                        }
                    else
                        {
                            $ID_Estudiante=0;
                        }



                    $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Restante;
                    $resultado[0]['conceptos'][$contador_conceptos] = array(
                         'id_empresa'=> $ID_Empresa,
                         'empresa'=> trim(utf8_decode($Empresa)),
                         'id_pto_vta'=> $Pto_Vta,
                         'id_responsable'=> $ID_Responsable,
                         'id_estudiante'=> $ID_Estudiante,
                         'id_nivel' => $ID_Nivel_A,
                         'descripcion'=> $Detalle,
                         'importe'=> $Importe_Restante,
                         'observaciones' => $Observaciones,
                         'id_periodo' => $ID_Periodo,
                         'id' => $ID_Movimiento_Caja,
                         'tipo' => 0
                   );

                }
            


            
          return $resultado;
        }

        public function ver_modelo_facturas_diarias($id, $fecha)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;
          $fecha=$fecha.'  00:00:00';

          $datos_movimientos_diarios = $this->dataBaseService->selectConexion($id_institucion)->select("
          SELECT mc.ID
          FROM movimientos_caja mc
          WHERE mc.Fecha='{$fecha}' and mc.B=0 and mc.Facturado=1
          ORDER BY mc.ID
          ");

          $ctrl_movimientos_diarios=count($datos_movimientos_diarios);

          if(empty($ctrl_movimientos_diarios))
            {

            }
        else
            {
                $contador_gral=0;
                for ($pj=0; $pj < count($datos_movimientos_diarios); $pj++)
                    {
                        $ID_Movimiento_Caja=$datos_movimientos_diarios[$pj]->ID;
                        $array_facturacion=array();
                        $Importe_Restante=0;

                        //CONSULTA DETOS DEL MOVIMIENTO DE CAJA
                        $datos_movimiento = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT mc.Fecha,mc.ID_Tipo_Movimiento,mc.ID_Responsable,mc.Importe,mc.ID_Medio_Pago,mc.Detalle,mc.Facturado, re.Nombre, re.Apellido
                        FROM movimientos_caja mc
                        INNER JOIN responsabes_economicos re ON mc.ID_Responsable=re.Id
                        WHERE mc.ID=$ID_Movimiento_Caja
                        ");
                        $Sumatoria_Conceptos=0;
                        $Fecha_Operacion=$datos_movimiento[0]->Fecha;
                        $ID_Tipo_Movimiento=$datos_movimiento[0]->ID_Tipo_Movimiento;
                        $Apellido_R=trim(utf8_decode($datos_movimiento[0]->Apellido));
                        $Nombre_R=trim(utf8_decode($datos_movimiento[0]->Nombre));
                        $Responsable=$Apellido_R.', '.$Nombre_R;
                        $ID_Responsable=$datos_movimiento[0]->ID_Responsable;
                        $Importe_Cobrado=$datos_movimiento[0]->Importe;
                        $ID_Medio_Pago=$datos_movimiento[0]->ID_Medio_Pago;
                        $Detalle=trim(utf8_decode($datos_movimiento[0]->Detalle));
                        $Facturado=$datos_movimiento[0]->Facturado;

                        $datos_tipo_movimiento = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ctm.Nombre
                        FROM caja_tipo_movimiento ctm
                        WHERE ctm.Id=$ID_Tipo_Movimiento
                        ");
                        $Tipo_Movimiento=$datos_tipo_movimiento[0]->Nombre;

                        $datos_medio_pago = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT mp.Nombre
                        FROM medios_pago mp
                        WHERE mp.Id=$ID_Medio_Pago
                        ");
                        $Medio_Pago=$datos_medio_pago[0]->Nombre;

                        $resultado[$contador_gral] = array(
                                    'id_operacion' => $ID_Movimiento_Caja,
                                    'fecha' => $Fecha_Operacion,
                                    'responsable'=> $Responsable,
                                    'importe_cobrado'=> $Importe_Cobrado,
                                    'observaciones'=> $Detalle,
                                    'facturado'=> $Facturado,
                                    'medio_pago'=> $Medio_Pago
                            );

                        $array_estudiantes=array();
                        
                        $datos_alumnos_vinculados = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT av.Id_Alumno, av.Id
                        FROM alumnos_vinculados av
                        WHERE av.ID_Responsable=$ID_Responsable and av.B=0
                        ");
                        $Cantidad_Alumnos_Vinculados=count($datos_alumnos_vinculados);
                        

                        for ($j=0; $j < count($datos_alumnos_vinculados); $j++)
                            {
                                $ID_Alumno_Vinculado=$datos_alumnos_vinculados[$j]->Id_Alumno;
                                if($Cantidad_Alumnos_Vinculados==1)
                                    {
                                        $ID_Alumno_Default=$ID_Alumno_Vinculado;
                                    }
                                else
                                    {
                                        $ID_Alumno_Default=$datos_alumnos_vinculados[0]->Id_Alumno;
                                    }
                                //CONSULTO DATOS DE ESTUDIANTE
                                $headers = [
                                    'Content-Type: application/json',
                                ];
                                $curl = curl_init();
                                $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno_Vinculado;
                                curl_setopt($curl, CURLOPT_URL,  $ruta_api);
                                //curl_setopt($curl, CURLOPT_URL,  'http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/3?id=1');
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); /**Permitimos recibir respuesta*/
                                curl_setopt($curl, CURLOPT_HTTPGET,true);
                                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($curl, CURLOPT_POST, false);
                                //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); /** Archivo donde guardamos datos de sesion */
                                $data = curl_exec($curl); /** Ejecutamos petición*/
                                curl_close($curl);

                                //$data=dd($data);
                                $data = json_decode($data, true);
                                $datos_alumno = $data['data'];

                                foreach($datos_alumno as $estudiante) {

                                    $Nombre_A=$estudiante["nombre"];
                                    $Apellido_A=$estudiante["apellido"];
                                    $Curso_A=$estudiante["curso"];
                                    $ID_Curso_A=$estudiante["id_curso"];
                                    $ID_Nivel_A=$estudiante["id_nivel"];

                                }

                                $resultado[$contador_gral]['estudiantes'][$j] = array(
                                                                'id'=> $datos_alumnos_vinculados[$j]->Id,
                                                                    'id_alumno'=> $ID_Alumno_Vinculado,
                                                                    'apellido'=> $Apellido_A,
                                                                    'nombre'=> $Nombre_A,
                                                                    'id_curso' => $ID_Curso_A,
                                                                    'curso' => $Curso_A,
                                                                    'id_nivel' => $ID_Nivel_A
                                                            );
                            }

                            //DETALLES DE LA FACTURA
                            //CONSULTA IMPUTACIONES
                            $datos_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ci.ID_Comprobante, ci.ID_Cta_Cte, ci.Importe, ci.Cancela, ci.ID
                                FROM comprobantes_imputaciones ci
                                WHERE ci.ID_Movimiento=$ID_Movimiento_Caja and ci.B=0 and ci.Facturado=0
                                ");
                            $datos_imputaciones_resueltas = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ci.Importe
                                FROM comprobantes_imputaciones ci
                                WHERE ci.ID_Movimiento=$ID_Movimiento_Caja and ci.B=0 and ci.Facturado>=1
                                ");
                            $Cantidad_Imputaciones_Totales=count(($datos_imputaciones));
                            $Cantidad_Imputaciones_Resueltas=count($datos_imputaciones_resueltas);
                            if($Cantidad_Imputaciones_Resueltas>=1)
                                {
                                    $Importe_ya_cobrado=0;
                                    for ($ji=0; $ji < count($datos_imputaciones_resueltas); $ji++)
                                        {
                                            //$ID_Comprobante=$datos_imputaciones[$ji]->ID_Comprobante;
                                            //$ID_Cta_Cte=$datos_imputaciones[$ji]->ID_Cta_Cte;
                                            $Importe_Imputado=$datos_imputaciones_resueltas[$ji]->Importe;
                                            $Importe_ya_cobrado=$Importe_ya_cobrado+$Importe_Imputado;
                                            //$Importe_Cobrado=$Importe_Cobrado-$Importe_Imputado;
                                        }
                                    $Importe_Restante=$Importe_Cobrado-$Importe_ya_cobrado;
                                }
                            
                            $Cantidad_Imputaciones=count($datos_imputaciones);
                            if($Cantidad_Imputaciones==0)
                                {
                                    $Observaciones='';
                                    //CONSULTO OTRO MOVIMIENTO PARA DEFINIR EMPRESA QUE FACTURA
                                    $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote
                                        FROM cuenta_corriente cc
                                        INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                                        INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                                        INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                        WHERE cc.ID_Responsable=$ID_Responsable and cc.Id_Tipo_Comprobante=2 and cc.B=0
                                        ORDER BY cc.Id LIMIT 1
                                        ");
                                    if($ID_Alumno_Default>=1)
                                        {
                                            $ID_Estudiante=$ID_Alumno_Default;
                                        }
                                    else
                                        {
                                            $ID_Estudiante=0;
                                        }
                                    $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                                    $Empresa=trim(utf8_decode($datos_extra_empresa[0]->Empresa));
                                    $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                                    $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                                    $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                                    $resultado[$contador_gral]['conceptos'][0] = array(
                                        'id_empresa'=> $ID_Empresa,
                                        'empresa'=> $Empresa,
                                        'id_pto_vta'=> $Pto_Vta,
                                        'id_responsable'=> $ID_Responsable,
                                        'id_estudiante'=> $ID_Estudiante,
                                        'id_nivel' => $ID_Nivel_A,
                                        'descripcion'=> $Detalle,
                                        'importe'=> $Importe_Cobrado,
                                        'observaciones' => $Observaciones,
                                        'id_periodo' => 3,
                                        'id' => $ID_Movimiento_Caja,
                                        'tipo' => 0,
                                        'alarma'=> 0,
                                        'descripcion_alarma'=> ''

                                );
                                    
                                }
                            else
                                {
                                    if($Cantidad_Imputaciones_Resueltas>=1)
                                        {

                                        }
                                    else
                                        {
                                            $Importe_Restante=$Importe_Cobrado;
                                        }
                                    
                                    $Observaciones='';
                                    $Sumatoria_Conceptos=0;
                                    $contador_conceptos=0;
                                    $Cant_Cuotas_Detalle=0;

                                    $datos_alumnos_vinculados = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT av.Id_Alumno, av.Id
                                    FROM alumnos_vinculados av
                                    WHERE av.ID_Responsable=$ID_Responsable and av.B=0
                                    ");
                                    $Cantidad_Alumnos_Vinculados=count($datos_alumnos_vinculados);
                                    

                                    for ($p=0; $p < count($datos_alumnos_vinculados); $p++)
                                        {
                                            $ID_Alumno_Vinculado=$datos_alumnos_vinculados[$p]->Id_Alumno;
                                            $Total_Comprobantes_por_alumno=0;
                                            for ($j=0; $j < count($datos_imputaciones); $j++)
                                                {
                                                    $ID_Tip_Com=$datos_imputaciones[$j]->ID_Cta_Cte;
                                                    $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                        SELECT cc.Id_Tipo_Comprobante
                                                        FROM cuenta_corriente cc
                                                        WHERE cc.Id=$ID_Tip_Com and cc.B=0 and cc.Facturado=0 and Id_Tipo_Comprobante=2 and ID_Alumno={$ID_Alumno_Vinculado}
                                                        ");
                                                    $Ctrl_Cuta=count($datos_cuenta_corriente);
                                                    if($Ctrl_Cuta>=1)
                                                        {
                                                            $Total_Comprobantes_por_alumno++;
                                                        }

                                                }
                                            
                                            $alumnosArray[] = array(
                                                'id_alumno' => $ID_Alumno_Vinculado,
                                                'cant_comprobante' => $Total_Comprobantes_por_alumno
                                            );

                                        }


                                    for ($j=0; $j < count($datos_imputaciones); $j++)
                                        {
                                            $ID_Tip_Com=$datos_imputaciones[$j]->ID_Cta_Cte;
                                            $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT cc.Id_Tipo_Comprobante
                                                FROM cuenta_corriente cc
                                                WHERE cc.Id=$ID_Tip_Com and cc.B=0 and cc.Facturado=0 and Id_Tipo_Comprobante=2
                                                ");
                                            $Ctrl_Cuta=count($datos_cuenta_corriente);
                                            if($Ctrl_Cuta>=1)
                                                {
                                                    $Cant_Cuotas_Detalle++;
                                                }

                                        }



                                    for ($j=0; $j < count($datos_imputaciones); $j++)
                                        {
                                            $ID_Comprobante=$datos_imputaciones[$j]->ID_Comprobante;
                                            $ID_Cta_Cte=$datos_imputaciones[$j]->ID_Cta_Cte;
                                            $Importe_Imputado=$datos_imputaciones[$j]->Importe;
                                            //$sumatoria_conceptos=$sumatoria_conceptos+$Importe_Imputado;
                                            $Estado_Cancelacion=$datos_imputaciones[$j]->Cancela;
                                            $ID_Imputacion=$datos_imputaciones[$j]->ID;
                                            $Importe_Restante=$Importe_Restante-$Importe_Imputado;
                                            /*
                                            $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT cc.ID_Alumno, cc.Fecha, cc.Id_Tipo_Comprobante, cc.Descripcion, cc.Id_Comprobante, cc.Importe, cc.Cancelado, cc.ID_Periodo, cc.ID_Empresa, cc.Facturado
                                                FROM cuenta_corriente cc
                                                WHERE cc.Id=$ID_Cta_Cte and cc.B=0 and cc.Facturado=0
                                                ");
                                            */
                                            $datos_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT cc.ID_Alumno, cc.Fecha, cc.Id_Tipo_Comprobante, cc.Descripcion, cc.Id_Comprobante, cc.Importe, cc.Cancelado, cc.ID_Periodo, cc.ID_Empresa, cc.Facturado
                                                FROM cuenta_corriente cc
                                                WHERE cc.Id=$ID_Cta_Cte and cc.B=0
                                                ");
                                            $Facturado_State=$datos_cuenta_corriente[0]->Facturado;
                                            if($Facturado_State==1)
                                                {

                                                }
                                            else
                                                {
                                                    $ID_Estudiante=$datos_cuenta_corriente[0]->ID_Alumno;
                                                    $Fecha_Cta=$datos_cuenta_corriente[0]->Fecha;
                                                    $ID_Tipo_Comprobante=$datos_cuenta_corriente[0]->Id_Tipo_Comprobante;
                                                    $Descripcion_Movimiento_Cta=trim(utf8_decode($datos_cuenta_corriente[0]->Descripcion));
                                                    $ID_Comprobante_Cta=$datos_cuenta_corriente[0]->Id_Comprobante;
                                                    $Importe_Movimiento_Cta=$datos_cuenta_corriente[0]->Importe;
                                                    //$Estado_Cancelacion=$datos_cuenta_corriente[0]->Cancelado;
                                                    $ID_Periodo=$datos_cuenta_corriente[0]->ID_Periodo;
                                                    if($ID_Periodo==0)
                                                        {
                                                            $list_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                    SELECT Id
                                                                    FROM periodos_detalle
                                                                    WHERE '{$Fecha_Cta}' BETWEEN Inicio AND Fin
                                                                ");

                                                            $ID_Periodo=$list_periodo[0]->Id;
                                                            if($ID_Periodo>=1)
                                                                {
                                                                    //ACTUALIZO PERIODO
                                                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                                                        UPDATE cuenta_corriente
                                                                        SET ID_Periodo={$ID_Periodo}
                                                                        WHERE ID={$ID_Cta_Cte}
                                                                    ");

                                                                }
                                                        }
                                                    $ID_Empresa=$datos_cuenta_corriente[0]->ID_Empresa;
                                                    if(empty($ID_Estudiante))
                                                        {
                                                            //BUSCO EN OTRO MOVIMIENTO LA EMPRESA
                                                            if($ID_Alumno_Default>=1)
                                                                {
                                                                    $ID_Estudiante=$ID_Alumno_Default;
                                                                }
                                                            else
                                                                {
                                                                    $ID_Estudiante=0;
                                                                }
                                                            if(empty($ID_Empresa))
                                                                {
                                                                    $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                        SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote
                                                                        FROM cuenta_corriente cc
                                                                        INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                                                                        INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                                                                        INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                                        WHERE cc.ID_Responsable=$ID_Responsable and cc.Id_Tipo_Comprobante=2 and cc.B=0
                                                                        ORDER BY cc.Id LIMIT 1
                                                                        ");
                                                                    $Control_Empresa=count($datos_extra_empresa);
                                                                    if(empty($Control_Empresa))
                                                                        {
                                                                            //BUSCO LA EMPRESA QUE FACTURA CAMPAANA
                                                                            $datos_vinculos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                SELECT fac.Id_Empresa, emp.Empresa, emp.Pto_Vta
                                                                                FROM campanas_alcance ca
                                                                                INNER JOIN facturacion fac ON ca.Id_Campana=fac.Id_Campana
                                                                                INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                                                WHERE ca.Id_Nivel={$ID_Nivel_A} and ca.B=0
                                                                                ");
                                                                            $ID_Empresa=$datos_vinculos[0]->Id_Empresa;
                                                                            $Empresa=$datos_vinculos[0]->Empresa;
                                                                            $Pto_Vta=$datos_vinculos[0]->Pto_Vta;
                        
                        
                        
                                                                        }
                                                                    else
                                                                        {
                                                                            $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                                                                            $Empresa=$datos_extra_empresa[0]->Empresa;
                                                                            $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                                                                            $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                                                                            $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                                                                            //$ID_Periodo=$datos_extra_empresa[0]->ID_Periodo;
                                                                        }
                                                                }
                                                            else
                                                                {
                                                                    $datos_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                SELECT emp.Empresa, emp.Pto_Vta
                                                                                FROM empresas emp
                                                                                WHERE emp.ID={$ID_Empresa}
                                                                                ");
                                                                            
                                                                            $Empresa=$datos_empresa[0]->Empresa;
                                                                            $Pto_Vta=$datos_empresa[0]->Pto_Vta;
                                                                }
                                                            
                                                            
                                                        }
                                                    else
                                                        {
                                                            $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote, cc.ID_Periodo
                                                                FROM cuenta_corriente cc
                                                                INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                                                                INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                                                                INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                                WHERE cc.ID_Responsable=$ID_Responsable and cc.ID_Alumno=$ID_Estudiante and cc.Id_Tipo_Comprobante=2 and cc.B=0
                                                                ORDER BY cc.Id LIMIT 1
                                                                ");
                                                            if(empty($ID_Empresa))
                                                                {
                                                                    $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                                                                    $Empresa=$datos_extra_empresa[0]->Empresa;
                                                                    $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                                                                    $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                                                                    $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                                                                    //$ID_Periodo=$datos_extra_empresa[0]->ID_Periodo;
                                                                }
                                                            else
                                                                {
                                                                    $datos_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                SELECT emp.Empresa, emp.Pto_Vta
                                                                                FROM empresas emp
                                                                                WHERE emp.ID={$ID_Empresa}
                                                                                ");
                                                                            
                                                                            $Empresa=$datos_empresa[0]->Empresa;
                                                                            $Pto_Vta=$datos_empresa[0]->Pto_Vta;
                                                                }
                                                            
                                                            
                                                        }

                                                        if($ID_Tipo_Comprobante==2)
                                                        {
                                                            //BUSCO EL IMPORTE TOTAL DE COMPROBANTE
                                                            
                                                            $datos_comprobante= $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT Importe
                                                            FROM comprobantes
                                                            WHERE Id={$ID_Comprobante} and Id_Tipo=2 and B=0
                                                            
                                                            ");
                                                            $Importe_Original=$datos_comprobante[0]->Importe;
                                                            if($Importe_Original==$Importe_Imputado)
                                                                {
                                                                    $Estado_Cancelacion=2;
                                                                }
                                                            else
                                                                {
                                                                    $Estado_Cancelacion=1;
                                                                }
                        
                                                        }
                        
                                                    if($Estado_Cancelacion==1)
                                                        {
                                                            
                                                            
                                                            $Detalle='A CUENTA DE ';
                                                            $Detalle=trim(utf8_decode($Detalle));
                                                            $Detalle=$Detalle.' - '.$Descripcion_Movimiento_Cta;
                                                            $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                            $resultado[$contador_gral]['conceptos'][$contador_conceptos] = array(
                                                                'id_empresa'=> $ID_Empresa,
                                                                'empresa'=> trim(utf8_decode($Empresa)),
                                                                'id_pto_vta'=> $Pto_Vta,
                                                                'id_responsable'=> $ID_Responsable,
                                                                'id_estudiante'=> $ID_Estudiante,
                                                                'id_nivel' => $ID_Nivel_A,
                                                                'descripcion'=> $Detalle,
                                                                'importe'=> $Importe_Imputado,
                                                                'observaciones' => $Observaciones,
                                                                'id_periodo' => $ID_Periodo,
                                                                'id' => $ID_Imputacion,
                                                                'tipo' => 1,
                                                                'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle
                                                                
                                                            );
                                                            $contador_conceptos++;
                                                        }
                                                    if($Estado_Cancelacion==2)
                                                        {
                                                            if($ID_Tipo_Comprobante==2)
                                                                {
                                                                    foreach ($alumnosArray as $alumno) {
                                                                        $id_alumno_en_array = $alumno['id_alumno'];
                                                                        $cant_comprobantes_en_array = $alumno['cant_comprobante'];
                                                                    
                                                                        // Aquí puedes realizar la verificación que necesitas para cant_comprobante
                                                                        if (($cant_comprobantes_en_array >= 2) and ($id_alumno_en_array ==$ID_Estudiante)) {
                                                                            $Cant_Cuotas_Detalle=2;
                                                                        } else {
                                                                            $Cant_Cuotas_Detalle=1;
                                                                        }
                                                                    }
                                                                    
                                                                    if($Cant_Cuotas_Detalle>=2)
                                                                        {
                                                                            $Detalle=$Descripcion_Movimiento_Cta;
                                                                            $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                                            $resultado[$contador_gral]['conceptos'][$contador_conceptos] = array(
                                                                                'id_empresa'=> $ID_Empresa,
                                                                                'empresa'=> trim(utf8_decode($Empresa)),
                                                                                'id_pto_vta'=> $Pto_Vta,
                                                                                'id_responsable'=> $ID_Responsable,
                                                                                'id_estudiante'=> $ID_Estudiante,
                                                                                'id_nivel' => $ID_Nivel_A,
                                                                                'descripcion'=> $Detalle,
                                                                                'importe'=> $Importe_Imputado,
                                                                                'observaciones' => $Observaciones,
                                                                                'id_periodo' => $ID_Periodo,
                                                                                'id' => $ID_Cta_Cte,
                                                                                'tipo' => 3,
                                                                                'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle
                                                                            );
                                                                            $contador_conceptos++;
                                                                        }
                                                                    else
                                                                        {
                                                                            
                                                                            $detalle_comprobante = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                            SELECT cd.Descripcion, cd.Importe, cd.ID_Tipo_Concepto, cd.Id
                                                                            FROM comprobantes_detalles cd
                                                                            WHERE cd.ID_Comprobante=$ID_Comprobante and cd.B=0 and cd.Facturado=0
                                                                            ");
                                                                            for ($p=0; $p < count($detalle_comprobante); $p++)
                                                                                {
                                                                                    $Descripcion_Concepto=trim(utf8_decode($detalle_comprobante[$p]->Descripcion));
                                                                                    $Importe_Concepto=$detalle_comprobante[$p]->Importe;
                                                                                    $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Concepto;
                                                                                    $ID_Tipo_Concepto=$detalle_comprobante[$p]->ID_Tipo_Concepto;
                                                                                    $ID_Item_Concepto=$detalle_comprobante[$p]->Id;
                                                                                    if($ID_Tipo_Concepto==2)
                                                                                        {
                                                                                            $Importe_Concepto=0-$Importe_Concepto;
                                                                                        }
                                                                                    $resultado[$contador_gral]['conceptos'][$contador_conceptos] = array(
                                                                                            'id_empresa'=> $ID_Empresa,
                                                                                            'empresa'=> trim(utf8_decode($Empresa)),
                                                                                            'id_pto_vta'=> $Pto_Vta,
                                                                                            'id_responsable'=> $ID_Responsable,
                                                                                            'id_estudiante'=> $ID_Estudiante,
                                                                                            'id_nivel' => $ID_Nivel_A,
                                                                                            'descripcion'=> $Descripcion_Concepto,
                                                                                            'importe'=> $Importe_Concepto,
                                                                                            'observaciones' => $Observaciones,
                                                                                            'id_periodo' => $ID_Periodo,
                                                                                            'id' => $ID_Item_Concepto,
                                                                                            'tipo' => 2,
                                                                                            'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle
                                                                                        );
                                                                                    $contador_conceptos++;
                                                                                }
                                                                        /*
                                                                        $Detalle=$Descripcion_Movimiento_Cta;
                                                                            $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                                            $resultado[$contador_gral]['conceptos'][$contador_conceptos] = array(
                                                                                'id_empresa'=> $ID_Empresa,
                                                                                'empresa'=> trim(utf8_decode($Empresa)),
                                                                                'id_pto_vta'=> $Pto_Vta,
                                                                                'id_responsable'=> $ID_Responsable,
                                                                                'id_estudiante'=> $ID_Estudiante,
                                                                                'id_nivel' => $ID_Nivel_A,
                                                                                'descripcion'=> $Detalle,
                                                                                'importe'=> $Importe_Imputado,
                                                                                'observaciones' => $Observaciones,
                                                                                'id_periodo' => $ID_Periodo,
                                                                                'id' => $ID_Cta_Cte,
                                                                                'tipo' => 3
                                                                            );
                                                                            $contador_conceptos++;
                                                                            */
                                                                        }

                                                                    
                        
                                                                }
                                                            else
                                                                {
                                                                    
                                                                    $Detalle='CANCELACION '.$Descripcion_Movimiento_Cta;
                                                                    $Detalle=utf8_decode($Detalle);
                                                                    $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Imputado;
                                                                    $resultado[$contador_gral]['conceptos'][$contador_conceptos] = array(
                                                                        'id_empresa'=> $ID_Empresa,
                                                                        'empresa'=> trim(utf8_decode($Empresa)),
                                                                        'id_pto_vta'=> $Pto_Vta,
                                                                        'id_responsable'=> $ID_Responsable,
                                                                        'id_estudiante'=> $ID_Estudiante,
                                                                        'id_nivel' => $ID_Nivel_A,
                                                                        'descripcion'=> $Detalle,
                                                                        'importe'=> $Importe_Imputado,
                                                                        'observaciones' => $Observaciones,
                                                                        'id_periodo' => $ID_Periodo,
                                                                        'id' => $ID_Cta_Cte,
                                                                        'tipo' => 3,
                                                                        'cantidad_cuota_detalle' => $Cant_Cuotas_Detalle
                                                                    );
                                                                    $contador_conceptos++;
                                                                }
                                                        }
                        

                                                }
                                            
                                                                        

                                        }
                                }
                        if($Importe_Restante>=1)
                                {
                                    //QUEDA UN MONTO A CUENTA
                                    $Observaciones='';
                                    $Detalle='A CUENTA DE PROXIMOS PERIODOS ';
                                    $Detalle=trim(utf8_encode($Detalle));
                                    //CONSULTO OTRO MOVIMIENTO PARA DEFINIR EMPRESA QUE FACTURA
                                    $datos_extra_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT fac.Id_Empresa, fac.Id_Campana, emp.Empresa, emp.Pto_Vta, comp.Id_Lote
                                        FROM cuenta_corriente cc
                                        INNER JOIN comprobantes comp ON cc.ID_Comprobante=comp.Id
                                        INNER JOIN facturacion fac ON comp.Id_Lote=fac.Id
                                        INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                        WHERE cc.ID_Responsable=$ID_Responsable and cc.Id_Tipo_Comprobante=2 and cc.B=0
                                        ORDER BY cc.Id LIMIT 1
                                        ");
                                    $Control_Empresa=count($datos_extra_empresa);
                                    if(empty($Control_Empresa))
                                        {
                                            //BUSCO LA EMPRESA QUE FACTURA CAMPAANA
                                            $datos_vinculos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT fac.Id_Empresa, emp.Empresa, emp.Pto_Vta
                                                FROM campanas_alcance ca
                                                INNER JOIN facturacion fac ON ca.Id_Campana=fac.Id_Campana
                                                INNER JOIN empresas emp ON fac.Id_Empresa=emp.Id
                                                WHERE ca.Id_Nivel={$ID_Nivel_A} and ca.B=0
                                                ");
                                            $ID_Empresa=$datos_vinculos[0]->Id_Empresa;
                                            $Empresa=$datos_vinculos[0]->Empresa;
                                            $Pto_Vta=$datos_vinculos[0]->Pto_Vta;



                                        }
                                    else
                                        {
                                            $ID_Empresa=$datos_extra_empresa[0]->Id_Empresa;
                                            $Empresa=$datos_extra_empresa[0]->Empresa;
                                            $ID_Campana=$datos_extra_empresa[0]->Id_Campana;
                                            $Pto_Vta=$datos_extra_empresa[0]->Pto_Vta;
                                            $ID_Lote=$datos_extra_empresa[0]->Id_Lote;
                                            //$ID_Periodo=$datos_extra_empresa[0]->ID_Periodo;
                                        }

                                    if($ID_Alumno_Default>=1)
                                        {
                                            $ID_Estudiante=$ID_Alumno_Default;
                                        }
                                    else
                                        {
                                            $ID_Estudiante=0;
                                        }



                                    $Sumatoria_Conceptos=$Sumatoria_Conceptos+$Importe_Restante;
                                    $resultado[$contador_gral]['conceptos'][$contador_conceptos] = array(
                                        'id_empresa'=> $ID_Empresa,
                                        'empresa'=> trim(utf8_decode($Empresa)),
                                        'id_pto_vta'=> $Pto_Vta,
                                        'id_responsable'=> $ID_Responsable,
                                        'id_estudiante'=> $ID_Estudiante,
                                        'id_nivel' => $ID_Nivel_A,
                                        'descripcion'=> $Detalle,
                                        'importe'=> $Importe_Restante,
                                        'observaciones' => $Observaciones,
                                        'id_periodo' => $ID_Periodo,
                                        'id' => $ID_Movimiento_Caja,
                                        'tipo' => 0
                                );

                                }
                        $contador_gral++;        

                    }
                
            }

          
          

            
          return $resultado;
        }

        function guardar_factura($numero,$tipo_de_factura,$fecha,$id_periodo,$Importe,$CAE,$Vto,$Observaciones,$ID_Empresa,$Pto_Vta,$ID_Responsable,$Responsable,$Domicilio,$Documento,$ID_Movimiento_Caja,$id_usuario,$id_institucion)
            {
            
            $FechaHoy=date("Y-m-d");

            $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO facturas_emitidas
                            (Numero,Tipo_Factura,Fecha,ID_Periodo,Importe,CAE,Vto,Observaciones,ID_Empresa,Pto_Vta,ID_Responsable,Responsable,Documento,Domicilio,ID_Operacion,ID_Usuario)
                            VALUES ({$numero},{$tipo_de_factura},'{$fecha}',{$id_periodo},'{$Importe}','{$CAE}','{$Vto}','{$Observaciones}',{$ID_Empresa},{$Pto_Vta},{$ID_Responsable},'{$Responsable}','{$Domicilio}','{$Documento}',{$ID_Movimiento_Caja},{$id_usuario})
                        ");
            //CONSULTO ID NUEVO
            $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fe.Id
                            FROM facturas_emitidas fe
                            WHERE fe.B=0 and fe.Tipo_Factura={$tipo_de_factura} and fe.Numero={$numero} and fe.CAE='{$CAE}'
                            ");
            $ID_Factura=$check_insercion[0]->Id;

            
            return $ID_Factura;
            }

            function enviar_factura($id_factura,$tipo_factura,$mail,$destinatario,$id_institucion)
                {
                
                    $FechaHoy=date("Y-m-d");
                    $Horario=date("H:i:s");
                    $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //posibles caracteres a usar
                    $numerodeletras=25; //numero de letras para generar el texto
                    $Cadena_Aleatoria = ""; //variable para almacenar la cadena generada
                    for($i=0;$i<$numerodeletras;$i++)
                        {
                        $Cadena_Aleatoria .= substr($caracteres,rand(0,strlen($caracteres)),1);
                        }
                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                        INSERT INTO envio_comprobantes
                        (ID_Comprobante,Tipo_Comprobante,Fecha,Hora,MailD,Destinatario,Aleatorio)
                        VALUES ({$id_factura},{$tipo_factura},'{$FechaHoy}','{$Horario}','{$mail}','{$destinatario}','{$Cadena_Aleatoria}')
                    ");
                    //CONSULTO ID NUEVO
                    $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ec.ID
                                    FROM envio_comprobantes ec
                                    WHERE ec.B=0 and ec.ID_Comprobante={$id_factura} and ec.Tipo_Comprobante={$tipo_factura}
                                    ");
                    $ID_Envio=$check_insercion[0]->ID;

                    return $ID_Envio;
                }

        
        public function generar_factura($id, $id_responsable, $id_empresa, $id_pto_vta, $id_periodo, $id_operacion, $importe, $conceptos, $id_usuario, $id_alumno)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;

           //CONSULTO DATOS DE ESTUDIANTE
           $headers = [
            'Content-Type: application/json',
            ];
            $curl = curl_init();
            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$id_alumno;
            curl_setopt($curl, CURLOPT_URL,  $ruta_api);
            //curl_setopt($curl, CURLOPT_URL,  'http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/3?id=1');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); /**Permitimos recibir respuesta*/
            curl_setopt($curl, CURLOPT_HTTPGET,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, false);
            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); /** Archivo donde guardamos datos de sesion */
            $data = curl_exec($curl); /** Ejecutamos petición*/
            curl_close($curl);

            //$data=dd($data);
            $data = json_decode($data, true);
            $datos_alumno = $data['data'];

            foreach($datos_alumno as $estudiante) {

                $Nombre_A=$estudiante["nombre"];
                $Apellido_A=$estudiante["apellido"];
                $Curso_A=$estudiante["curso"];
                //$ID_Curso_A=$estudiante["id_curso"];
                //$ID_Nivel_A=$estudiante["id_nivel"];
                $Estudiante=$Apellido_A.', '.$Nombre_A.' - '.$Curso_A;

            }


          
          $empresas = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT emp.CUIT, emp.Afip_key, emp.Afip_scr, emp.Pto_Vta, emp.Produccion
                    FROM empresas emp
                    WHERE emp.B=0 and emp.ID={$id_empresa}
                    ");
           $cuit=$empresas[0]->CUIT;
           $key=$empresas[0]->Afip_key;
           $cert=$empresas[0]->Afip_scr;
           $produccion=$empresas[0]->Produccion;
           $Pto_Vta=$empresas[0]->Pto_Vta;

           $periodos = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT pd.Id_Mes, pd.Inicio, pd.Fin, pd.Vencimiento
                    FROM periodos_detalle pd
                    WHERE pd.id={$id_periodo}
                    ");
           $F_Inicio=$periodos[0]->Inicio;
           $F_Fin=$periodos[0]->Fin;
           $F_Vencimiento=$periodos[0]->Vencimiento;

           $operaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Detalle
                    FROM movimientos_caja mc
                    WHERE mc.ID={$id_operacion}
                    ");
           $Observaciones=$operaciones[0]->Detalle;
           $Observaciones=$Observaciones.' - Ref: '.$Estudiante;

           $responsables = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT re.DNI, re.CUIT, re.Nombre, re.Apellido, re.Domicilio, re.Email, re.Nombre_Fiscal
                    FROM responsabes_economicos re
                    WHERE re.Id={$id_responsable}
                    ");
           $dni_r=$responsables[0]->DNI;
           $cuit_r=$responsables[0]->CUIT;
           $nombre_r=$responsables[0]->Nombre;
           $apellido_r=$responsables[0]->Apellido;
           $domicilio_r=$responsables[0]->Domicilio;
           $email_r=$responsables[0]->Email;
           $nombre_fiscal=$responsables[0]->Nombre_Fiscal;
           $responsable=$apellido_r.', '.$nombre_r;

        $afip = new Afip(array(
            'CUIT' => $cuit,
            //'CUIT' => '23284542819',
            'cert' 		=> $cert,
            'key' 		=> $key
            //'production' => $produccion
            //'access_token' => 'UDbn6i9Yho7YGcG1tuZNSMN4BfQ8dWJrHACbLVZdTJ5uLtKncSbmrgn9RqOIxCFB'
            ));
        
        $punto_de_venta = $Pto_Vta;
        $tipo_de_comprobante = 11; // 11 = Factura C
        $last_voucher = $afip->ElectronicBilling->GetLastVoucher($punto_de_venta, $tipo_de_comprobante);
        $concepto = 1;
        if($cuit_r==0)
                {
                    $tipo_de_documento = 96;
                    $numero_de_documento = $dni_r;
                    $Documento='DNI: '.$numero_de_documento;
                    $Responsable=$responsable;
                }
        else
                {
                    $tipo_de_documento = 80;
                    $numero_de_documento = $cuit_r;
                    $Documento='CUIT: '.$numero_de_documento;
                    $Responsable=$nombre_fiscal;
                }
        $numero_de_factura = $last_voucher+1;
        $fecha = date('Y-m-d');
        //$fecha = '2023-09-30';
        $importe_total = $importe;
        if ($concepto === 2 || $concepto === 3) 
            {
               
                //$fecha_servicio_desde = intval(date('Ymd', strtotime($Fecha_Inicio)));

                $fecha_servicio_desde = intval(date('Ymd', strtotime($F_Inicio)));
  
                $fecha_servicio_hasta = intval(date('Ymd', strtotime($F_Fin)));

                $fecha_vencimiento_pago = intval(date('Ymd', strtotime($F_Vencimiento)));
            }
        else {
                $fecha_servicio_desde = null;
                $fecha_servicio_hasta = null;
                $fecha_vencimiento_pago = null;
            }
      
        $data = array(
                'CantReg' 	=> 1, // Cantidad de facturas a registrar
                'PtoVta' 	=> $punto_de_venta,
                'CbteTipo' 	=> $tipo_de_comprobante, 
                'Concepto' 	=> $concepto,
                'DocTipo' 	=> $tipo_de_documento,
                'DocNro' 	=> $numero_de_documento,
                'CbteDesde' => $numero_de_factura,
                'CbteHasta' => $numero_de_factura,
                'CbteFch' 	=> intval(str_replace('-', '', $fecha)),
                'FchServDesde'  => $fecha_servicio_desde,
                'FchServHasta'  => $fecha_servicio_hasta,
                'FchVtoPago'    => $fecha_vencimiento_pago,
                'ImpTotal' 	=> $importe_total,
                'ImpTotConc'=> 0, // Importe neto no gravado
                'ImpNeto' 	=> $importe_total, // Importe neto
                'ImpOpEx' 	=> 0, // Importe exento al IVA
                'ImpIVA' 	=> 0, // Importe de IVA
                'ImpTrib' 	=> 0, //Importe total de tributos
                'MonId' 	=> 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos) 
                'MonCotiz' 	=> 1, // Cotización de la moneda usada (1 para pesos argentinos)  
            );

        $res = $afip->ElectronicBilling->CreateVoucher($data);
        $res["Numero"] = $numero_de_factura;


        //$responseArray = json_decode($res, true);
        $data = $res;

        $CAE_Obtenido = $data['CAE'];
        $Fecha_Obtenida = $data['CAEFchVto'];
        
        
        //HAY QUE REVISAR ALGORITOMO DE GENERACIOND E FACTURA
        //$ID_Factura=guardar_factura($Numero_G,$Tipo_Comprobante_G,$FechaHoy,$Importe_G,$CAE,$Vencimiento_G,$Observaciones,$ID_Empresa_G,$Pto_Vta_G,$ID_Responsable_G,$Responsable_G,$Domicilio_G,$Documento_G,$ID_Movimiento_Caja);

        if($CAE_Obtenido<>'')
            {
                $ID_Factura = $this->guardar_factura($numero_de_factura,$tipo_de_comprobante,$fecha,$id_periodo,$importe_total,$CAE_Obtenido,$Fecha_Obtenida,$Observaciones,$id_empresa,$Pto_Vta,$id_responsable,$Responsable,$domicilio_r,$Documento,$id_operacion,$id_usuario,$id_institucion);
                $Observaciones='';
                //GUARDO EL DETALLE
                    foreach($conceptos as $Linea)
                        {
                            $Descripcion_C=$Linea['descripcion'];
                            $Importe_C=$Linea['importe'];
                            $codigo = $Linea['Codigo'];
                            $tipo = $Linea['tipo'];
                            $id_item = $Linea['id'];
                            if($codigo==0)
                                {
                                    $codigo='';
                                }

                            
                            if($tipo==0)
                                {
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE movimientos_caja
                                            SET Facturado=2
                                            WHERE ID={$id_item}
                                        ");
                                    
                                }
                            if($tipo==1)
                                {
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes_imputaciones
                                            SET Facturado={$ID_Factura}
                                            WHERE ID={$id_item}
                                        ");
                                }
                            if($tipo==2)
                                {
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes_detalles
                                            SET Facturado={$ID_Factura}
                                            WHERE ID={$id_item}
                                        ");
                                }
                            if($tipo==3)
                                {
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE cuenta_corriente
                                            SET Facturado=1
                                            WHERE ID={$id_item}
                                        ");
                                }
                            
                            //$descripcion=utf8_encode($descripcion);
                            $cantidad = 1;
                            $unidad = 'Unidades';
                            $unitario = $Importe_C;
                            $p_bonif = 0;
                            $i_bonif =0;
                            $subtotal = $Importe_C;

                            $creo_periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO facturas_emitidas_detalle
                                    (ID_Factura,Codigo,Descripcion,UMedida,Unitario,Cantidad,P_Bonificacion,I_Bonificacion,Sub_Total)
                                    VALUES
                                    ({$ID_Factura},'{$codigo}','{$Descripcion_C}','{$unidad}',{$unitario},{$cantidad},{$p_bonif},{$i_bonif},'{$subtotal}')
                                ");

                            
                        }
                $ID_Envio = $this->enviar_factura($ID_Factura,1,$email_r,$responsable,$id_institucion);
        
            }

        //$ID_Factura = $this->guardar_factura($ID_Factura,$tipo_de_comprobante,$FechaActual,$id_periodo,$importe_total,$CAE_Obtenido,$Fecha_Obtenida,$Observaciones,$id_empresa,$Pto_Vta,$id_responsable,$Responsable,$domicilio_r,$Documento,$id_operacion,$id_usuario,$id_institucion);
        
        //
        $Texto='<p>La Factura se ha generado con éxito<p><p>Comprobante: C-'.$Pto_Vta.'-'.$numero_de_factura.'<p>Importe: $'.$importe_total.'<p>CAE: '.$CAE_Obtenido;
        $res= $Texto;
        return $res;
    }

    public function generar_lote_facturas($id, $id_usuario, $lote)
    //public function generar_factura($id, $id_responsable, $id_empresa, $id_pto_vta, $id_periodo, $id_operacion, $importe, $conceptos, $id_usuario, $id_alumno)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;



          foreach($lote as $Linea)
                        {
                            $id_responsable=$Linea['id_responsable'];
                            $id_empresa=$Linea['id_empresa'];
                            $id_pto_vta = $Linea['id_pto_vta'];
                            $id_periodo = $Linea['id_periodo'];
                            $id_operacion = $Linea['id_operacion'];
                            $importe = $Linea['importe'];
                            $conceptos = $Linea['conceptos'];
                            $id_usuario = $Linea['id_usuario'];
                            $id_alumno = $Linea['id_alumno'];

                            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //posibles caracteres a usar
                            $numerodeletras=20;
                            $Cadena_Aleatoria = ""; //variable para almacenar la cadena generada
                            for($i=0;$i<$numerodeletras;$i++)
                            {
                                $Cadena_Aleatoria .= substr($caracteres,rand(0,strlen($caracteres)),1);
                            }

                            $aleatorio=$Cadena_Aleatoria;
                            $creo_encabezados = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO lotes_facturacion
                                    (Fecha,Hora,ID_Usuario,Aleatorio,ID_Responsable,ID_Empresa,ID_Pto_Vta,ID_Periodo,ID_Operacion,Importe,ID_Alumno)
                                    VALUES
                                    ('{$FechaActual}','{$HoraActual}',$id_usuario,'{$aleatorio}',$id_responsable,$id_empresa,$id_pto_vta,$id_periodo,$id_operacion,'{$importe}',$id_alumno)
                                ");
                            $consulta_encabezado = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT lf.ID
                                FROM lotes_facturacion lf
                                WHERE lf.Aleatorio='{$aleatorio}'
                                ");
                            $ID_Lote_Generado=$consulta_encabezado[0]->ID;
                            foreach($conceptos as $Linea2)
                                {
                                    $Descripcion_C=$Linea2['descripcion'];
                                    $Importe_C=$Linea2['importe'];
                                    $codigo = $Linea2['Codigo'];
                                    $tipo = $Linea2['tipo'];
                                    $id_item = $Linea2['id'];
                                    
                                    
                                    //$descripcion=utf8_encode($descripcion);
                                    $cantidad = 1;
                                    $unidad = 'Unidades';
                                    $unitario = $Importe_C;
                                    $p_bonif = 0;
                                    $i_bonif =0;
                                    $subtotal = $Importe_C;

                                    $creo_periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO lotes_facturacion_detalle
                                            (ID_Lote,Descripcion,Importe,Codigo,Tipo,ID_Item)
                                            VALUES
                                            ({$ID_Lote_Generado},'{$Descripcion_C}','{$Importe_C}','{$codigo}',{$tipo},{$id_item})
                                        ");

                                    
                                }


                        }
                        
        $Texto='<p>El Lote de facturación se ha enviado con éxito. Se ha comenzado la generación de comprobantes y será notificado cuando finalice';
        $res= $Texto;
        return $res;
    }

    public function cerrar_factura($id, $id_operacion)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          //ACTUALIZO COMPROBANTE ANULADO
          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                        UPDATE movimientos_caja
                        SET Facturado=2
                        WHERE ID={$id_operacion}
                    ");
                        
          $resultado='La operación de caja ha sido facturada por completo';
                    
          return $resultado;
        }

    public function lotes_intereses($id)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $resultado=array();
            $lista_intereses = $this->dataBaseService->selectConexion($id_institucion)->select("
                                      SELECT ig.ID, ig.ID_Periodo, ig.Orden, ig.Estado, ig.Fecha_Generacion, ig.Hora_Generacion, pd.Id_Mes, pe.Nombre, us.name
                                      FROM intereses_generados ig
                                      INNER JOIN periodos_detalle pd ON ig.ID_Periodo=pd.Id
                                      INNER JOIN periodos pe ON pd.Id_Periodo=pe.Id
                                      INNER JOIN users us ON ig.ID_Generacion=us.id
                                      WHERE ig.B=0
                                      ORDER BY ig.ID
  
                                          ");
  
             $ctrl_movimientos=count($lista_intereses);
             if(empty($ctrl_movimientos))
              {
                  
              }
          else
              {
                  for ($j=0; $j < count($lista_intereses); $j++)
                      {
                          $ID_Lote=$lista_intereses[$j]->ID;
                          $ID_Periodo=$lista_intereses[$j]->ID_Periodo;
                          $Orden=$lista_intereses[$j]->Orden;
                          $ID_Estado = $lista_intereses[$j]->Estado;
                          $Fecha_Generacion = $lista_intereses[$j]->Fecha_Generacion;
                          $Hora_Generacion=$lista_intereses[$j]->Hora_Generacion;
                          $ID_Mes=$lista_intereses[$j]->Id_Mes;
                          $Periodo_General=$lista_intereses[$j]->Nombre;
                          $Usuario=$lista_intereses[$j]->name;
                          if($ID_Mes==1) { $Mes='Enero';}
                          if($ID_Mes==2) { $Mes='Febrero';}
                          if($ID_Mes==3) { $Mes='Marzo';}
                          if($ID_Mes==4) { $Mes='Abril';}
                          if($ID_Mes==5) { $Mes='Mayo';}
                          if($ID_Mes==6) { $Mes='Junio';}
                          if($ID_Mes==7) { $Mes='Julio';}
                          if($ID_Mes==8) { $Mes='Agosto';}
                          if($ID_Mes==9) { $Mes='Septiembre';}
                          if($ID_Mes==10) { $Mes='Octubre';}
                          if($ID_Mes==11) { $Mes='Noviembre';}
                          if($ID_Mes==12) { $Mes='Diciembre';}

                          if($Orden==1) { $Orden_detalle='Primer Vencimiento';}
                          if($Orden==2) { $Orden_detalle='Segundo Vencimiento';}
                          if($ID_Estado==0) { $Estado='Pendiente de Generar';}
                          if($ID_Estado==1) { $Estado='Generado';}

                          if($ID_Estado==0)
                            {
                                $Fecha_Generacion='Pendiente';
                                $Hora_Generacion='Pendiente';
                                $Monto='Pendiente';
                            }
                        else
                            {
                                //MONTO
                                
                                /*
                                
                                $consulta_intereses = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM cuenta_corriente
                                WHERE B=0 AND Id_Tipo_Comprobante=7 and ID_Periodo={$ID_Periodo} and ID_Alumno<>0
                                ");
                                $Monto=$consulta_intereses[0]->Total;

                                */
                                $consulta_intereses = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM intereses_generados_detalles
                                WHERE B=0 AND ID_Lote={$ID_Lote}
                                ");
                                $Monto=$consulta_intereses[0]->Total;


                              
                            }

                          $Periodo=$Mes.' - '.$Periodo_General;
                          


                          $resultado[$j] = array(
                                                                                       
                              'id'=> $ID_Lote,
                              'id_periodo'=> $ID_Periodo,
                              'periodo'=> $Periodo,
                              'orden'=> $Orden,
                              'detalle'=> $Orden_detalle,
                              'estado'=> $Estado,
                              'id_estado'=> $ID_Estado,
                              'importe'=> $Monto,
                              'fecha_generacion'=> $Fecha_Generacion,
                              'hora_generacion'=> $Hora_Generacion,
                              'usuario'=> trim(utf8_decode($Usuario))
  
                        );
  
                      }
                    
              }
            return $resultado;
        }

        public function modelo_lote_intereses($id, $id_periodo, $orden)
        {
            $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $headers = [
            'Content-Type: application/json',
            ];
            $curl = curl_init();
            $ruta_api2='http://apirest.geofacturacion.com.ar/api/responsables/estudiantes_listado/'.$id_institucion;
            curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPGET,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, false);
            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
            //usleep(100000);
            $data = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($data, true);
            $datos_alumnos = $data['data'];

            $consulta_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pd.Interes, pd.Vencimiento
                          FROM periodos_detalle pd
                          WHERE pd.B=0 and pd.Id={$id_periodo}
                          
                      ");

            $Interes_Sugerido = $consulta_periodo[0]->Interes;
            $Vencimiento_Sugerido = $consulta_periodo[0]->Vencimiento;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT rp.Id,rp.Nombre,rp.Apellido
                          FROM responsabes_economicos rp
                          WHERE rp.B=0
                          ORDER BY rp.Apellido,rp.Nombre
                      ");

        
          $numeracion=0;
          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Responsable = $listado[$j]->Id;
                  
                   
                   $vinculos = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT av.Id
                          FROM alumnos_vinculados av
                          WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}

                      ");
                    $Cant_Vinculos = count($vinculos);

                    //CALCULAR
                    $Concepto='Recargo por mora 1er vencimiento Junio 2023';
                    $Importe=1200;

                    if($orden==1)
                        {
                            $Interes_Sugerido=2.955;
                            $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                          SELECT av.Id,av.Id_Alumno
                                          FROM alumnos_vinculados av
                                          WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                          ORDER BY av.Id
                                      ");
                            $cant_vinculos=count($detalle);
                            if($cant_vinculos>=1)
                                {
                                    for ($k=0; $k < count($detalle); $k++) 
                                        {
                                            //$resultado[$j]['detalle_periodo'][$k] = 1;
                                            $ID_Alumno = $detalle[$k]->Id_Alumno;
                                            foreach($datos_alumnos as $estudiante) 
                                                {

                                                    $id_estudiante=$estudiante["id"];
                                                    if($id_estudiante==$ID_Alumno)
                                                        {
                                                            
                                                            //REVISION DE DEUDA
                                                            $busqueda_comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                        SELECT cc.Id, cc.Descripcion, cc.Cancelado, cc.Importe, comp.Id_Lote, fact.Monto
                                                                        FROM cuenta_corriente cc
                                                                        INNER JOIN comprobantes comp ON cc.Id_Comprobante=comp.Id
                                                                        INNER JOIN facturacion fact ON comp.Id_Lote=fact.Id
                                                                        WHERE cc.B=0 and cc.Id_Responsable={$ID_Responsable} and cc.Id_Tipo_Comprobante=2 and cc.ID_Alumno={$ID_Alumno} and cc.Cancelado<=1 and cc.ID_Periodo={$id_periodo} and cc.Interes=1
                                                                    ");
                                                            $ctrl_pago=count($busqueda_comprobantes);
                                                            if($ctrl_pago>=1)
                                                                {
                                                                    for ($f=0; $f < count($busqueda_comprobantes); $f++)
                                                                        {
                                                                            $Cancelado = $busqueda_comprobantes[$f]->Cancelado;
                                                                            $ID_Cta_Cte = $busqueda_comprobantes[$f]->Id;
                                                                            $Descripcion_Cta = $busqueda_comprobantes[$f]->Descripcion;
                                                                            if($Cancelado==1)
                                                                                {
                                                                                    $Importe_Adeudado=$busqueda_comprobantes[$f]->Importe;
                                                                                    $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                            SELECT ci.Importe
                                                                                            FROM comprobantes_imputaciones ci
                                                                                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Cta_Cte}
                                                                                        ");
                                                                                    $Ctrl_Imputaciones=count($busqueda_imputaciones);
                                                                                    if(empty($Ctrl_Imputaciones))
                                                                                        {
                                                                                            
                                                                                        }
                                                                                    else
                                                                                        {
                                                                                            for ($g=0; $g < count($busqueda_imputaciones); $g++)
                                                                                                {
                                                                                                    $Importe_Pagado=$busqueda_imputaciones[$g]->Importe;
                                                                                                    $Importe_Adeudado=$Importe_Adeudado-$Importe_Pagado;
                                                                                                }
                                                                                        }
                                                                                }
                                                                            else
                                                                                {
                                                                                    //BUSCO IMPORTE ORIGINAL
                                                                                    $Importe_Adeudado=$busqueda_comprobantes[$f]->Monto;
                                                                                }
                                                                            if($Importe_Adeudado>=10)
                                                                                {
                                                                                    $Concepto='Recargo por Mora 1er Vencimiento ('.$Descripcion_Cta.')';
                                                                                    $Coeficiente=$Interes_Sugerido/100;
                                                                                    $Interes_Calculado=round($Importe_Adeudado*$Coeficiente);

                                                                                    $Interes_Redondeado = round($Interes_Calculado / 10) * 10;
                                                                                    
                                                                                    
                                                                                    $Nombre_A=$estudiante["nombre"];
                                                                                    $Apellido_A=$estudiante["apellido"];
                                                                                    $Estudiante=$Apellido_A.', '.$Nombre_A;
                                                                                    $resultado[$numeracion] = array(
                                                                                                'id' => $ID_Responsable,
                                                                                                'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                                                                'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                                                                'id_periodo'=> $id_periodo,
                                                                                                'orden'=> $orden,
                                                                                                'interes_sugerido'=> $Interes_Sugerido,
                                                                                                'vencimiento_sugerido'=> $Vencimiento_Sugerido,
                                                                                                'concepto'=> trim(utf8_decode($Concepto)),
                                                                                                'importe_adeudado'=> $Importe_Adeudado,
                                                                                                'importe'=> $Interes_Redondeado,
                                                                                                'id_alumno'=> $ID_Alumno,
                                                                                                'alumno'=> trim(utf8_decode($Estudiante)),
                                                                                                'actualizacion'=> 0,
                        
                                                                                            );
                                                                                    $numeracion++;
                                                                                }
                                                                            

                                                                        }

                                                                }
                                        
                                                        }
                                                }
                                            
                                        }
                                    
                                }
                        }
                    if($orden==2)
                        {
                            $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                          SELECT av.Id,av.Id_Alumno
                                          FROM alumnos_vinculados av
                                          WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                          ORDER BY av.Id
                                      ");
                            $cant_vinculos=count($detalle);
                            if($cant_vinculos>=1)
                                {
                                    for ($k=0; $k < count($detalle); $k++) 
                                        {
                                            //$resultado[$j]['detalle_periodo'][$k] = 1;
                                            $ID_Alumno = $detalle[$k]->Id_Alumno;
                                            foreach($datos_alumnos as $estudiante) 
                                                {

                                                    $id_estudiante=$estudiante["id"];
                                                    if($id_estudiante==$ID_Alumno)
                                                        {
                                                            
                                                            $n_estudiante=$estudiante["nombre"];
                                                            $a_estudiante=$estudiante["apellido"];
                                                            $Nivel=$estudiante["nivel"];
                                                            $Estudiante=$a_estudiante.', '.$n_estudiante;
                                                            //REVISION DE DEUDA
                                                            $busqueda_comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                        SELECT cc.Id, cc.Descripcion, cc.Cancelado, cc.Importe, comp.Id_Lote, fact.Monto
                                                                        FROM cuenta_corriente cc
                                                                        INNER JOIN comprobantes comp ON cc.Id_Comprobante=comp.Id
                                                                        INNER JOIN facturacion fact ON comp.Id_Lote=fact.Id
                                                                        WHERE cc.B=0 and cc.Id_Responsable={$ID_Responsable} and cc.Id_Tipo_Comprobante=2 and cc.ID_Alumno={$ID_Alumno} and cc.Cancelado<=1 and cc.ID_Periodo={$id_periodo} and cc.Interes=1
                                                                    ");
                                                            $ctrl_pago=count($busqueda_comprobantes);
                                                            if($ctrl_pago>=1)
                                                                {
                                                                    for ($f=0; $f < count($busqueda_comprobantes); $f++)
                                                                        {
                                                                            $Cancelado = $busqueda_comprobantes[$f]->Cancelado;
                                                                            $ID_Cta_Cte = $busqueda_comprobantes[$f]->Id;
                                                                            $Descripcion_Cta = $busqueda_comprobantes[$f]->Descripcion;
                                                                            if($Cancelado==1)
                                                                                {
                                                                                    $Importe_Adeudado=$busqueda_comprobantes[$f]->Importe;
                                                                                    $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                            SELECT ci.Importe
                                                                                            FROM comprobantes_imputaciones ci
                                                                                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Cta_Cte}
                                                                                        ");
                                                                                    $Ctrl_Imputaciones=count($busqueda_imputaciones);
                                                                                    if(empty($Ctrl_Imputaciones))
                                                                                        {
                                                                                            
                                                                                        }
                                                                                    else
                                                                                        {
                                                                                            for ($g=0; $g < count($busqueda_imputaciones); $g++)
                                                                                                {
                                                                                                    $Importe_Pagado=$busqueda_imputaciones[$g]->Importe;
                                                                                                    $Importe_Adeudado=$Importe_Adeudado-$Importe_Pagado;
                                                                                                }
                                                                                        }
                                                                                    //AHORA DEBERIA BUSCAR SI TIENE IMPAGO EL INTERES, SINO SE LO SUMO
                                                                                    $busqueda_comprobantes2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                        SELECT cc.Id, cc.Cancelado, cc.Importe
                                                                                        FROM cuenta_corriente cc
                                                                                        WHERE cc.B=0 and cc.Id_Responsable={$ID_Responsable} and cc.Id_Tipo_Comprobante=7 and cc.ID_Alumno={$ID_Alumno} and cc.Cancelado<=1 and cc.ID_Periodo={$id_periodo} and cc.Interes=1
                                                                                    ");
                                                                                    $Ctrl_Existencia=count($busqueda_comprobantes2);
                                                                                    if($Ctrl_Existencia>=1)
                                                                                        {
                                                                                            $Cancelado = $busqueda_comprobantes2[0]->Cancelado;
                                                                                            $Importe_Interes_PA = $busqueda_comprobantes2[0]->Importe;
                                                                                            $ID_Cta = $busqueda_comprobantes2[0]->Id;
                                                                                            if($Cancelado==0)
                                                                                                {
                                                                                                    $Importe_Adeudado=$Importe_Adeudado+$Importe_Interes_PA;
                                                                                                }
                                                                                            else
                                                                                                {
                                                                                                    $busqueda_imputaciones2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                                            SELECT ci.Importe
                                                                                                            FROM comprobantes_imputaciones ci
                                                                                                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Cta}
                                                                                                        ");
                                                                                                    $Ctrl_Imputaciones2=count($busqueda_imputaciones2);
                                                                                                    for ($h=0; $h < count($busqueda_imputaciones2); $h++)
                                                                                                        {
                                                                                                            $Importe_Descontado=$busqueda_imputaciones2[$h]->Importe;
                                                                                                            $Importe_Interes_PA=$Importe_Interes_PA-$Importe_Descontado;
                                                                                                        }
                                                                                                    $Importe_Adeudado=$Importe_Adeudado+$Importe_Interes_PA;

                                                                                                }
                                                                                        }
                                                                                    $Actualizable=0;
                                                                                }
                                                                            else
                                                                                {
                                                                                    //BUSCO IMPORTE ORIGINAL
                                                                                    $Importe_Adeudado=$busqueda_comprobantes[$f]->Monto;
                                                                                    //SE DEBE BORRAR EL INTERES GENERADO DEL PRIMER VENCIMIENTO
                                                                                    $Actualizable=1;
                                                                                }
                                                                            
                                                                            
                                                                            if($Importe_Adeudado>=10)
                                                                                {
                                                                                    $Concepto='Recargo por Mora 2do Vencimiento ('.$Descripcion_Cta.')';
                                                                                    $Coeficiente=$Interes_Sugerido/100;
                                                                                    $Interes_Calculado=round($Importe_Adeudado*$Coeficiente);

                                                                                    $Interes_Redondeado = round($Interes_Calculado / 10) * 10;
                                                                                    
                                                                                    
                                                                                    $Nombre_A=$estudiante["nombre"];
                                                                                    $Apellido_A=$estudiante["apellido"];
                                                                                    $Estudiante=$Apellido_A.', '.$Nombre_A;
                                                                                    $resultado[$numeracion] = array(
                                                                                                'id' => $ID_Responsable,
                                                                                                'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                                                                'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                                                                'id_periodo'=> $id_periodo,
                                                                                                'orden'=> $orden,
                                                                                                'interes_sugerido'=> $Interes_Sugerido,
                                                                                                'vencimiento_sugerido'=> $Vencimiento_Sugerido,
                                                                                                'concepto'=> trim(utf8_decode($Concepto)),
                                                                                                'importe_adeudado'=> $Importe_Adeudado,
                                                                                                'importe'=> $Interes_Redondeado,
                                                                                                'id_alumno'=> $ID_Alumno,
                                                                                                'alumno'=> trim(utf8_decode($Estudiante)),
                                                                                                'actualizacion'=> $Actualizable
                        
                                                                                            );
                                                                                    $numeracion++;
                                                                                }
                                                                            

                                                                        }

                                                                }

                                                                //REVISON DE DEUDA ANTRERIOR
                                                                $Saldo_Deudor=0;
                                                                $busqueda_comprobantes_impagos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                                            SELECT cc.Id, cc.Id_Comprobante, cc.Id_Tipo_Comprobante, cc.Importe, cc.Cancelado, cc.Descripcion, cc.Fecha
                                                                                                            FROM cuenta_corriente cc
                                                                                                            WHERE cc.B=0 and cc.Id_Responsable={$ID_Responsable} and cc.ID_Alumno={$ID_Alumno} and cc.Id_Tipo_Comprobante<>4 and cc.Cancelado<=1 and cc.ID_Periodo<>{$id_periodo} and cc.Interes=1
                                                                                                            ORDER BY cc.Fecha
                                                                                                        ");
                                                                $ctrl_Comp_imp=count($busqueda_comprobantes);
                                                                if(empty($ctrl_Comp_imp))
                                                                    {

                                                                    }
                                                                else
                                                                    {
                                                                        for ($f=0; $f < count($busqueda_comprobantes_impagos); $f++)
                                                                                {
                                                                                    $ID_Cta_Cte = $busqueda_comprobantes_impagos[$f]->Id;
                                                                                    $ID_Comprobante = $busqueda_comprobantes_impagos[$f]->Id_Comprobante;
                                                                                    $Importe_Comprobante= $busqueda_comprobantes_impagos[$f]->Importe;
                                                                                    $Cancelado = $busqueda_comprobantes_impagos[$f]->Cancelado;
                                                                                    $ID_Tipo_Comprobante= $busqueda_comprobantes_impagos[$f]->Id_Tipo_Comprobante;    
                                                                                    $Descripcion_Cta = $busqueda_comprobantes_impagos[$f]->Descripcion;
                                                                                    $Fecha_Movimiento = $busqueda_comprobantes_impagos[$f]->Fecha;
                                                                                    if($Cancelado==0)
                                                                                        {
                                                                                            $Saldo_Deudor=$Saldo_Deudor+$Importe_Comprobante;
                                                                                        }
                                                                                    if($Cancelado==1)
                                                                                        {
                                                                                            $Importe_Imputado=0;
                                                                                            $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                                                        SELECT ci.Importe
                                                                                                                        FROM comprobantes_imputaciones ci
                                                                                                                        WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Cta_Cte}
                                                                                                                    ");
                                                                                                                $Ctrl_Imputaciones=count($busqueda_imputaciones);
                                                                                                                if(empty($Ctrl_Imputaciones))
                                                                                                                    {
                                                                                                                        $Importe_Restante=$Importe_Comprobante;
                                                                                                                    }
                                                                                                                else
                                                                                                                    {
                                                                                                                        for ($g=0; $g < count($busqueda_imputaciones); $g++)
                                                                                                                            {
                                                                                                                                $Importe_Imputado_Movimiento=$busqueda_imputaciones[$g]->Importe;
                                                                                                                                $Importe_Imputado=$Importe_Imputado+$Importe_Imputado_Movimiento;
                                                                                                                                
                                                                                                                            }
                                                                                                                        $Importe_Restante=$Importe_Comprobante-$Importe_Imputado;
                                                                                                                    }
                                                                                            $Saldo_Deudor=$Saldo_Deudor+$Importe_Restante;
                                                                                            

                                                                                        }
                                                                                }
                                                                    }
                                                                if($Saldo_Deudor>=10)
                                                                    {
                                                                        //$Interes_Generado=$Saldo_Deudor*$Interes;
                                                                        //$Interes_Generado=round($Interes_Generado,2);

                                                                        $Concepto='Recargo por mora sobre saldo deudor de $ '.$Saldo_Deudor.' (Nivel: '.$Nivel.' Alumno: '.$Estudiante;
                                                                        $Coeficiente=$Interes_Sugerido/100;
                                                                        $Interes_Calculado=round($Saldo_Deudor*$Coeficiente);

                                                                        $Interes_Redondeado = round($Interes_Calculado / 10) * 10;
                                                                        
                                                                        $resultado[$numeracion] = array(
                                                                                    'id' => $ID_Responsable,
                                                                                    'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                                                    'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                                                    'id_periodo'=> $id_periodo,
                                                                                    'orden'=> $orden,
                                                                                    'interes_sugerido'=> $Interes_Sugerido,
                                                                                    'vencimiento_sugerido'=> $Vencimiento_Sugerido,
                                                                                    'concepto'=> trim(utf8_decode($Concepto)),
                                                                                    'importe_adeudado'=> $Saldo_Deudor,
                                                                                    'importe'=> $Interes_Redondeado,
                                                                                    'id_alumno'=> $ID_Alumno,
                                                                                    'alumno'=> trim(utf8_decode($Estudiante)),
                                                                                    'actualizacion'=> 0

                                                                                );
                                                                        $numeracion++;

                                                                    }

                                        
                                                        }
                                                }
                                            
                                        }
                                    
                                        $Saldo_Deudor=0;
                                        $busqueda_comprobantes_impagos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                    SELECT cc.Id, cc.Id_Comprobante, cc.Id_Tipo_Comprobante, cc.Importe, cc.Cancelado, cc.Descripcion, cc.Fecha
                                                                                    FROM cuenta_corriente cc
                                                                                    WHERE cc.B=0 and cc.Id_Responsable={$ID_Responsable} and cc.ID_Alumno=0 and cc.Id_Tipo_Comprobante<>4 and cc.Cancelado<=1 and cc.ID_Periodo<>{$id_periodo} and cc.Interes=1
                                                                                    ORDER BY cc.Fecha
                                                                                ");
                                        $ctrl_Comp_imp=count($busqueda_comprobantes);
                                        if(empty($ctrl_Comp_imp))
                                            {

                                            }
                                        else
                                            {
                                                for ($f=0; $f < count($busqueda_comprobantes_impagos); $f++)
                                                        {
                                                            $ID_Cta_Cte = $busqueda_comprobantes_impagos[$f]->Id;
                                                            $ID_Comprobante = $busqueda_comprobantes_impagos[$f]->Id_Comprobante;
                                                            $Importe_Comprobante= $busqueda_comprobantes_impagos[$f]->Importe;
                                                            $Cancelado = $busqueda_comprobantes_impagos[$f]->Cancelado;
                                                            $ID_Tipo_Comprobante= $busqueda_comprobantes_impagos[$f]->Id_Tipo_Comprobante;    
                                                            $Descripcion_Cta = $busqueda_comprobantes_impagos[$f]->Descripcion;
                                                            $Fecha_Movimiento = $busqueda_comprobantes_impagos[$f]->Fecha;
                                                            if($Cancelado==0)
                                                                {
                                                                    $Saldo_Deudor=$Saldo_Deudor+$Importe_Comprobante;
                                                                }
                                                            if($Cancelado==1)
                                                                {
                                                                    $Importe_Imputado=0;
                                                                    $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                                SELECT ci.Importe
                                                                                                FROM comprobantes_imputaciones ci
                                                                                                WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Cta_Cte}
                                                                                            ");
                                                                                        $Ctrl_Imputaciones=count($busqueda_imputaciones);
                                                                                        if(empty($Ctrl_Imputaciones))
                                                                                            {
                                                                                                $Importe_Restante=$Importe_Comprobante;
                                                                                            }
                                                                                        else
                                                                                            {
                                                                                                for ($g=0; $g < count($busqueda_imputaciones); $g++)
                                                                                                    {
                                                                                                        $Importe_Imputado_Movimiento=$busqueda_imputaciones[$g]->Importe;
                                                                                                        $Importe_Imputado=$Importe_Imputado+$Importe_Imputado_Movimiento;
                                                                                                        
                                                                                                    }
                                                                                                $Importe_Restante=$Importe_Comprobante-$Importe_Imputado;
                                                                                            }
                                                                    $Saldo_Deudor=$Saldo_Deudor+$Importe_Restante;
                                                                    

                                                                }
                                                        }
                                            }
                                        if($Saldo_Deudor>=10)
                                            {
                                                //$Interes_Generado=$Saldo_Deudor*$Interes;
                                                //$Interes_Generado=round($Interes_Generado,2);

                                                $Concepto='Recargo por mora sobre saldo deudor de $ '.$Saldo_Deudor;
                                                $Coeficiente=$Interes_Sugerido/100;
                                                $Interes_Calculado=round($Saldo_Deudor*$Coeficiente);

                                                $Interes_Redondeado = round($Interes_Calculado / 10) * 10;
                                                
                                                $resultado[$numeracion] = array(
                                                            'id' => $ID_Responsable,
                                                            'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                            'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                            'id_periodo'=> $id_periodo,
                                                            'orden'=> $orden,
                                                            'interes_sugerido'=> $Interes_Sugerido,
                                                            'vencimiento_sugerido'=> $Vencimiento_Sugerido,
                                                            'concepto'=> trim(utf8_decode($Concepto)),
                                                            'importe_adeudado'=> $Saldo_Deudor,
                                                            'importe'=> $Interes_Redondeado,
                                                            'id_alumno'=> $ID_Alumno,
                                                            'alumno'=> trim(utf8_decode($Estudiante)),
                                                            'actualizacion'=> 0

                                                        );
                                                $numeracion++;

                                            }
                                    
                                }

                            

                        }
                    
                    

                }
          return $resultado;
        }

        public function ver_lote_intereses($id, $id_item)
        {
            $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $headers = [
            'Content-Type: application/json',
            ];
            $curl = curl_init();
            $ruta_api2='http://apirest.geofacturacion.com.ar/api/responsables/estudiantes_listado/'.$id_institucion;
            curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPGET,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, false);
            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
            //usleep(100000);
            $data = curl_exec($curl);
            curl_close($curl);
            $data = json_decode($data, true);
            $datos_alumnos = $data['data'];

            $consulta_lote = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ig.ID, ig.ID_Periodo, ig.Orden, ig.Monto, ig.Interes_Aplicado, ig.Estado, ig.Vencimiento, ig.Fecha_Generacion, ig.Hora_Generacion, pd.Id_Mes, pe.Nombre, us.name
                        FROM intereses_generados ig
                        INNER JOIN periodos_detalle pd ON ig.ID_Periodo=pd.Id
                        INNER JOIN periodos pe ON pd.Id_Periodo=pe.Id
                        INNER JOIN users us ON ig.ID_Generacion=us.id
                        WHERE ig.ID={$id_item}
                          
                      ");
            
            $ID_Periodo=$consulta_lote[0]->ID_Periodo;
            $Orden=$consulta_lote[0]->Orden;
            $ID_Estado = $consulta_lote[0]->Estado;
            $Vencimiento_Lote = $consulta_lote[0]->Vencimiento;
            $Fecha_Generacion = $consulta_lote[0]->Fecha_Generacion;
            $Hora_Generacion=$consulta_lote[0]->Hora_Generacion;
            $ID_Mes=$consulta_lote[0]->Id_Mes;
            $Interes_Aplicado=$consulta_lote[0]->Interes_Aplicado;
            $Periodo_General=$consulta_lote[0]->Nombre;
            $Usuario=$consulta_lote[0]->name;
            $Monto=$consulta_lote[0]->Monto;
            if($ID_Mes==1) { $Mes='Enero';}
            if($ID_Mes==2) { $Mes='Febrero';}
            if($ID_Mes==3) { $Mes='Marzo';}
            if($ID_Mes==4) { $Mes='Abril';}
            if($ID_Mes==5) { $Mes='Mayo';}
            if($ID_Mes==6) { $Mes='Junio';}
            if($ID_Mes==7) { $Mes='Julio';}
            if($ID_Mes==8) { $Mes='Agosto';}
            if($ID_Mes==9) { $Mes='Septiembre';}
            if($ID_Mes==10) { $Mes='Octubre';}
            if($ID_Mes==11) { $Mes='Noviembre';}
            if($ID_Mes==12) { $Mes='Diciembre';}

            if($Orden==1) { $Orden_detalle='Primer Vencimiento';}
            if($Orden==2) { $Orden_detalle='Segundo Vencimiento';}
            if($ID_Estado==0) { $Estado='Pendiente de Generar';}
            if($ID_Estado==1) { $Estado='Generado';}

            $Periodo=$Mes.' - '.$Periodo_General;
            $Cobrado=0;
                
            $resultado = array(
                    'id' => $id_item,
                    'lote'=> trim(utf8_decode($Periodo)),
                    'detalle'=> trim(utf8_decode($Orden_detalle)),
                    'fecha_imputacion'=> $Vencimiento_Lote,
                    'fecha_generacion'=> $Fecha_Generacion,
                    'hora_generacion'=> $Hora_Generacion,
                    'usuario_generacion'=> $Usuario,
                    'interes_aplicado'=> $Interes_Aplicado,
                    'monto_total'=> $Monto
                );


          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT igd.ID_Alumno, igd.Descripcion, igd.Importe, res.Nombre, res.Apellido, igd.ID_Responsable
                          FROM intereses_generados_detalles igd
                          INNER JOIN responsabes_economicos res ON igd.ID_Responsable=res.Id
                          WHERE igd.B=0
                          ORDER BY igd.ID
                      ");

        $detalle = array();
        $numeracion=0;
        $monto_cobrado=0;
        for ($j=0; $j < count($listado); $j++)
            {
                $ID_Alumno = $listado[$j]->ID_Alumno;
                $ID_Responsable = $listado[$j]->ID_Responsable;
                $Descripcion = utf8_decode($listado[$j]->Descripcion);
                $Importe = $listado[$j]->Importe;
                $Apellido_F=$listado[$j]->Apellido;
                $Nombre_F=$listado[$j]->Nombre;
                $Responsable=$Apellido_F.', '.$Nombre_F;
                $Apellido_A='';
                $Nombre_A='';
                $chequeo_cobranza= $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ci.Importe
                          FROM cuenta_corriente cc
                          INNER JOIN comprobantes_imputaciones ci ON cc.Id=ci.ID_Cta_Cte
                          WHERE ci.B=0 and cc.ID_Responsable={$ID_Responsable} and cc.Id_Tipo_Comprobante=7 and cc.Importe={$Importe} and cc.B=0 and cc.ID_Alumno={$ID_Alumno} 
                          ORDER BY cc.Id
                      ");
                $Ctrl_Cobranza=count($chequeo_cobranza);
                if(empty($Ctrl_Cobranza))
                    {
                        $monto_cobrado=$monto_cobrado;
                    }
                else
                    {
                        for ($z=0; $z < count($chequeo_cobranza); $z++)
                            {
                                $Importe_Imputado = $chequeo_cobranza[$z]->Importe;
                                $monto_cobrado=$monto_cobrado+$Importe_Imputado;
                            }
                    }
               


                foreach($datos_alumnos as $estudiante) 
                        {

                            $id_estudiante=$estudiante["id"];
                            if($id_estudiante==$ID_Alumno)
                                {
                                    $Nombre_A=$estudiante["nombre"];
                                    $Apellido_A=$estudiante["apellido"];
                                    $Estudiante=$Apellido_A.', '.$Nombre_A;

                                }
                        }
                        $detalle[$j] = array(
                            'responsable' => trim(utf8_decode($Responsable)),
                            'alumno' => trim(utf8_decode($Estudiante)),
                            'concepto' => $Descripcion,
                            'importe' => $Importe
                        );
                
                $numeracion++;
                
            }
        $resultado['listado'] = $detalle;
        $resultado['monto_cobrado'] = $monto_cobrado;

          return $resultado;
        }

        public function generar_lote_intereses($id, $id_periodo, $orden, $fecha, $interes, $arreglo, $id_usuario)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado = array();
            $id_institucion=$id;
            $fecha='2023-12-12';

            

            if($orden==1)
                {
                    //$interes=2.955;
                    //CONSULTO LOS DATOS DE LOTE
                    $datos_lote = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ig.ID
                                FROM intereses_generados ig
                                WHERE ig.B=0 and ig.ID_Periodo={$id_periodo} and ig.Orden={$orden}
                            ");
                    $ID_Lote_Intereses=$datos_lote[0]->ID;
                    $Monto_Total_Intereses=0;
                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                    UPDATE intereses_generados
                    SET Estado=1, Vencimiento='{$fecha}', Fecha_Generacion='{$FechaActual}', Hora_Generacion='{$HoraActual}', ID_Generacion={$id_usuario}
                    WHERE ID={$ID_Lote_Intereses}
                    ");
                    foreach($arreglo as $Linea)
                    {
                    $id_responsable=$Linea['id_responsable'];
                    $id_alumno=$Linea['id_alumno'];
                    $descripcion=$Linea['concepto'];
                    $importe=$Linea['importe'];
                    $actualizable=$Linea["actualizacion"];

                    if($importe>=10)
                        {

                            $creo_interes_tabla = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO intereses_generados_detalles
                            (ID_Lote,ID_Responsable,ID_Alumno,Descripcion,Interes,Importe)
                            VALUES
                            ({$ID_Lote_Intereses},{$id_responsable},{$id_alumno},'{$descripcion}','{$interes}' ,'{$importe}')
                            ");
                            $Monto_Total_Intereses=$Monto_Total_Intereses+$importe;
                            if($actualizable==1)
                                {
                                    //BORRO EL INTERES GENERADO EN EL CICLO ANTERIOR
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}'
                                    WHERE Id_Responsable={$id_responsable} and ID_Alumno={$id_alumno} and Id_Tipo_Comprobante=7 and ID_Periodo={$id_periodo} and B=0 and Facturado=0
                                    ");
                                    
                                }
                            $creo_interes_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Importe,Cancelado,ID_Periodo,Interes)
                            VALUES
                            ({$id_responsable},{$id_alumno},'{$fecha}',7,'{$descripcion}','{$importe}',0,{$id_periodo},1)
                            ");
                        

                        }

                    }
                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                    UPDATE intereses_generados
                    SET Monto='{$Monto_Total_Intereses}'
                    WHERE ID={$ID_Lote_Intereses}
                    ");

                }
            
            if($orden==2)
                {
                    //$interes=2.955;
                    //CONSULTO LOS DATOS DE LOTE
                    $datos_lote = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ig.ID
                                FROM intereses_generados ig
                                WHERE ig.B=0 and ig.ID_Periodo={$id_periodo} and ig.Orden={$orden}
                            ");
                    $ID_Lote_Intereses=$datos_lote[0]->ID;
                    $Monto_Total_Intereses=0;
                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                    UPDATE intereses_generados
                    SET Estado=1, Vencimiento='{$fecha}', Fecha_Generacion='{$FechaActual}', Hora_Generacion='{$HoraActual}', ID_Generacion={$id_usuario}
                    WHERE ID={$ID_Lote_Intereses}
                    ");
                    foreach($arreglo as $Linea)
                    {
                    $id_responsable=$Linea['id_responsable'];
                    $id_alumno=$Linea['id_alumno'];
                    $descripcion=$Linea['concepto'];
                    $importe=$Linea['importe'];
                    $actualizable=$Linea["actualizacion"];

                    if($importe>=10)
                        {

                            $creo_interes_tabla = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO intereses_generados_detalles
                            (ID_Lote,ID_Responsable,ID_Alumno,Descripcion,Interes,Importe)
                            VALUES
                            ({$ID_Lote_Intereses},{$id_responsable},{$id_alumno},'{$descripcion}','{$interes}' ,'{$importe}')
                            ");
                            $Monto_Total_Intereses=$Monto_Total_Intereses+$importe;
                            if($actualizable==1)
                                {
                                    //BORRO EL INTERES GENERADO EN EL CICLO ANTERIOR
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B=999
                                    WHERE Id_Responsable={$id_responsable} and ID_Alumno={$id_alumno} and Id_Tipo_Comprobante=7 and ID_Periodo={$id_periodo} and B=0 and Facturado=0
                                    ");
                                    
                                }
                            $creo_interes_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Importe,Cancelado,ID_Periodo,Interes)
                            VALUES
                            ({$id_responsable},{$id_alumno},'{$fecha}',7,'{$descripcion}','{$importe}',0,{$id_periodo},1)
                            ");
                        

                        }

                    }
                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                    UPDATE intereses_generados
                    SET Monto='{$Monto_Total_Intereses}'
                    WHERE ID={$ID_Lote_Intereses}
                    ");

                }
            
            //VERIFICO QUE EL COMPROBANTE NO TIENE NOTA DE CREDITO
            
                $ctrl_anulacion=1;
                if($ctrl_anulacion>=1)
                    {
                        
                        $resultado='El lote de intereses ha sido generado con exito';
                    

                    }
                else
                    {
                        //YA FUE ANULADO
                        $resultado='error';
                    }
                    
            return $resultado;
        }

    public function consulta_libro_iva($id, $id_empresa)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $resultado=array();
          $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT fe.Id, fe.Numero, fe.Tipo_Factura, fe.Fecha, fe.Importe, fe.Observaciones, emp.Empresa, fe.Pto_Vta, re.Apellido, re.Nombre, fe.ID_Operacion, fe.ID_Anulacion
                                    FROM facturas_emitidas fe
                                    INNER JOIN empresas emp ON fe.ID_Empresa=emp.ID
                                    INNER JOIN responsabes_economicos re ON fe.ID_Responsable=re.ID
                                    WHERE fe.B=0 and fe.ID_Empresa={$id_empresa}
                                    ORDER BY fe.Id desc

                                        ");

           $ctrl_movimientos=count($lista_movientos);
           if(empty($ctrl_movimientos))
            {
                
            }
        else
            {
                for ($j=0; $j < count($lista_movientos); $j++)
                    {
                        $ID_Factura=$lista_movientos[$j]->Id;
                        $Numero_Factura=$lista_movientos[$j]->Numero;
                        $ID_Tipo_Factura=$lista_movientos[$j]->Tipo_Factura;
                        $Responsable_A = $lista_movientos[$j]->Apellido;
                        $Responsable_N = $lista_movientos[$j]->Nombre;
                        $Responsable=$Responsable_A.', '.$Responsable_N;
                        
                        $ID_Anulacion=$lista_movientos[$j]->ID_Anulacion;
                        
                        if($ID_Tipo_Factura==11)
                            {
                                $TipoyNro_Factura='C';
                            }
                        if($ID_Tipo_Factura==12)
                            {
                                $TipoyNro_Factura='ND';
                            }
                        if($ID_Tipo_Factura==13)
                            {
                                $TipoyNro_Factura='NC';
                            }
                        $TipoyNro_Factura=$TipoyNro_Factura.'-'.$Numero_Factura;
        
                        $resultado[$j] = array(
                                                                                     
                            'id'=> $ID_Factura,
                            'fecha'=> $lista_movientos[$j]->Fecha,
                            'tipoynumero'=> $TipoyNro_Factura,
                            'empresa'=> trim(utf8_decode($lista_movientos[$j]->Empresa)),
                            'pto_vta'=> $lista_movientos[$j]->Pto_Vta,
                            'responsable'=> trim(utf8_decode($Responsable)),
                            'importe'=> $lista_movientos[$j]->Importe,
                            'referencia'=> trim(utf8_decode($lista_movientos[$j]->Observaciones))

                      );

                    }
                  
            }
          return $resultado;
        }

    public function generacion_libro_iva($id, $arreglo, $id_usuario, $id_empresa)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado = array();
            $id_institucion=$id;

            /*
            $consulta_novedades= $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT niv.Nivel, for.Titulo, for.Comentario, for.Tipo
                                FROM foro for
                                INNER JOIN nivel niv ON for.ID_Nivel=niv.ID
                                WHERE for.Vencimiento<='{$fechaHoy}' and for.ID_Nivel={$id_nivel}
                                ORDER BY for.Fecha
                                    ");
                                    s
            $ctrl_Existencia=count($consulta_novedades);
            if(empty($ctrl_Existencia))
                {
                    $resultado=array();
                }
            else
                {
                    for ($j=0; $j < count($consulta_novedades); $j++)
                    {
                        $ID_Curso=$consulta_novedades[$j]->ID_Curso;
                        if($ID_Curso>=1)
                            {
                                $consulta_curso= $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cur.Cursos
                                    FROM cursos cur
                                    WHERE cur.ID={$ID_Curso}
                                ");
                                $Encabezado=$consulta_curso[0]->Cursos;
                            }
                        else
                            {
                                $Encabezado=$consulta_novedades[$j]->Nivel;
                            }
                        
                        $resultado[$j] = array(
                                                                                         
                                'encabezado'=> trim(utf8_decode($Encabezado)),
                                'tipo'=> trim(utf8_decode($consulta_novedades[$j]->Tipo)),
                                'titulo'=> trim(utf8_decode($consulta_novedades[$j]->Titulo)),
                                'descripcion'=> trim(utf8_decode($consulta_novedades[$j]->Comentario))
                                
                          );
                        
                    }
                }
            
            */



            $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
            FROM institucion inst
            WHERE inst.Id=1
                ");
$Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
$Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;

            $creo_consulta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO libro_iva_consultas
                            (Fecha,Hora,ID_Usuario,ID_Empresa)
                            VALUES
                            ('{$FechaActual}','{$HoraActual}',{$id_usuario},{$id_empresa})
                            ");
            $consulta_id = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT lic.ID
                            FROM libro_iva_consultas lic
                            WHERE lic.Fecha='{$FechaActual}' and lic.ID_Usuario={$id_usuario} and lic.ID_Empresa={$id_empresa}
                            ORDER BY lic.ID DESC
                              
                          ");
                
            $ID_Operacion=$consulta_id[0]->ID;
            
            foreach($arreglo as $Linea)
                {
                    $id_comprobante=$Linea['id_comprobante'];
                    $importe=$Linea['importe'];
                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                    INSERT INTO libro_iva_consultas_detalle
                    (ID_Consulta,ID_Comprobante,Importe)
                    VALUES
                    ({$ID_Operacion},{$id_comprobante},{$importe})
                    ");

                    

                }
                    
            //if($ctrl_anulacion>=1)
                   // {
                        $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/iva_book.php?id='.$ID_Operacion;
                          
                        $resultado=$Enlace_Recibo;
                    
                   // }
                //else
                  //  {
                        //YA FUE ANULADO
                       // $resultado='error';
                    //}
                    
            return $resultado;
        }

        public function generacion_libro_iva_alicuotas($id, $arreglo, $id_usuario, $id_empresa)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado = array();
            $id_institucion=$id;

            


            $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
            FROM institucion inst
            WHERE inst.Id=1
                ");
            $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
            $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;

            $creo_consulta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO libro_iva_consultas
                            (Fecha,Hora,ID_Usuario,ID_Empresa)
                            VALUES
                            ('{$FechaActual}','{$HoraActual}',{$id_usuario},{$id_empresa})
                            ");
            $consulta_id = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT lic.ID
                            FROM libro_iva_consultas lic
                            WHERE lic.Fecha='{$FechaActual}' and lic.ID_Usuario={$id_usuario} and lic.ID_Empresa={$id_empresa}
                            ORDER BY lic.ID DESC
                              
                          ");
                
            $ID_Operacion=$consulta_id[0]->ID;
            
            foreach($arreglo as $Linea)
                {
                    $id_comprobante=$Linea['id_comprobante'];
                    $importe=$Linea['importe'];
                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                    INSERT INTO libro_iva_consultas_detalle
                    (ID_Consulta,ID_Comprobante,Importe)
                    VALUES
                    ({$ID_Operacion},{$id_comprobante},{$importe})
                    ");

                    

                }
                    
            //if($ctrl_anulacion>=1)
                   // {
                        $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/iva_book_alic.php?id='.$ID_Operacion;
                          
                        $resultado=$Enlace_Recibo;
                    
                   // }
                //else
                  //  {
                        //YA FUE ANULADO
                       // $resultado='error';
                    //}
                    
            return $resultado;
        }


        
}
