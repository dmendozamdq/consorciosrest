<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class CajaRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
        {
            $this->Alumno = $Alumno;
            $this->dataBaseService = $dataBaseService;
        }




        
    public function nueva_cobranza($id)
    {
      
      try {

               date_default_timezone_set('America/Argentina/Buenos_Aires');
               $FechaActual=date("Y-m-d");
               $HoraActual=date("H:i:s");
               $id_institucion=$id;
               $resultado=array();
               $lista_cajas = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT c.Id, c.Nombre
                                  FROM caja c
                                  WHERE c.B=0
                                  ORDER BY c.Nombre

                                    ");

              $ctrl_e=count($lista_cajas);

              if(empty($ctrl_e))
                {
                  $resultado='error';
                }
              else
                {
                    $contador=0;
                    for ($j=0; $j < count($lista_cajas); $j++)
                        {
                            $ID_Caja = $lista_cajas[$j]->Id;
                            $Caja = $lista_cajas[$j]->Nombre;
                            $resultado[$contador]['cajas'][$j] = array(
                                'id_caja' => $ID_Caja,
                                'nombre'=> trim(utf8_decode($Caja))
                             );
                        }
                    $medios_pago = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT mp.Id, mp.Nombre
                        FROM medios_pago mp
                        WHERE mp.B=0 and mp.Estado=1
                    ");
                    for ($k=0; $k < count($medios_pago); $k++)
                        {
                            $ID_Medio_Pago = $medios_pago[$k]->Id;
                            $Medio_Pago = $medios_pago[$k]->Nombre;
                            $resultado[$contador]['medios_pago'][$k] = array(
                                'id_medio_pago' => $ID_Medio_Pago,
                                'nombre'=> trim(utf8_decode($Medio_Pago))
                             );
                            $medios_pago_detalles = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mpd.Id, mpd.Parametro
                                    FROM medios_pago_detalle mpd
                                    WHERE mpd.B=0 and mpd.Id_Medio_Pago={$ID_Medio_Pago}
                                    ORDER BY mpd.Id
                                ");
                            for ($i=0; $i < count($medios_pago_detalles); $i++)
                                {
                                    $ID_Parametro = $medios_pago_detalles[$i]->Id;
                                    $Parametro = $medios_pago_detalles[$i]->Parametro;
                                    $resultado[$contador]['medios_pago'][$k]['parametros'][$i] = array(
                                        'id_parametro' => $ID_Parametro,
                                        'parametro'=> trim(utf8_decode($Parametro))
                                    );
                                }


                        }


                    $contador++;
                       
                }

              
                    return $resultado;

          } catch (\Exception $e) {
              return $e;
          }
        }

        public function comprobantes_pendientes($id, $id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cc.Id, cc.ID_Alumno, cc.Fecha, cc.Descripcion, cc.Id_Comprobante, cc.Importe, cct.Tipo, cc.Cancelado
                          FROM cuenta_corriente cc
                          INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                          WHERE cc.B=0 and cc.Id_Responsable={$id_item} and cc.Cancelado<=1 and cct.Clase<=1
                          ORDER BY cc.Fecha
                      ");

          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Item= $listado[$j]->Id;
                   $ID_Alumno= $listado[$j]->ID_Alumno;
                   $ID_Comprobante=$listado[$j]->Id_Comprobante;
                   $Descripcion=$listado[$j]->Descripcion;
                   $Cancelado=$listado[$j]->Cancelado;
                   $Importe_Original=$listado[$j]->Importe;
                   $Importe_Restante=$Importe_Original;
                   if($Cancelado==1)
                    {
                        if($ID_Comprobante==0)
                            {
                                $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ci.Importe
                                FROM comprobantes_imputaciones ci
                                WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Item}
                                    ");
                                $Cant_Imputaciones = count($busqueda_imputaciones);
                                if(empty($Cant_Imputaciones))
                                    {
                                        
                                    }
                                else
                                    {
                                        //$Importe_Restante=$Importe_Original;
                                        for ($k=0; $k < count($busqueda_imputaciones); $k++)
                                            {
                                                $Importe_Imputado= $busqueda_imputaciones[$k]->Importe;
                                                $Importe_Restante=$Importe_Restante-$Importe_Imputado;
                                            }
                                    }
                            }
                        else
                            {
                                $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ci.Importe
                                FROM comprobantes_imputaciones ci
                                WHERE ci.B=0 and ci.ID_Comprobante={$ID_Comprobante}
                                    ");
                                $Cant_Imputaciones = count($busqueda_imputaciones);
                                if(empty($Cant_Imputaciones))
                                    {
                                        
                                    }
                                else
                                    {
                                        //$Importe_Restante=$Importe_Original;
                                        for ($k=0; $k < count($busqueda_imputaciones); $k++)
                                            {
                                                $Importe_Imputado= $busqueda_imputaciones[$k]->Importe;
                                                $Importe_Restante=$Importe_Restante-$Importe_Imputado;
                                            }
                                    }
                            }
                               
                    }
                if($ID_Alumno>=1)
                        {
                            $headers = [
                                'Content-Type: application/json',
                            ];
                            $curl = curl_init();
                            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno;
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
                                $Alumno_Completo=$Apellido_A.', '.$Nombre_A;
        
                            }
                            $Detalle_Concepto=$Descripcion.' - '.$Alumno_Completo;
                        }
                    else
                        {
                            $Detalle_Concepto=$Descripcion;
                        }
                
                
                $resultado[$j] = array(
                                              'id_cta' => $ID_Item,
                                              'fecha'=> $listado[$j]->Fecha,
                                              'descripcion'=> trim(utf8_decode($Detalle_Concepto)),
                                              'importe'=> floatval($Importe_Restante)
                                          );
                    

                }
          return $resultado;
        }



    public function recibir_cobranza_efectivo($id, $id_item, $fecha, $id_responsable, $observaciones, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $observaciones=utf8_encode($observaciones);

            

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id,Cierre
                                    FROM caja_aperturas
                                    WHERE Fecha='{$fecha}' and Id_Caja=$id_item

                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    $creo_apertura_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO caja_aperturas
                                    (Id_Caja,Fecha,Hora,Id_Apertura)
                                    VALUES ('{$id_item}','{$fecha}','{$HoraActual}','{$id_usuario}')
                                ");
                }
            else
                {
                    $ID_Caja = $control_caja_abierta[0]->Id;
                    $Cierre = $control_caja_abierta[0]->Cierre;
                    if($Cierre==1)
                        {
                            //LA CAJA ESTÁ CERRADA, NO SE PUEDE CONTINUAR
                            $resultado='error_cerrado';
                        }
                }
            $ID_Tipo_Movimiento=1;//PAGO EN EFECTIVO
            $ID_Medio_Pago=1;//EFECTIVO
            $Hora_Insertada=$HoraActual;
            $creo_registro_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO movimientos_caja
                                    (ID_Caja,ID_Usuario,Fecha,Hora,ID_Tipo_Movimiento,Importe,ID_Medio_Pago,Detalle,Id_Responsable,Facturado)
                                    VALUES ('{$id_item}','{$id_usuario}','{$fecha}','{$Hora_Insertada}','{$ID_Tipo_Movimiento}','{$importe}','{$ID_Medio_Pago}','{$observaciones}','{$id_responsable}','{$factura}')
                                ");
            //VERIFICO INSERCION
            $control_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ID
                                    FROM movimientos_caja
                                    WHERE Fecha='{$fecha}' and Hora='{$Hora_Insertada}' and Id_Caja=$id_item and ID_Usuario=$id_usuario and ID_Tipo_Movimiento=$ID_Tipo_Movimiento and Importe='{$importe}' and Id_Responsable=$id_responsable and B=0
                                    ORDER BY ID desc
                                        ");
            $ctrl_insercion=count($control_insercion);
            if(empty($ctrl_insercion))
                {
                    $id_movimiento_caja=0;
                }
            else
                {
                    $ID_Movimiento_Caja = $control_insercion[0]->ID;
                }
            
            //SE REALIZA LA INSERCION EN LA CUENTA CORRIENTE DEL MOVIMIENTO
            $creo_registro_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO cuenta_corriente
                                (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe)
                                VALUES ('{$id_responsable}','{$fecha}','4','{$observaciones}','{$ID_Movimiento_Caja}','{$importe}')
                            ");
            
            
            //REGISTRO DE IMPUTACIONES
            $Importe_Disponible=$importe;
            foreach($detalle_imputaciones as $imputaciones)
             {
                //RECORRO LOS COMPROBANTES QUE VIENEN IMPUTADOS
                $ID_Cta=$imputaciones["id_cta"];
                //$Importe_Comprobante=$imputaciones["importe"];
                $control_importe_restante = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Cancelado, Importe
                                    FROM cuenta_corriente
                                    WHERE Id='{$ID_Cta}'
                                        ");
                $Estado_Cancelacion_Movimiento = $control_importe_restante[0]->Cancelado;
                $Importe_Comprobante = $control_importe_restante[0]->Importe;
                if($Estado_Cancelacion_Movimiento==1)
                    {
                        //EL MOVIMIENTO REGISTRA PAGOS ANTERIORES, VERIFICO
                        $Suma_Pagos_Anteriores=0;
                        $pagos_anteriores = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Importe
                                    FROM comprobantes_imputaciones
                                    WHERE Id_Cta_Cte='{$ID_Cta}' and B=0
                                        ");
                        $Ctrl_Pagos_Ant=count($pagos_anteriores);
                        if($Ctrl_Pagos_Ant>=1)
                            {
                                for ($j=0; $j < count($pagos_anteriores); $j++)
                                    {
                                        $Importe_registrado=$pagos_anteriores[$j]->Importe;
                                        $Suma_Pagos_Anteriores=$Suma_Pagos_Anteriores+$Importe_registrado;
                                    }
                                $Importe_Comprobante=$Importe_Comprobante-$Suma_Pagos_Anteriores;
                            }

                    }
                


                if($Importe_Disponible>0)
                    {
                        //SI EL IMPORTE DISPONIBLE ALCANZA PARA REALIZAR EL PAGO COMPLETO DEL COMPROBANTE
                        if($Importe_Disponible>=$Importe_Comprobante)
                            {
                                $detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                                $Importe_Disponible=$Importe_Disponible-$Importe_Comprobante;
                                //CANCELO COMPROBANTE
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='2'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                //VERIFICO QUE TIPO DE COMPROBANTE PAGO
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                //SI ES UNA CUOTA EMITIDA POR LOTE
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='2'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                                //SI ES UN RECARGO O CONCEPTO ADICIONAL
                                if(($ID_Tipo_Comprobante_pagado==7) or ($ID_Tipo_Comprobante_pagado==8) or ($ID_Tipo_Comprobante_pagado==9) or ($ID_Tipo_Comprobante_pagado==10) or ($ID_Tipo_Comprobante_pagado==11))
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                                //SI ES UN SALDO INICIAL
                                if($ID_Tipo_Comprobante_pagado==1)
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                            }
                        else
                            //SI EL IMPORTE DISPONIBLE NO ALCANZA PARA EL TOTAL DEL PAGO DISPONIBLE
                            {
                                $Importe_Imputable=$Importe_Disponible;
                                $Importe_Disponible=0;
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='1'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='1'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                                if($ID_Tipo_Comprobante_pagado==1)
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                                if(($ID_Tipo_Comprobante_pagado==7) or ($ID_Tipo_Comprobante_pagado==8) or ($ID_Tipo_Comprobante_pagado==9) or ($ID_Tipo_Comprobante_pagado==10) or ($ID_Tipo_Comprobante_pagado==11))
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }


                            }
                    }
                
                

            }

            if($recibo==1)
                {
                    
                    if($factura==0)
                        {
//GENERO SCRIP PARA ENVIAR RECIBO POR CORREO

                    //VERIFICO DATOS DEL RESPONSABLE
                    $datos_responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Nombre, Apellido, Email
                                    FROM responsabes_economicos
                                    WHERE Id={$id_responsable}

                                        ");
                    $Ctrl_Existencia=count($datos_responsable);
                    if(empty($Ctrl_Existencia))
                        {
                            
                        }
                    else
                        {
                            $Nombre_R = $datos_responsable[0]->Nombre;
                            $Apellido_R = $datos_responsable[0]->Apellido;
                            $destinatario = $Apellido_R.', '.$Nombre_R;
                            $mail = $datos_responsable[0]->Email;
                            //$mail = 'dmendozamdq@gmail.com';
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
                                VALUES ({$ID_Movimiento_Caja},9,'{$FechaActual}','{$HoraActual}','{$mail}','{$destinatario}','{$Cadena_Aleatoria}')
                            ");
                            //CONSULTO ID NUEVO
                            $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT ec.ID
                                            FROM envio_comprobantes ec
                                            WHERE ec.B=0 and ec.ID_Comprobante={$ID_Movimiento_Caja} and ec.Tipo_Comprobante=9
                                            ");
                            $ID_Envio=$check_insercion[0]->ID;
                            
                        }

                        }
                                        

                }

            $resultado='pago registrado';
            //$resultado=$detalleZ;

            return $resultado;
                        


    }

    public function recibir_cobranza_transferencia($id, $id_item, $fecha, $id_responsable, $observaciones, $banco, $referencia, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $observaciones=utf8_encode($observaciones);
            $banco=utf8_encode($banco);
            $referencia=utf8_encode($referencia);
            $detalles='Transferencia: '.$banco.'-'.$referencia.'-'.$observaciones;

            

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id,Cierre
                                    FROM caja_aperturas
                                    WHERE Fecha='{$fecha}' and Id_Caja=$id_item

                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    $creo_apertura_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO caja_aperturas
                                    (Id_Caja,Fecha,Hora,Id_Apertura)
                                    VALUES ('{$id_item}','{$fecha}','{$HoraActual}','{$id_usuario}')
                                ");
                }
            else
                {
                    $ID_Caja = $control_caja_abierta[0]->Id;
                    $Cierre = $control_caja_abierta[0]->Cierre;
                    if($Cierre==1)
                        {
                            //LA CAJA ESTÁ CERRADA, NO SE PUEDE CONTINUAR
                            $resultado='error_cerrado';
                        }
                }
            $ID_Tipo_Movimiento=2;//TRANSFERENCIA
            $ID_Medio_Pago=2;//TRANSFERENCIA
            $Hora_Insertada=$HoraActual;
            $creo_registro_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO movimientos_caja
                                    (ID_Caja,ID_Usuario,Fecha,Hora,ID_Tipo_Movimiento,Importe,ID_Medio_Pago,Detalle,Id_Responsable,Facturado)
                                    VALUES ('{$id_item}','{$id_usuario}','{$fecha}','{$Hora_Insertada}','{$ID_Tipo_Movimiento}','{$importe}','{$ID_Medio_Pago}','{$detalles}','{$id_responsable}','{$factura}')
                                ");
            //VERIFICO INSERCION
            $control_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ID
                                    FROM movimientos_caja
                                    WHERE Fecha='{$fecha}' and Hora='{$Hora_Insertada}' and Id_Caja=$id_item and ID_Usuario=$id_usuario and ID_Tipo_Movimiento=$ID_Tipo_Movimiento and Importe='{$importe}' and Id_Responsable=$id_responsable and B=0
                                    ORDER BY ID desc
                                        ");
            $ctrl_insercion=count($control_insercion);
            if(empty($ctrl_insercion))
                {
                    $id_movimiento_caja=0;
                }
            else
                {
                    $ID_Movimiento_Caja = $control_insercion[0]->ID;
                }
            

            $creo_registro_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO cuenta_corriente
                                (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe)
                                VALUES ('{$id_responsable}','{$fecha}','4','{$detalles}','{$ID_Movimiento_Caja}','{$importe}')
                            ");
            
            //REGISTRO DE IMPUTACIONES
            $Importe_Disponible=$importe;
            foreach($detalle_imputaciones as $imputaciones) {
                //RECORRO LOS COMPROBANTES QUE VIENEN IMPUTADOS
                $ID_Cta=$imputaciones["id_cta"];
                $control_importe_restante = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Cancelado, Importe
                                    FROM cuenta_corriente
                                    WHERE Id='{$ID_Cta}'
                                        ");
                $Estado_Cancelacion_Movimiento = $control_importe_restante[0]->Cancelado;
                $Importe_Comprobante = $control_importe_restante[0]->Importe;
                if($Estado_Cancelacion_Movimiento==1)
                    {
                        //EL MOVIMIENTO REGISTRA PAGOS ANTERIORES, VERIFICO
                        $Suma_Pagos_Anteriores=0;
                        $pagos_anteriores = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Importe
                                    FROM comprobantes_imputaciones
                                    WHERE Id_Cta_Cte='{$ID_Cta}' and B=0
                                        ");
                        $Ctrl_Pagos_Ant=count($pagos_anteriores);
                        if($Ctrl_Pagos_Ant>=1)
                            {
                                for ($j=0; $j < count($pagos_anteriores); $j++)
                                    {
                                        $Importe_registrado=$pagos_anteriores[$j]->Importe;
                                        $Suma_Pagos_Anteriores=$Suma_Pagos_Anteriores+$Importe_registrado;
                                    }
                                $Importe_Comprobante=$Importe_Comprobante-$Suma_Pagos_Anteriores;
                            }

                    }
                


                if($Importe_Disponible>0)
                    {
                        //SI EL IMPORTE DISPONIBLE ALCANZA PARA REALIZAR EL PAGO COMPLETO DEL COMPROBANTE
                        if($Importe_Disponible>=$Importe_Comprobante)
                            {
                                $detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                                $Importe_Disponible=$Importe_Disponible-$Importe_Comprobante;
                                //CANCELO COMPROBANTE
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='2'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                //VERIFICO QUE TIPO DE COMPROBANTE PAGO
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                //SI ES UNA CUOTA EMITIDA POR LOTE
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='2'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                                //SI ES UN RECARGO O CONCEPTO ADICIONAL
                                if(($ID_Tipo_Comprobante_pagado==7) or ($ID_Tipo_Comprobante_pagado==8) or ($ID_Tipo_Comprobante_pagado==9) or ($ID_Tipo_Comprobante_pagado==10) or ($ID_Tipo_Comprobante_pagado==11))
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                                //SI ES UN SALDO INICIAL
                                if($ID_Tipo_Comprobante_pagado==1)
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                            }
                        else
                            //SI EL IMPORTE DISPONIBLE NO ALCANZA PARA EL TOTAL DEL PAGO DISPONIBLE
                            {
                                $Importe_Imputable=$Importe_Disponible;
                                $Importe_Disponible=0;
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='1'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='1'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                                if($ID_Tipo_Comprobante_pagado==1)
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                                if(($ID_Tipo_Comprobante_pagado==7) or ($ID_Tipo_Comprobante_pagado==8) or ($ID_Tipo_Comprobante_pagado==9) or ($ID_Tipo_Comprobante_pagado==10) or ($ID_Tipo_Comprobante_pagado==11))
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }


                            }
                    }
                
                
                

            }
            /*
            //REGISTRO DE IMPUTACIONES
            $Importe_Disponible=$importe;
            foreach($detalle_imputaciones as $imputaciones) {
        
                $ID_Cta=$imputaciones["id_cta"];
                $Importe_Comprobante=$imputaciones["importe"];
                //$detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                if($Importe_Disponible>0)
                    {
                        if($Importe_Disponible>=$Importe_Comprobante)
                            {
                                $detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                                $Importe_Disponible=$Importe_Disponible-$Importe_Comprobante;
                                //CANCELO COMPROBANTE
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='2'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");

                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='2'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                            }
                        else
                            {
                                $Importe_Imputable=$Importe_Disponible;
                                $Importe_Disponible=0;

                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='1'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='1'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','0','{$ID_Cta}')
                                        ");
                                        
                                    }


                            }
                    }
                
                

            }
            */
            if($recibo==1)
                {
                    
                    if($factura==0)
                        {
//GENERO SCRIP PARA ENVIAR RECIBO POR CORREO

                    //VERIFICO DATOS DEL RESPONSABLE
                    $datos_responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Nombre, Apellido, Email
                                    FROM responsabes_economicos
                                    WHERE Id={$id_responsable}

                                        ");
                    $Ctrl_Existencia=count($datos_responsable);
                    if(empty($Ctrl_Existencia))
                        {
                            
                        }
                    else
                        {
                            $Nombre_R = $datos_responsable[0]->Nombre;
                            $Apellido_R = $datos_responsable[0]->Apellido;
                            $destinatario = $Apellido_R.', '.$Nombre_R;
                            $mail = $datos_responsable[0]->Email;
                            //$mail = 'dmendozamdq@gmail.com';
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
                                VALUES ({$ID_Movimiento_Caja},9,'{$FechaActual}','{$HoraActual}','{$mail}','{$destinatario}','{$Cadena_Aleatoria}')
                            ");
                            //CONSULTO ID NUEVO
                            $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT ec.ID
                                            FROM envio_comprobantes ec
                                            WHERE ec.B=0 and ec.ID_Comprobante={$ID_Movimiento_Caja} and ec.Tipo_Comprobante=9
                                            ");
                            $ID_Envio=$check_insercion[0]->ID;
                            
                        }
                    
                        }
                    

                }

            $resultado='pago registrado';
            //$resultado=$detalleZ;

            return $resultado;
                        


    }

    public function recibir_cobranza_cheque($id, $id_item, $fecha, $id_responsable, $observaciones, $banco, $referencia, $id_medio_pago, $importe, $factura, $detalle_imputaciones, $id_usuario, $recibo)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $observaciones=utf8_encode($observaciones);
            $banco=utf8_encode($banco);
            $referencia=utf8_encode($referencia);
            $detalles='Cheque: '.$banco.'-'.$referencia.'-'.$observaciones;

            

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id,Cierre
                                    FROM caja_aperturas
                                    WHERE Fecha='{$fecha}' and Id_Caja=$id_item

                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    $creo_apertura_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO caja_aperturas
                                    (Id_Caja,Fecha,Hora,Id_Apertura)
                                    VALUES ('{$id_item}','{$fecha}','{$HoraActual}','{$id_usuario}')
                                ");
                }
            else
                {
                    $ID_Caja = $control_caja_abierta[0]->Id;
                    $Cierre = $control_caja_abierta[0]->Cierre;
                    if($Cierre==1)
                        {
                            //LA CAJA ESTÁ CERRADA, NO SE PUEDE CONTINUAR
                            $resultado='error_cerrado';
                        }
                }
            $ID_Tipo_Movimiento=5;//CHEQUE
            $ID_Medio_Pago=3;//CHEQUE
            $Hora_Insertada=$HoraActual;
            $creo_registro_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO movimientos_caja
                                    (ID_Caja,ID_Usuario,Fecha,Hora,ID_Tipo_Movimiento,Importe,ID_Medio_Pago,Detalle,Id_Responsable,Facturado)
                                    VALUES ('{$id_item}','{$id_usuario}','{$fecha}','{$Hora_Insertada}','{$ID_Tipo_Movimiento}','{$importe}','{$ID_Medio_Pago}','{$detalles}','{$id_responsable}','{$factura}')
                                ");
            //VERIFICO INSERCION
            $control_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ID
                                    FROM movimientos_caja
                                    WHERE Fecha='{$fecha}' and Hora='{$Hora_Insertada}' and Id_Caja=$id_item and ID_Usuario=$id_usuario and ID_Tipo_Movimiento=$ID_Tipo_Movimiento and Importe='{$importe}' and Id_Responsable=$id_responsable and B=0
                                    ORDER BY ID desc
                                        ");
            $ctrl_insercion=count($control_insercion);
            if(empty($ctrl_insercion))
                {
                    $id_movimiento_caja=0;
                }
            else
                {
                    $ID_Movimiento_Caja = $control_insercion[0]->ID;
                }
            

            $creo_registro_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO cuenta_corriente
                                (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe)
                                VALUES ('{$id_responsable}','{$fecha}','4','{$detalles}','{$ID_Movimiento_Caja}','{$importe}')
                            ");
            
            //REGISTRO DE IMPUTACIONES
            $Importe_Disponible=$importe;
            foreach($detalle_imputaciones as $imputaciones) {
                //RECORRO LOS COMPROBANTES QUE VIENEN IMPUTADOS
                $ID_Cta=$imputaciones["id_cta"];
                $control_importe_restante = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Cancelado, Importe
                                    FROM cuenta_corriente
                                    WHERE Id='{$ID_Cta}'
                                        ");
                $Estado_Cancelacion_Movimiento = $control_importe_restante[0]->Cancelado;
                $Importe_Comprobante = $control_importe_restante[0]->Importe;
                if($Estado_Cancelacion_Movimiento==1)
                    {
                        //EL MOVIMIENTO REGISTRA PAGOS ANTERIORES, VERIFICO
                        $Suma_Pagos_Anteriores=0;
                        $pagos_anteriores = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Importe
                                    FROM comprobantes_imputaciones
                                    WHERE Id_Cta_Cte='{$ID_Cta}' and B=0
                                        ");
                        $Ctrl_Pagos_Ant=count($pagos_anteriores);
                        if($Ctrl_Pagos_Ant>=1)
                            {
                                for ($j=0; $j < count($pagos_anteriores); $j++)
                                    {
                                        $Importe_registrado=$pagos_anteriores[$j]->Importe;
                                        $Suma_Pagos_Anteriores=$Suma_Pagos_Anteriores+$Importe_registrado;
                                    }
                                $Importe_Comprobante=$Importe_Comprobante-$Suma_Pagos_Anteriores;
                            }

                    }
                


                if($Importe_Disponible>0)
                    {
                        //SI EL IMPORTE DISPONIBLE ALCANZA PARA REALIZAR EL PAGO COMPLETO DEL COMPROBANTE
                        if($Importe_Disponible>=$Importe_Comprobante)
                            {
                                $detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                                $Importe_Disponible=$Importe_Disponible-$Importe_Comprobante;
                                //CANCELO COMPROBANTE
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='2'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                //VERIFICO QUE TIPO DE COMPROBANTE PAGO
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                //SI ES UNA CUOTA EMITIDA POR LOTE
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='2'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                                //SI ES UN RECARGO O CONCEPTO ADICIONAL
                                if(($ID_Tipo_Comprobante_pagado==7) or ($ID_Tipo_Comprobante_pagado==8) or ($ID_Tipo_Comprobante_pagado==9) or ($ID_Tipo_Comprobante_pagado==10) or ($ID_Tipo_Comprobante_pagado==11))
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                                //SI ES UN SALDO INICIAL
                                if($ID_Tipo_Comprobante_pagado==1)
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','2','{$ID_Cta}')
                                        ");    
                                    }
                            }
                        else
                            //SI EL IMPORTE DISPONIBLE NO ALCANZA PARA EL TOTAL DEL PAGO DISPONIBLE
                            {
                                $Importe_Imputable=$Importe_Disponible;
                                $Importe_Disponible=0;
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='1'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='1'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                                if($ID_Tipo_Comprobante_pagado==1)
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                                if(($ID_Tipo_Comprobante_pagado==7) or ($ID_Tipo_Comprobante_pagado==8) or ($ID_Tipo_Comprobante_pagado==9) or ($ID_Tipo_Comprobante_pagado==10) or ($ID_Tipo_Comprobante_pagado==11))
                                    {
                                        
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }


                            }
                    }
                
                
                

            }
            /*
            //REGISTRO DE IMPUTACIONES
            $Importe_Disponible=$importe;
            foreach($detalle_imputaciones as $imputaciones) {
        
                $ID_Cta=$imputaciones["id_cta"];
                $Importe_Comprobante=$imputaciones["importe"];
                //$detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                if($Importe_Disponible>0)
                    {
                        if($Importe_Disponible>=$Importe_Comprobante)
                            {
                                $detalleZ=$ID_Cta.' - '.$Importe_Comprobante;
                                $Importe_Disponible=$Importe_Disponible-$Importe_Comprobante;
                                //CANCELO COMPROBANTE
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='2'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");

                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='2'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Comprobante}','{$fecha}','1','{$ID_Cta}')
                                        ");
                                        
                                    }
                            }
                        else
                            {
                                $Importe_Imputable=$Importe_Disponible;
                                $Importe_Disponible=0;

                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado='1'
                                    WHERE Id={$ID_Cta} and B=0
                                    ");
                                $item_cta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT cc.Id_Comprobante, cc.Id_Tipo_Comprobante
                                    FROM cuenta_corriente cc
                                    WHERE cc.B=0 and cc.Id={$ID_Cta}
                                ");
                                $ID_Comprobante_pagado = $item_cta[0]->Id_Comprobante;
                                $ID_Tipo_Comprobante_pagado = $item_cta[0]->Id_Tipo_Comprobante;
                                if($ID_Tipo_Comprobante_pagado==2)
                                    {
                                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                            UPDATE comprobantes
                                            SET Cancelado='1'
                                            WHERE Id={$ID_Comprobante_pagado} and B=0
                                            ");
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO comprobantes_imputaciones
                                            (ID_Comprobante,ID_Movimiento,Importe,Fecha,Cancela,ID_Cta_Cte)
                                            VALUES ('{$ID_Comprobante_pagado}','{$ID_Movimiento_Caja}','{$Importe_Imputable}','{$fecha}','0','{$ID_Cta}')
                                        ");
                                        
                                    }


                            }
                    }
                
                

            }
            */
            if($recibo==1)
                {
                    
                    if($factura==0)
                        {
//GENERO SCRIP PARA ENVIAR RECIBO POR CORREO

                    //VERIFICO DATOS DEL RESPONSABLE
                    $datos_responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Nombre, Apellido, Email
                                    FROM responsabes_economicos
                                    WHERE Id={$id_responsable}

                                        ");
                    $Ctrl_Existencia=count($datos_responsable);
                    if(empty($Ctrl_Existencia))
                        {
                            
                        }
                    else
                        {
                            $Nombre_R = $datos_responsable[0]->Nombre;
                            $Apellido_R = $datos_responsable[0]->Apellido;
                            $destinatario = $Apellido_R.', '.$Nombre_R;
                            $mail = $datos_responsable[0]->Email;
                            //$mail = 'dmendozamdq@gmail.com';
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
                                VALUES ({$ID_Movimiento_Caja},9,'{$FechaActual}','{$HoraActual}','{$mail}','{$destinatario}','{$Cadena_Aleatoria}')
                            ");
                            //CONSULTO ID NUEVO
                            $check_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT ec.ID
                                            FROM envio_comprobantes ec
                                            WHERE ec.B=0 and ec.ID_Comprobante={$ID_Movimiento_Caja} and ec.Tipo_Comprobante=9
                                            ");
                            $ID_Envio=$check_insercion[0]->ID;
                            
                        }
                    
                        }
                    

                }

            $resultado='pago registrado';
            //$resultado=$detalleZ;

            return $resultado;
                        


    }

    public function test_facturante($id)
    {
      
      try {

               date_default_timezone_set('America/Argentina/Buenos_Aires');
               $FechaActual=date("Y-m-d");
               $HoraActual=date("H:i:s");
               $id_institucion=$id;
               $resultado=array();
               $id_empresa=1;
               $Username='coned@yopmail.com';
               $Password='9dH814C5yzdy';
               $CompanyID=28393;
               $UserID=2642;
               $SubsidiaryID=2077;
               $PosID=2952;
               $URL='http://synctest.facturante.com';
               $datos_empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT emp.Empresa, emp.CUIT, emp.IIBB, emp.Tax, emp.Inicio_Actividades, emp.Pto_Vta
                                  FROM empresas emp
                                  WHERE emp.ID={$id_empresa}

                                    ");

              $ctrl_e=count($datos_empresa);

              if(empty($ctrl_e))
                {
                  $resultado='error';
                }
              else
                {
                    $contador=0;
                    for ($j=0; $j < count($datos_empresa); $j++)
                        {
                            $Empresa= trim(utf8_decode($datos_empresa[$j]->Empresa));
                            $CUIT= $datos_empresa[$j]->CUIT;
                            $IIBB= $datos_empresa[$j]->IIBB;
                            $Tax= $datos_empresa[$j]->Tax;
                            $Inicio_Actividades= $datos_empresa[$j]->Inicio_Actividades;
                            //$Pto_Vta= $datos_empresa[$j]->Pto_Vta;
                            $Pto_Vta= 2952;

                            $data = array("Username" => $Username, "Password" => $Password, "CountryId" => 1);
                            $data_string = json_encode($data);
                            $ch = curl_init($URL.'/Token');
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                    'Content-Type: application/json',
                                    'Content-Length: ' . strlen($data_string))
                                    );
                            $result = curl_exec($ch);
                            if(curl_errno($ch))
                                {
                                    $resulta='Curl error: ' . curl_error($ch);
                                    $resultado=$resulta;       
                                }
                            else
                                {
                                    $obj = json_decode($result, true);                  
                                    $Token = $obj['response']['token'];                    
                                    $resultado=$Token;
                                }
                            


                            $Descripcion_Producto='CUOTA NIVEL INICIAL 2023 - ABRIL';
                            $Price=array("UnitPrice"=> 25000);
                            
                            $DETAIL=array("pos"=> $Pto_Vta);
                            //$HEADER=array("documentType" => 1, "documentGeneralType" => 1, "documentGeneralType" => 1, "detail" => $DETAIL);
                            $HEADER=array("detail" => $DETAIL);
                            
                            //$RECEIVER=array("documentType" => 1,"documentNumber" => $CUIT, "businessName"=> $Empresa, "taxTreatment"=> $Tax);
                            $ITEMS=array("Quantity"=> 2, "Description"=> $Descripcion_Producto, "Price"=> $Price);
                            
                            $data = array("Header" => $HEADER, "Items" => $ITEMS);

                            //$bearrer=array($Token);

                            //$Token='Fdfs';

                            //$Token_JSON=array("Authorization: Bearer " => $Token);
                            //$Token_JSON=json_encode($Token_JSON);
                            $authorization = "Authorization: Bearer ".$Token;
                            //$authorization = json_encode($authorization);
                            $data_string = json_encode($data);
                            
                            $headers = [
                                'Content-Type: application/json',
                                'Accept: application/json',
                                //'Content-Length: ' . strlen($data_string),
                                $authorization
                            ];
                            $ch = curl_init($URL.'/ElectronicDocument');
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            $result = curl_exec($ch);
                            if(curl_errno($ch))
                                {
                                    $resulta='Curl error: ' . curl_error($ch);
                                    $resultado=$resulta;       
                                }
                            else
                                {
                                    $obj = json_decode($result, true);                  
                                    //$Token = $obj['response']['documents']['accessData']['pdfUrl'];
                                    //$Token_JSON=json_decode($Token_JSON);                   
                                    $resultado=$obj;
                                }

                               // $resultado=$bearrer;
                            /*$resultado[$j] = array(
                                'id_empresa' => $id_empresa,
                                'empresa'=> $Empresa,
                                'CUIT'=> $CUIT,
                                'IIBB'=> $IIBB,
                                'tax'=> $Tax,
                                'Inicio_Actividades'=> $Inicio_Actividades,
                                'Pto_Vta'=> $Pto_Vta
                                
                             );
                             */
                        }
                    
                    $contador++;
                       
                }

              
            return $resultado;

          } catch (\Exception $e) {
              return $e;
          }
        }

    
    public function movimientos_diarios($id,$id_item,$fecha)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $resultado=array();
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
  
              $Total=0;
              $Total_Facturado=0;
              $Total_Pendiente=0;
              $Total_NoFacturable=0;
              $fechaFormateada = date("Y-m-d", strtotime($fecha));
  
              $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                      SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
                                      FROM institucion inst
                                      WHERE inst.Id=1
                                          ");
                $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
                $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;


            $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                      SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.Id_Responsable,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre, re.Apellido, mc.Facturado
                                      FROM movimientos_caja mc
                                      INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
                                      INNER JOIN responsabes_economicos re ON mc.Id_Responsable=re.ID
                                      WHERE mc.Id_Caja='{$id_item}' and mc.B=0 and mc.Fecha='{$fechaFormateada}'
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
                          unset($detalle_facturas);
                          unset($detalle_imputaciones);
                          $detalle_facturas=array();
                          $detalle_imputaciones=array();
                          $ID_Operacion=$lista_movientos[$j]->ID;
                          $ID_Responsable=$lista_movientos[$j]->Id_Responsable;
                          $Responsable = $lista_movientos[$j]->Apellido;
                          $Tipo_Movimiento = $lista_movientos[$j]->Nombre;
                          $FechayHora=$lista_movientos[$j]->Fecha;
                          $ID_Caja=$lista_movientos[$j]->ID_Caja;
                          $ID_User=$lista_movientos[$j]->ID_Usuario;
                          $Importe=$lista_movientos[$j]->Importe;
                          $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                          $Detalle=$lista_movientos[$j]->Detalle;
                          $Facturado=$lista_movientos[$j]->Facturado;
                          $Total=$Total+$Importe;
                          if($Facturado==0)
                              {
                                  $Estado_Facturado='Sin Factura';
                                  $Viene_Factura=0;
                                  $borrable=1;
                                  $Estado_Facturable=0;
                                  $Total_NoFacturable=$Total_NoFacturable+$Importe;
                              }
                              if($Facturado==1)
                              {
                                  $Estado_Facturado='Pendiente';
                                  $Viene_Factura=0;
                                  $borrable=1;
                                  $Estado_Facturable=1;
                                  $Total_Pendiente=$Total_Pendiente+$Importe;
                              }
                              if($Facturado==2)
                              {   
                                  $Estado_Facturado='Facturado';
                                  $Estado_Facturable=1;
                                  $Viene_Factura=1;
                                  $borrable=0;
                                  $Total_Facturado=$Total_Facturado+$Importe;
                                  $busqueda_factura = $this->dataBaseService->selectConexion($id_institucion)->select("
                                      SELECT fe.Id, cc.Comprobante, fe.Numero
                                      FROM facturas_emitidas fe
                                      INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                                      WHERE fe.ID_Operacion='{$ID_Operacion}' and fe.B=0
                                          ");
                                  $ctrl_factura=count($busqueda_factura);
                                  if(empty($ctrl_factura))
                                      {
                                          $detalle_facturas='';
                                      }
                                  else
                                      {
                                          for ($k=0; $k < count($busqueda_factura); $k++)
                                              {
                                                  $ID_Factura=$busqueda_factura[$k]->Id;
                                                  $Tipo_Comprobante = $busqueda_factura[$k]->Comprobante;
                                                  $Numero_Comprobante = $busqueda_factura[$k]->Numero;
                                                  $Comprobante=$Tipo_Comprobante.'-'.$Numero_Comprobante;
                                                  $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice.php?id='.$ID_Factura;
                                                  $detalle_facturas[$k] = array(
                                                                                       
                                                      'id_factura'=> $ID_Factura,
                                                      'comprobante'=> $Comprobante,
                                                      'tipo_movimiento'=> $Tipo_Movimiento,
                                                      'enlace'=> $Enlace
                                                      );
                                              }
                                      }
                                  
  
                              }
                          //CHEQUEO DE IMPUTACIONES
                          $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ci.Importe,ci.Cancela,cc.Descripcion
                              FROM comprobantes_imputaciones ci
                              INNER JOIN cuenta_corriente cc ON ci.ID_Cta_Cte=cc.Id
                              WHERE ci.ID_Movimiento='{$ID_Operacion}' and ci.B=0
                                  ");
                          $ctrl_imputaciones=count($busqueda_imputaciones);
                          if(empty($ctrl_imputaciones))
                              {
                                  $detalle_imputaciones[0] = array(
                                      'importe'=> '0.00',
                                      'detalle_imputacion'=> 'No se han registrado Imputaciones'
                                                                         
                                         
                                  );
                              }
                          else
                              {
                                  for ($m=0; $m < count($busqueda_imputaciones); $m++)
                                      {
                                          $Importe_Imputacion=$busqueda_imputaciones[$m]->Importe;
                                          $Cancela=$busqueda_imputaciones[$m]->Cancela;
                                          $Descripcion=$busqueda_imputaciones[$m]->Descripcion;
                                          
                                          if($Cancela==1)
                                              {
                                                  $Imputacion='A cuenta de '.$Descripcion;
                                              }
                                          else
                                              {
                                                  if($Cancela==2)
                                                  {
                                                      $Imputacion='Completa pago de '.$Descripcion;
                                                  }
                                                  else
                                                  {
                                                      $Imputacion='';
                                                  }
                                              }
                                         
                                        $detalle_imputaciones[$m] = array(
                                          'importe'=> $Importe_Imputacion,
                                          'detalle_imputacion'=> $Imputacion                            
                                         
                                          );
                                          
                                          
                                      }
                              }
                          
                          $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoicep.php?id='.$ID_Operacion;
                                                 
                          $busqueda_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                              SELECT re.Nombre, re.Apellido
                              FROM responsabes_economicos re
                              WHERE re.Id='{$ID_Responsable}'
                                  ");
  
                          $Nombre_R=$busqueda_responsable[0]->Nombre;
                          $Apellido_R=$busqueda_responsable[0]->Apellido;
                          $Responsable=$Apellido_R.', '.$Nombre_R;

                        //EXPLORO LECTURA
                        
                        $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                        FROM envio_comprobantes ec
                        WHERE ec.B=0 and ec.Id_Comprobante={$ID_Operacion} and ec.Tipo_Comprobante=9

                            ");
                        $Control_Envio=count($busqueda_lecturas);
                        if(empty($Control_Envio))
                            {
                                $Leido=0;
                                $Detalla_Lectura='<p>Comprobante no enviado al destinatario';
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
  
                          
                          $resultado[0]['movimientos'][$j] = array(
                                                                                       
                              'id'=> $ID_Operacion,
                              'fecha'=> $FechayHora,
                              'tipo_movimiento'=> $Tipo_Movimiento,
                              'responsable'=> trim(utf8_decode($Responsable)),
                              'tipo_movimiento'=> trim(utf8_decode($Tipo_Movimiento)),
                              'caja'=> $ID_Caja,
                              'id_caja'=> $ID_Caja,
                              'usuario'=> $ID_User,
                              'id_usuario'=> $ID_User,
                              'importe'=> $Importe,
                              'id_medio_pago'=> $ID_Medio_Pago,
                              'detalle'=> trim(utf8_decode($Detalle)),
                              'detalle_imputaciones'=> $detalle_imputaciones,
                              'borrable'=> $borrable,
                              'facturado'=> trim(utf8_decode($Estado_Facturado)),
                              'estado_facturado'=> $Estado_Facturable,
                              'viene_factura'=> $Viene_Factura,
                              'detalle_facturas'=> $detalle_facturas,
                              'enlace_recibo'=> $Enlace_Recibo,
                              'leido'=> $Leido,
                              'detalle_lectura'=> trim(utf8_decode($Detalla_Lectura)),
  
  
                        );
  
                        $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT av.Id,av.Id_Alumno
                                            FROM alumnos_vinculados av
                                            WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                            ORDER BY av.Id
                                        ");
                          $cant_vinculos=count($detalle);
                          
                          if($cant_vinculos>=1)
                              {
                                for ($k=0; $k < count($detalle); $k++) {
                                      //$resultado[$j]['detalle_periodo'][$k] = 1;
                                      $ID_Alumno = $detalle[$k]->Id_Alumno;
                                      foreach($datos_alumnos as $estudiante) {
  
                                          $id_estudiante=$estudiante["id"];
                                          if($id_estudiante==$ID_Alumno)
                                              {
                                                  $Nombre_A=$estudiante["nombre"];
                                                  $Apellido_A=$estudiante["apellido"];
                                                  $habil=1;
                                              }
                                          }
                                      $resultado[0]['movimientos'][$j]['detalle_vinculos'][$k] = array(
                                                                                       
                                                                                        'apellido'=> $Apellido_A,
                                                                                        'nombre'=> $Nombre_A
                                                                                        
                                                                                  );
                                      }
                              }
                          else
                              {
                                  $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                       
                                      'apellido'=> '',
                                      'nombre'=> ''
                                      
                                );
                              }
  
  
                      }
                  $Total=round($Total,2);
                  $Total_Facturado=round($Total_Facturado,2);
                  $Total_NoFacturable=round($Total_NoFacturable,2);
                  $Total_Pendiente=round($Total_Pendiente,2);
                  $resultado[0]['total']=$Total;
                  $resultado[0]['total_facturado']=$Total_Facturado;
                  $resultado[0]['total_pendiente']=$Total_Pendiente;
                  $resultado[0]['total_nofacturado']=$Total_NoFacturable;
  
  
                    
              }
            return $resultado;
            /*
            date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $resultado=array();
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

          
          $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.Id_Responsable,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre, re.Apellido, mc.Facturado
                                    FROM movimientos_caja mc
                                    INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
                                    INNER JOIN responsabes_economicos re ON mc.Id_Responsable=re.ID
                                    WHERE mc.Id_Caja='{$id_item}' and mc.B=0
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
                        unset($detalle_facturas);
                        unset($detalle_imputaciones);
                        $detalle_facturas=array();
                        $detalle_imputaciones=array();
                        $ID_Operacion=$lista_movientos[$j]->ID;
                        $ID_Responsable=$lista_movientos[$j]->Id_Responsable;
                        $Responsable = $lista_movientos[$j]->Apellido;
                        $Tipo_Movimiento = $lista_movientos[$j]->Nombre;
                        $FechayHora=$lista_movientos[$j]->Fecha;
                        $ID_Caja=$lista_movientos[$j]->ID_Caja;
                        $ID_User=$lista_movientos[$j]->ID_Usuario;
                        $Importe=$lista_movientos[$j]->Importe;
                        $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                        $Detalle=$lista_movientos[$j]->Detalle;
                        $Facturado=$lista_movientos[$j]->Facturado;
                        if($Facturado==0)
                            {
                                $Estado_Facturado='Sin Factura';
                                $Viene_Factura=0;
                                $borrable=1;
                                $Estado_Facturable=0;
                            }
                            if($Facturado==1)
                            {
                                $Estado_Facturado='Pendiente';
                                $Viene_Factura=0;
                                $borrable=1;
                                $Estado_Facturable=1;
                            }
                            if($Facturado==2)
                            {   
                                $Estado_Facturado='Facturado';
                                $Viene_Factura=1;
                                $borrable=0;
                                $Estado_Facturable=0;
                                $busqueda_factura = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT fe.Id, cc.Comprobante, fe.Numero
                                    FROM facturas_emitidas fe
                                    INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                                    WHERE fe.ID_Operacion='{$ID_Operacion}' and fe.B=0
                                        ");
                                $ctrl_factura=count($busqueda_factura);
                                if(empty($ctrl_factura))
                                    {
                                        $detalle_facturas='';
                                    }
                                else
                                    {
                                        for ($k=0; $k < count($busqueda_factura); $k++)
                                            {
                                                $ID_Factura=$busqueda_factura[$k]->Id;
                                                $Tipo_Comprobante = $busqueda_factura[$k]->Comprobante;
                                                $Numero_Comprobante = $busqueda_factura[$k]->Numero;
                                                $Comprobante=$Tipo_Comprobante.'-'.$Numero_Comprobante;
                                                $Enlace='http://geofacturacion.com.ar/sancayetano/cobranzas/invoice/print_invoice.php?id='.$ID_Factura;
                                                $detalle_facturas[$k] = array(
                                                                                     
                                                    'id_factura'=> $ID_Factura,
                                                    'comprobante'=> $Comprobante,
                                                    'tipo_movimiento'=> $Tipo_Movimiento,
                                                    'enlace'=> $Enlace
                                                    );
                                            }
                                    }
                                

                            }
                        //CHEQUEO DE IMPUTACIONES
                        $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ci.Importe,ci.Cancela,cc.Descripcion
                            FROM comprobantes_imputaciones ci
                            INNER JOIN cuenta_corriente cc ON ci.ID_Cta_Cte=cc.Id
                            WHERE ci.ID_Movimiento='{$ID_Operacion}' and ci.B=0
                                ");
                        $ctrl_imputaciones=count($busqueda_imputaciones);
                        if(empty($ctrl_imputaciones))
                            {
                                $detalle_imputaciones[0] = array(
                                    'importe'=> '0.00',
                                    'detalle_imputacion'=> 'No se han registrado Imputaciones'
                                                                       
                                       
                                );
                            }
                        else
                            {
                                for ($m=0; $m < count($busqueda_imputaciones); $m++)
                                    {
                                        $Importe_Imputacion=$busqueda_imputaciones[$m]->Importe;
                                        $Cancela=$busqueda_imputaciones[$m]->Cancela;
                                        $Descripcion=$busqueda_imputaciones[$m]->Descripcion;
                                        
                                        if($Cancela==1)
                                            {
                                                $Imputacion='A cuenta de '.$Descripcion;
                                            }
                                        else
                                            {
                                                if($Cancela==2)
                                                {
                                                    $Imputacion='Completa pago de '.$Descripcion;
                                                }
                                                else
                                                {
                                                    $Imputacion='';
                                                }
                                            }
                                       
                                      $detalle_imputaciones[$m] = array(
                                        'importe'=> $Importe_Imputacion,
                                        'detalle_imputacion'=> $Imputacion                            
                                       
                                        );
                                        
                                        
                                    }
                            }

                        $busqueda_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT re.Nombre, re.Apellido
                            FROM responsabes_economicos re
                            WHERE re.Id='{$ID_Responsable}'
                                ");

                        $Nombre_R=$busqueda_responsable[0]->Nombre;
                        $Apellido_R=$busqueda_responsable[0]->Apellido;
                        $Responsable=$Apellido_R.', '.$Nombre_R;

                        
                        $resultado[$j] = array(
                                                                                     
                            'id'=> $ID_Operacion,
                            'fecha'=> $FechayHora,
                            'tipo_movimiento'=> $Tipo_Movimiento,
                            'responsable'=> trim(utf8_decode($Responsable)),
                            'tipo_movimiento'=> trim(utf8_decode($Tipo_Movimiento)),
                            'caja'=> $ID_Caja,
                            'id_caja'=> $ID_Caja,
                            'usuario'=> $ID_User,
                            'id_usuario'=> $ID_User,
                            'importe'=> $Importe,
                            'id_medio_pago'=> $ID_Medio_Pago,
                            'detalle'=> trim(utf8_decode($Detalle)),
                            'detalle_imputaciones'=> $detalle_imputaciones,
                            'borrable'=> $borrable,
                            'facturado'=> trim(utf8_decode($Estado_Facturado)),
                            'estado_facturado'=> trim(utf8_decode($Estado_Facturable)),
                            'viene_factura'=> $Viene_Factura,
                            'detalle_facturas'=> $detalle_facturas


                      );

                      $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                          SELECT av.Id,av.Id_Alumno
                                          FROM alumnos_vinculados av
                                          WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                          ORDER BY av.Id
                                      ");
                        $cant_vinculos=count($detalle);
                        
                        if($cant_vinculos>=1)
                            {
                              for ($k=0; $k < count($detalle); $k++) {
                                    //$resultado[$j]['detalle_periodo'][$k] = 1;
                                    $ID_Alumno = $detalle[$k]->Id_Alumno;
                                    foreach($datos_alumnos as $estudiante) {

                                        $id_estudiante=$estudiante["id"];
                                        if($id_estudiante==$ID_Alumno)
                                            {
                                                $Nombre_A=$estudiante["nombre"];
                                                $Apellido_A=$estudiante["apellido"];
                                                $habil=1;
                                            }
                                        }
                                    $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                     
                                                                                      'apellido'=> $Apellido_A,
                                                                                      'nombre'=> $Nombre_A
                                                                                      
                                                                                );
                                    }
                            }
                        else
                            {
                                $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                     
                                    'apellido'=> '',
                                    'nombre'=> ''
                                    
                              );
                            }


                    }
                  
            }
          return $resultado;
          */
        }
    
        public function movimientos_historicos($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $resultado=array();
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

            $Total=0;
            $Total_Facturado=0;
            $Total_Pendiente=0;
            $Total_NoFacturable=0;
            $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
            FROM institucion inst
            WHERE inst.Id=1
                ");
        $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
        $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;
          
          $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.Id_Responsable,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre, re.Apellido, mc.Facturado
                                    FROM movimientos_caja mc
                                    INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
                                    INNER JOIN responsabes_economicos re ON mc.Id_Responsable=re.ID
                                    WHERE mc.Id_Caja='{$id_item}' and mc.B=0
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
                        unset($detalle_facturas);
                        unset($detalle_imputaciones);
                        $detalle_facturas=array();
                        $detalle_imputaciones=array();
                        $ID_Operacion=$lista_movientos[$j]->ID;
                        $ID_Responsable=$lista_movientos[$j]->Id_Responsable;
                        $Responsable = $lista_movientos[$j]->Apellido;
                        $Tipo_Movimiento = $lista_movientos[$j]->Nombre;
                        $FechayHora=$lista_movientos[$j]->Fecha;
                        $ID_Caja=$lista_movientos[$j]->ID_Caja;
                        $ID_User=$lista_movientos[$j]->ID_Usuario;
                        $Importe=$lista_movientos[$j]->Importe;
                        $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                        $Detalle=$lista_movientos[$j]->Detalle;
                        $Facturado=$lista_movientos[$j]->Facturado;
                        $Total=$Total+$Importe;
                        if($Facturado==0)
                            {
                                $Estado_Facturado='Sin Factura';
                                $Viene_Factura=0;
                                $borrable=1;
                                $Estado_Facturable=0;
                                $Total_NoFacturable=$Total_NoFacturable+$Importe;
                            }
                            if($Facturado==1)
                            {
                                $Estado_Facturado='Pendiente';
                                $Viene_Factura=0;
                                $borrable=1;
                                $Estado_Facturable=1;
                                $Total_Pendiente=$Total_Pendiente+$Importe;
                            }
                            if($Facturado==2)
                            {   
                                $Estado_Facturado='Facturado';
                                $Estado_Facturable=1;
                                $Viene_Factura=1;
                                $borrable=0;
                                $Total_Facturado=$Total_Facturado+$Importe;
                                $busqueda_factura = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT fe.Id, cc.Comprobante, fe.Numero
                                    FROM facturas_emitidas fe
                                    INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                                    WHERE fe.ID_Operacion='{$ID_Operacion}' and fe.B=0
                                        ");
                                $ctrl_factura=count($busqueda_factura);
                                if(empty($ctrl_factura))
                                    {
                                        $detalle_facturas='';
                                    }
                                else
                                    {
                                        for ($k=0; $k < count($busqueda_factura); $k++)
                                            {
                                                $ID_Factura=$busqueda_factura[$k]->Id;
                                                $Tipo_Comprobante = $busqueda_factura[$k]->Comprobante;
                                                $Numero_Comprobante = $busqueda_factura[$k]->Numero;
                                                $Comprobante=$Tipo_Comprobante.'-'.$Numero_Comprobante;
                                                $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice.php?id='.$ID_Factura;
                                                $detalle_facturas[$k] = array(
                                                                                     
                                                    'id_factura'=> $ID_Factura,
                                                    'comprobante'=> $Comprobante,
                                                    'tipo_movimiento'=> $Tipo_Movimiento,
                                                    'enlace'=> $Enlace
                                                    );
                                            }
                                    }
                                

                            }
                        //CHEQUEO DE IMPUTACIONES
                        $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ci.Importe,ci.Cancela,cc.Descripcion
                            FROM comprobantes_imputaciones ci
                            INNER JOIN cuenta_corriente cc ON ci.ID_Cta_Cte=cc.Id
                            WHERE ci.ID_Movimiento='{$ID_Operacion}' and ci.B=0
                                ");
                        $ctrl_imputaciones=count($busqueda_imputaciones);
                        if(empty($ctrl_imputaciones))
                            {
                                $detalle_imputaciones[0] = array(
                                    'importe'=> '0.00',
                                    'detalle_imputacion'=> 'No se han registrado Imputaciones'
                                                                       
                                       
                                );
                            }
                        else
                            {
                                for ($m=0; $m < count($busqueda_imputaciones); $m++)
                                    {
                                        $Importe_Imputacion=$busqueda_imputaciones[$m]->Importe;
                                        $Cancela=$busqueda_imputaciones[$m]->Cancela;
                                        $Descripcion=$busqueda_imputaciones[$m]->Descripcion;
                                        
                                        if($Cancela==1)
                                            {
                                                $Imputacion='A cuenta de '.$Descripcion;
                                            }
                                        else
                                            {
                                                if($Cancela==2)
                                                {
                                                    $Imputacion='Completa pago de '.$Descripcion;
                                                }
                                                else
                                                {
                                                    $Imputacion='';
                                                }
                                            }
                                       
                                      $detalle_imputaciones[$m] = array(
                                        'importe'=> $Importe_Imputacion,
                                        'detalle_imputacion'=> $Imputacion                            
                                       
                                        );
                                        
                                        
                                    }
                            }

                        $busqueda_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT re.Nombre, re.Apellido
                            FROM responsabes_economicos re
                            WHERE re.Id='{$ID_Responsable}'
                                ");

                        $Nombre_R=$busqueda_responsable[0]->Nombre;
                        $Apellido_R=$busqueda_responsable[0]->Apellido;
                        $Responsable=$Apellido_R.', '.$Nombre_R;
                        
                        $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoicep.php?id='.$ID_Operacion;
                        
                        //EXPLORO LECTURA
                        
                        $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                        FROM envio_comprobantes ec
                        WHERE ec.B=0 and ec.Id_Comprobante={$ID_Operacion} and ec.Tipo_Comprobante=9

                            ");
                        $Control_Envio=count($busqueda_lecturas);
                        if(empty($Control_Envio))
                            {
                                $Leido=0;
                                $Detalla_Lectura='<p>Comprobante no enviado al destinatario';
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
                        
                        $resultado[0]['movimientos'][$j] = array(
                                                                                     
                            'id'=> $ID_Operacion,
                            'fecha'=> $FechayHora,
                            'tipo_movimiento'=> $Tipo_Movimiento,
                            'responsable'=> trim(utf8_decode($Responsable)),
                            'tipo_movimiento'=> trim(utf8_decode($Tipo_Movimiento)),
                            'caja'=> $ID_Caja,
                            'id_caja'=> $ID_Caja,
                            'usuario'=> $ID_User,
                            'id_usuario'=> $ID_User,
                            'importe'=> $Importe,
                            'id_medio_pago'=> $ID_Medio_Pago,
                            'detalle'=> trim(utf8_decode($Detalle)),
                            'detalle_imputaciones'=> $detalle_imputaciones,
                            'borrable'=> $borrable,
                            'facturado'=> trim(utf8_decode($Estado_Facturado)),
                            'estado_facturado'=> $Estado_Facturable,
                            'viene_factura'=> $Viene_Factura,
                            'detalle_facturas'=> $detalle_facturas,
                            'enlace_recibo' => $Enlace_Recibo,
                            'leido'=> $Leido,
                            'detalle_lectura'=> trim(utf8_decode($Detalla_Lectura))


                      );

                      $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                          SELECT av.Id,av.Id_Alumno
                                          FROM alumnos_vinculados av
                                          WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                          ORDER BY av.Id
                                      ");
                        $cant_vinculos=count($detalle);
                        
                        if($cant_vinculos>=1)
                            {
                              for ($k=0; $k < count($detalle); $k++) {
                                    //$resultado[$j]['detalle_periodo'][$k] = 1;
                                    $ID_Alumno = $detalle[$k]->Id_Alumno;
                                    foreach($datos_alumnos as $estudiante) {

                                        $id_estudiante=$estudiante["id"];
                                        if($id_estudiante==$ID_Alumno)
                                            {
                                                $Nombre_A=$estudiante["nombre"];
                                                $Apellido_A=$estudiante["apellido"];
                                                $habil=1;
                                            }
                                        }
                                    $resultado[0]['movimientos'][$j]['detalle_vinculos'][$k] = array(
                                                                                     
                                                                                      'apellido'=> $Apellido_A,
                                                                                      'nombre'=> $Nombre_A
                                                                                      
                                                                                );
                                    }
                            }
                        else
                            {
                                $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                     
                                    'apellido'=> '',
                                    'nombre'=> ''
                                    
                              );
                            }


                    }
                $Total=round($Total,2);
                $Total_Facturado=round($Total_Facturado,2);
                $Total_NoFacturable=round($Total_NoFacturable,2);
                $Total_Pendiente=round($Total_Pendiente,2);
                $resultado[0]['total']=$Total;
                $resultado[0]['total_facturado']=$Total_Facturado;
                $resultado[0]['total_pendiente']=$Total_Pendiente;
                $resultado[0]['total_nofacturado']=$Total_NoFacturable;


                  
            }
          return $resultado;
        }

    public function listado($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT Id,Nombre
                          FROM caja
                          WHERE B=0
                          ORDER BY Nombre
                      ");

          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Caja = $listado[$j]->Id;
                   $Caja = $listado[$j]->Nombre;
                   $movimientos_apertura= $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ca.Id
                            FROM caja_aperturas ca
                            WHERE ca.Id_Caja={$ID_Caja}
                            ORDER BY ca.Id desc
                        ");
                   $Cant_Aperturas=count($movimientos_apertura);
                   $resultado[$j] = array(
                                              'id' => $ID_Caja,
                                              'caja'=> trim(utf8_decode($Caja)),
                                              'cantidad_aperturas' => $Cant_Aperturas

                                          );

                }
          return $resultado;
        }

        public function listado_abiertas($id, $id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT Id,Nombre
                          FROM caja
                          WHERE B=0 and Id={$id_item}
                          ORDER BY Nombre
                      ");

          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Caja = $listado[$j]->Id;
                   $Caja = $listado[$j]->Nombre;
                   $movimientos_apertura= $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ca.Id,ca.Fecha,ca.Hora,ca.Saldo_Inicial,ca.Id_Apertura,ca.Cierre,ca.Saldo_Final,ca.ID_Cierre,ca.Fecha_Cierre,ca.Hora_Cierre
                            FROM caja_aperturas ca
                            WHERE ca.Id_Caja={$ID_Caja}
                            ORDER BY ca.Id desc
                        ");
                   $Cant_Aperturas=count($movimientos_apertura);
                   $resultado[$j] = array(
                                              'id' => $ID_Caja,
                                              'caja'=> trim(utf8_decode($Caja)),
                                              'cantidad_aperturas' => $Cant_Aperturas

                                          );
                    
                    for ($k=0; $k < count($movimientos_apertura); $k++)
                        {
                            $ID_Apertura=$movimientos_apertura[$k]->Id_Apertura;
                            $Fecha_Apertura=$movimientos_apertura[$k]->Fecha;
                            $Hora_Apertura=$movimientos_apertura[$k]->Hora;
                            if($ID_Apertura>=1)
                                {
                                    $Usuarios = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT name
                                        FROM users
                                        WHERE id={$ID_Apertura}
                                        ");
                                    $Ctrl_Usuario=count($Usuarios);
                                    if($Ctrl_Usuario>=1)
                                        {
                                            $User_Apertura=trim(utf8_decode($Usuarios[0]->name));
                                        }
                                    else
                                        {
                                            $User_Apertura='Desconocido';
                                        }
                                    

                                }
                            else
                                {
                                    $User_Apertura='Desconocido';
                                }
                            $Texto_Apertura=$Fecha_Apertura.' a las '.$Hora_Apertura.' por '.$User_Apertura;
                            $Cierre=$movimientos_apertura[$k]->Cierre;
                            if($Cierre==1)
                                {
                                    $ID_Cierre=$movimientos_apertura[$k]->ID_Cierre;
                                    $Fecha_Cierre=$movimientos_apertura[$k]->Fecha_Cierre;
                                    $Hora_Cierre=$movimientos_apertura[$k]->Hora_Cierre;
                                    $Usuarios = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT name
                                        FROM users
                                        WHERE id={$ID_Cierre}
                                        ");
                                    $User_Cierre=trim(utf8_decode($Usuarios[0]->name));
                                    $Texto_Cierre=$Fecha_Cierre.' a las '.$Hora_Cierre.' por '.$User_Cierre;
                                }
                            else
                                {
                                    $Texto_Cierre='Pendiente';
                                }
                            $Fecha_Formateada=$Fecha_Apertura.' 00:00:00';
                            $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT SUM(Importe) AS Total
                                FROM movimientos_caja
                                WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento<>3
                            ");
                        $Monto_Operado=$consulta_caja[0]->Total;
                        $Monto_Operado = is_null($Monto_Operado) ? 0 : $Monto_Operado;
                        
                            


                            $resultado[$j]['aperturas'][$k] = array(
                                'id_apertura' => $movimientos_apertura[$k]->Id,
                                'fecha' => $movimientos_apertura[$k]->Fecha,
                                'datos_apertura'=> $Texto_Apertura,
                                'saldo_inicial' => $movimientos_apertura[$k]->Saldo_Inicial,
                                'monto_operado' => $Monto_Operado,
                                'cierre' => $Cierre,
                                'datos_cierre'=> $Texto_Cierre,
                                'saldo_final' => $movimientos_apertura[$k]->Saldo_Final





                            );
                        }


                }
          return $resultado;
        }


      public function ver($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT rp.*
                            FROM responsabes_economicos rp
                            WHERE rp.B=0 and rp.Id={$id_item}

                      ");

          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Responsable=$listado[$j]->Id;
                   $ID_Estado=$listado[$j]->Estado;
                   if($ID_Estado==1)
                        {
                            $Estado='Activo';
                        }
                   if($ID_Estado==2)
                        {
                            $Estado='Suspendido';
                        }
                   if($ID_Estado==3)
                        {
                            $Estado='Archivado';
                        }
                    $Saldo =$this->saldo($id_institucion,$ID_Responsable);
                    $Saldito = $Saldo[0]['saldo'];
                    $plan_consulta = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT rpmp.Tipo_Medio
                            FROM responsables_economicos_mp rpmp
                            WHERE rpmp.B=0 AND rpmp.ID_Responsable={$id_item}

                        ");
              $Cant_Plan=count($plan_consulta);
              if($Cant_Plan>=1)
                  {
                      $Tipo_Medio = $plan_consulta[0]->Tipo_Medio;
                  }
              else
                  {
                      $Tipo_Medio = 99;
                  }


                   $resultado[$j] = array(
                                                'id' => $ID_Responsable,
                                                'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                'dni'=> $listado[$j]->DNI,
                                                'domicilio'=> trim(utf8_decode($listado[$j]->Domicilio)),
                                                'telefono' => trim(utf8_decode($listado[$j]->Telefono)),
                                                'email'=> trim(utf8_decode($listado[$j]->Email)),
                                                'estado'=> $Estado,
                                                'cuit'=> $listado[$j]->CUIT,
                                                'nombre_fiscal'=> $listado[$j]->Nombre_Fiscal,
                                                'tipo_factura'=> $listado[$j]->Tipo_Factura,
                                                'saldo' => $Saldito,
                                                'plan'=> $Tipo_Medio,
                                          );
                   $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT av.Id,av.Id_Alumno
                            FROM alumnos_vinculados av
                            WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                            ORDER BY av.Id
                        ");
                    $cant_vinculos=count($detalle);
                    if($cant_vinculos>=1)
                        {
                            for ($k=0; $k < count($detalle); $k++) {
                                    //$resultado[$j]['detalle_periodo'][$k] = 1;
                                    $ID_Alumno = $detalle[$k]->Id_Alumno;
                                    //CONSULTO DATOS DE ESTUDIANTE
                                    $headers = [
                                        'Content-Type: application/json',
                                    ];
                                    $curl = curl_init();
                                    $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno;
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

                                    $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                       'id'=> $detalle[$k]->Id,
                                                                        'id_alumno'=> $ID_Alumno,
                                                                        'apellido'=> $Apellido_A,
                                                                        'nombre'=> $Nombre_A,
                                                                        'id_curso' => $ID_Curso_A,
                                                                        'curso' => $Curso_A,
                                                                        'id_nivel' => $ID_Nivel_A


                                                                  );
                                                     }
                        }
                }
          return $resultado;
        }

        public function vincular_estudiante($id,$id_item,$id_alumno,$id_usuario)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id
                                    FROM alumnos_vinculados
                                    WHERE Id_Responsable='{$id_item}' and Id_Alumno='{$id_alumno}' and B=0
                               ");

           $control_existencia=count($check_existencia);
           if(empty($control_existencia))
            {
                //LO AGREGO
                $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO alumnos_vinculados
                                    (Id_Responsable,Id_Alumno,Id_Usuario)
                                    VALUES ('{$id_item}','{$id_alumno}','{$id_usuario}')
                                ");
                $resultado='El Estudiante se ha vinculado con éxito al responsable económico.';
            }
            else
            {
                $resultado='Atención: El Estudiante ya se encuentra vinculado al responsable económico.';
            }

            return $resultado;

        }

        public function borrar_pago($id,$id_item,$id_usuario)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            //BORRO MOVIMIENTO DE CAJA
            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE movimientos_caja
                                SET B=1, ID_B={$id_usuario}, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}'
                                WHERE ID={$id_item}
                                ");
            //$resultado='El Estudiante se ha desvinculado con éxito al responsable económico.';
            //BORRO IMPUTACIONES REALIZADAS
            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ID,ID_Comprobante,ID_Cta_Cte
                                FROM comprobantes_imputaciones
                                WHERE B=0 and ID_Movimiento='{$id_item}'
                        ");
            $Ctrl_Existencia=count($check_existencia);
            if(empty($Ctrl_Existencia))
                {
                    //BUSCO EN LA CUENTA CORRIENTE Y BORRO
                    //BORRO EL MOVIMIENTO DE CAJA EN LA CUENTA
                    $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                    UPDATE cuenta_corriente
                    SET B=1, ID_B={$id_usuario}, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}'
                    WHERE Id_Comprobante={$id_item} AND Id_Tipo_Comprobante=4
                    ");

                }
            else
                {
                    for ($k=0; $k < count($check_existencia); $k++)
                        {
                            $ID_Imputacion = $check_existencia[$k]->ID;
                            $ID_Comprobante = $check_existencia[$k]->ID_Comprobante;
                            $ID_Cta_Cte= $check_existencia[$k]->ID_Cta_Cte;
                            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE comprobantes_imputaciones
                                SET B=1
                                WHERE ID={$ID_Imputacion}
                                ");
                            //OK
                            //CONSULTO SI EL ID_CTA_CTE IMPUTADO TIENE OTRAS IMPUTACIONES ANTERIORES
                            $check_existencia2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ID
                                FROM comprobantes_imputaciones
                                WHERE B=0 and ID_Cta_Cte={$ID_Cta_Cte} and ID<>{$ID_Imputacion}
                                ");
                            $Ctrl_Existencia2=count($check_existencia2);
                            if(empty($Ctrl_Existencia2))
                                {
                                    $Nuevo_Estado=0;
                                }
                            else
                                {
                                    $Nuevo_Estado=1;
                                }
                            //DEVUELVO AL ESTADO ANTERIOR LOS MOVIMIENTOS EN CUENTA QUE HUBO
                            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE cuenta_corriente
                                SET Cancelado={$Nuevo_Estado}
                                WHERE Id={$ID_Cta_Cte}
                                ");
                                //OK
                            //DEVUELVO AL ESTADO ANTERIOR AL COMPROBANTE
                            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE comprobantes
                                SET Cancelado={$Nuevo_Estado}
                                WHERE Id={$ID_Comprobante}
                                ");
                            //OK

                        }
                    //BORRO EL MOVIMIENTO DE CAJA EN LA CUENTA
                    $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                    UPDATE cuenta_corriente
                    SET B=1, ID_B={$id_usuario}, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}'
                    WHERE Id_Comprobante={$id_item} AND Id_Tipo_Comprobante=4
                    ");
                    //OK
                }

          
            $resultado='El pago se ha borrado con éxito.';
           

            return $resultado;

        }

        public function desvincular_estudiante($id,$id_item)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            //LO ACTUALIZO
            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE alumnos_vinculados
                                SET B=1
                                WHERE Id={$id_item}
                                ");
            $resultado='El Estudiante se ha desvinculado con éxito al responsable económico.';

            return $resultado;

        }

        public function egreso_caja($id, $id_item, $descripcion, $importe, $id_usuario)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $observaciones=utf8_encode($descripcion);

            

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id,Cierre
                                    FROM caja_aperturas
                                    WHERE Fecha='{$FechaActual}' and Id_Caja=$id_item

                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    $creo_apertura_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO caja_aperturas
                                    (Id_Caja,Fecha,Hora,Id_Apertura)
                                    VALUES ('{$id_item}','{$FechaActual}','{$HoraActual}','{$id_usuario}')
                                ");
                }
            else
                {
                    $ID_Caja = $control_caja_abierta[0]->Id;
                    $Cierre = $control_caja_abierta[0]->Cierre;
                    if($Cierre==1)
                        {
                            //LA CAJA ESTÁ CERRADA, NO SE PUEDE CONTINUAR
                            $resultado='error_cerrado';
                        }
                }
            $ID_Tipo_Movimiento=3;//EGRESO DE CAJA
            $ID_Medio_Pago=1;//EFECTIVO
            $Hora_Insertada=$HoraActual;
            $creo_registro_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO movimientos_caja
                                    (ID_Caja,ID_Usuario,Fecha,Hora,ID_Tipo_Movimiento,Importe,ID_Medio_Pago,Detalle)
                                    VALUES ('{$id_item}','{$id_usuario}','{$FechaActual}','{$Hora_Insertada}','{$ID_Tipo_Movimiento}','{$importe}','{$ID_Medio_Pago}','{$observaciones}')
                                ");
            

            $resultado='Egreso de Caja registrado';
            //$resultado=$detalleZ;

            return $resultado;
                        


    }

    public function apertura_caja($id, $id_item, $saldo_incial, $id_usuario)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id,Cierre
                                    FROM caja_aperturas
                                    WHERE Fecha='{$FechaActual}' and Id_Caja=$id_item

                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    $creo_apertura_caja = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO caja_aperturas
                                    (Id_Caja,Fecha,Hora,Id_Apertura,Saldo_Inicial)
                                    VALUES ('{$id_item}','{$FechaActual}','{$HoraActual}','{$id_usuario}','{$saldo_incial}')
                                ");
                    $resultado='La caja ha sido abierta con éxito';
                }
            else
                {
                    $ID_Caja = $control_caja_abierta[0]->Id;
                    $Cierre = $control_caja_abierta[0]->Cierre;
                    if($Cierre==1)
                        {
                            //LA CAJA ESTÁ CERRADA, NO SE PUEDE CONTINUAR
                            $resultado='Error: La caja ya se encuentra cerrada';
                        }
                    else
                        {
                            $resultado='Error: La caja ya se encuentra abierta';
                        }
                }
            

            return $resultado;
                        


    }

    public function cierre_caja($id, $id_item, $id_usuario)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ca.Id,ca.Cierre,ca.Fecha,ca.Id_Caja,ca.Saldo_Inicial,caj.Nombre
                                    FROM caja_aperturas ca
                                    INNER JOIN caja caj ON ca.Id_Caja=caj.Id
                                    WHERE ca.Id=$id_item and ca.Cierre=0

                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    
                    $resultado='Error: La caja ya se encuentra cerrada';
                }
            else
                {
                    $Fecha_Apertura=$control_caja_abierta[0]->Fecha;
                    $Caja=trim(utf8_decode($control_caja_abierta[0]->Nombre));
                    $ID_Caja=$control_caja_abierta[0]->Id_Caja;
                    $Saldo_Inicial=$control_caja_abierta[0]->Saldo_Inicial;
                    $Fecha_Formateada=$Fecha_Apertura.' 00:00:00';
                    $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(Importe) AS Total
                        FROM movimientos_caja
                        WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento<>3
                    ");
                   $Monto_Operado=$consulta_caja[0]->Total;
                   $Monto_Operado = is_null($Monto_Operado) ? 0 : $Monto_Operado;
                   $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(Importe) AS Total
                        FROM movimientos_caja
                        WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento=3
                    ");
                   $Monto_Egresado=$consulta_caja[0]->Total;
                   $Monto_Egresado = is_null($Monto_Egresado) ? 0 : $Monto_Egresado;
                   $Monto_Egresado=round($Monto_Egresado,2);
                   $Saldo_Final=$Saldo_Inicial+$Monto_Operado-$Monto_Egresado;
                   $Saldo_Final=round($Saldo_Final,2);
                   
                   $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                   UPDATE caja_aperturas
                   SET Cierre=1, Saldo_Final='{$Saldo_Final}', ID_Cierre={$id_usuario}, Fecha_Cierre='{$FechaActual}', Hora_Cierre='{$HoraActual}'
                   WHERE Id={$id_item}
               ");
                   $resultado='La caja '.$Caja.' del día '.$Fecha_Apertura.' ha sido cerrada con éxito.<br>Saldo Inicial: $ '.$Saldo_Inicial.'<br>Cobranza Total: $ '.$Monto_Operado.'<br>Egresos de Caja: $ '.$Monto_Egresado.'<br>Saldo Final: $ '.$Saldo_Final;





                }
            

            return $resultado;
                        


    }

    public function borrar_egreso_caja($id, $id_item, $id_usuario)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            $control_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT mc.Fecha,mc.ID_Caja
                                    FROM movimientos_caja mc
                                    WHERE mc.ID=$id_item

                                        ");
            $Fecha_Movimiento=$control_caja[0]->Fecha;
            $ID_Caja=$control_caja[0]->ID_Caja;
            $Fecha_Formateada=substr($Fecha_Movimiento,0,-9);
            //$Fecha_Formateada=$Fecha_Apertura.' 00:00:00';

            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ca.Id,ca.Cierre,ca.Fecha,ca.Id_Caja,ca.Saldo_Inicial
                                    FROM caja_aperturas ca
                                    WHERE ca.Id_Caja=$ID_Caja and ca.Fecha='{$Fecha_Formateada}' and ca.Cierre=0
                                        ");
            $ctrl_caja=count($control_caja_abierta);

            if(empty($ctrl_caja))
                {
                    //NO HAY CAJA ABIERTA, SE ABRE
                    
                    $resultado='Error: La caja ya se encuentra cerrada y no se peude modificar.';
                }
            else
                {
                    
                   
                   $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                   UPDATE movimientos_caja
                   SET B=1, ID_B={$id_usuario},Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}'
                   WHERE Id={$id_item}
               ");
                   
               
               $resultado='El egreso de caja ha sido eliminado con éxito';





                }
            

            return $resultado;
                        


    }

    public function planilla_caja($id, $id_item, $id_fecha)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $resultado=array();

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
    
    
                $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
                                        FROM institucion inst
                                        WHERE inst.Id=1
                                            ");
                  $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
                  $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;
           
            $control_caja_abierta = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ca.Id,ca.Id_Caja,caj.Nombre,ca.Fecha,ca.Hora,ca.Saldo_Inicial,ca.Id_Apertura,ca.Cierre,ca.Saldo_Final,ca.ID_Cierre,ca.Fecha_Cierre,ca.Hora_Cierre
                                    FROM caja_aperturas ca
                                    INNER JOIN caja caj ON ca.Id_Caja=caj.Id
                                    WHERE ca.Id=$id_item

                                        ");
            $ctrl_caja=count($control_caja_abierta);

        
            if(empty($ctrl_caja))
                {
                    $resultado='Error: La caja que esá consultado no se encuentra abierta';
                }
            else
                {
                    $ID_Caja = $control_caja_abierta[0]->Id_Caja;
                    $ID_Apertura = $control_caja_abierta[0]->Id;
                    $Caja=trim(utf8_decode($control_caja_abierta[0]->Nombre));
                    $Fecha_Apertura = $control_caja_abierta[0]->Fecha;
                    $Hora_Apertura = $control_caja_abierta[0]->Hora;
                    $Saldo_Inicial = $control_caja_abierta[0]->Saldo_Inicial;
                    $ID_Apertura = $control_caja_abierta[0]->Id_Apertura;
                    $Cierre = $control_caja_abierta[0]->Cierre;
                    $Saldo_Final = $control_caja_abierta[0]->Saldo_Final;
                    $ID_Cierre = $control_caja_abierta[0]->ID_Cierre;
                    $Fecha_Cierre = $control_caja_abierta[0]->Fecha_Cierre;
                    $Hora_Cierre = $control_caja_abierta[0]->Hora_Cierre;
                    if($ID_Apertura>=1)
                        {
                            $Usuarios = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT name
                                FROM users
                                WHERE id={$ID_Apertura}
                                ");
                            $Ctrl_Usuario=count($Usuarios);
                            if($Ctrl_Usuario>=1)
                                {
                                    $User_Apertura=trim(utf8_decode($Usuarios[0]->name));
                                }
                            else
                                {
                                    $User_Apertura='Desconocido';
                                }
                            

                        }
                    else
                        {
                            $User_Apertura='Desconocido';
                        }
                    $Texto_Apertura=$Fecha_Apertura.' a las '.$Hora_Apertura.' por '.$User_Apertura;
                    //s$Cierre=$movimientos_apertura[$k]->Cierre;
                    if($Cierre==1)
                        {
                            $Usuarios = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT name
                                FROM users
                                WHERE id={$ID_Cierre}
                                ");
                            $User_Cierre=trim(utf8_decode($Usuarios[0]->name));
                            $Texto_Cierre=$Fecha_Cierre.' a las '.$Hora_Cierre.' por '.$User_Cierre;
                        }
                    else
                        {
                            $Texto_Cierre='Pendiente';
                        }
                    $Fecha_Formateada=$Fecha_Apertura.' 00:00:00';
                    $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(Importe) AS Total
                        FROM movimientos_caja
                        WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento<>3
                    ");
                   $Monto_Operado=$consulta_caja[0]->Total;
                   $Monto_Operado = is_null($Monto_Operado) ? 0 : $Monto_Operado;

                   
                   $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(Importe) AS Total
                        FROM movimientos_caja
                        WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento=1
                    ");
                   $Monto_Operado_Efectivo=$consulta_caja[0]->Total;
                   $Monto_Operado_Efectivo = is_null($Monto_Operado_Efectivo) ? 0 : $Monto_Operado_Efectivo;
                   $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(Importe) AS Total
                        FROM movimientos_caja
                        WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento=2
                    ");
                   $Monto_Operado_Transferencia=$consulta_caja[0]->Total;
                   $Monto_Operado_Transferencia = is_null($Monto_Operado_Transferencia) ? 0 : $Monto_Operado_Transferencia;
                   $consulta_caja = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT SUM(Importe) AS Total
                        FROM movimientos_caja
                        WHERE B=0 AND ID_Caja={$ID_Caja} and Fecha='{$Fecha_Formateada}' and ID_Tipo_Movimiento=3
                    ");
                   $Monto_Operado_Egresos=$consulta_caja[0]->Total;
                   $Monto_Operado_Egresos = is_null($Monto_Operado_Egresos) ? 0 : $Monto_Operado_Egresos;

                    $resultado[0]['datos_caja']= array(
                        'id_caja' => $ID_Caja,
                        'id_apertura' => $id_item,
                        'caja' => $Caja,
                        'fecha' => $Fecha_Apertura,
                        'datos_apertura'=> $Texto_Apertura,
                        'saldo_inicial' => $Saldo_Inicial,
                        'monto_operado_total' => $Monto_Operado,
                        'monto_operado_efectivo' => $Monto_Operado_Efectivo,
                        'monto_operado_transferencia' => $Monto_Operado_Transferencia,
                        'monto_egresos' => $Monto_Operado_Egresos,
                        'cierre'=> $Cierre,
                        'datos_cierre'=> $Texto_Cierre,
                        'saldo_final' => $Saldo_Final

                    );
                $detalle_efectivo=array();
            
            //EFECTIVO
            $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.Id_Responsable,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre, re.Apellido, mc.Facturado
            FROM movimientos_caja mc
            INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
            INNER JOIN responsabes_economicos re ON mc.Id_Responsable=re.ID
            WHERE mc.Id_Caja='{$ID_Caja}}' and mc.B=0 and mc.Fecha='{$Fecha_Formateada}' and mc.ID_Tipo_Movimiento=1
            ORDER BY mc.ID desc

                ");

            $ctrl_movimientos=count($lista_movientos);
            if(empty($ctrl_movimientos))
            {
                $resultado[0]['movimientos_efectivo']=[];
            }
            else
            {

                $Total=0;
                $Total_NoFacturable=0;
                $Total_Pendiente=0;
                $Total_Facturado=0;
            for ($j=0; $j < count($lista_movientos); $j++)
                {
                    unset($detalle_facturas);
                    unset($detalle_imputaciones);
                    $detalle_facturas=array();
                    $detalle_imputaciones=array();
                    $ID_Operacion=$lista_movientos[$j]->ID;
                    $ID_Responsable=$lista_movientos[$j]->Id_Responsable;
                    $Responsable = $lista_movientos[$j]->Apellido;
                    $Tipo_Movimiento = $lista_movientos[$j]->Nombre;
                    $FechayHora=$lista_movientos[$j]->Fecha;
                    $ID_Caja=$lista_movientos[$j]->ID_Caja;
                    $ID_User=$lista_movientos[$j]->ID_Usuario;
                    $Importe=$lista_movientos[$j]->Importe;
                    $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                    $Detalle=$lista_movientos[$j]->Detalle;
                    $Facturado=$lista_movientos[$j]->Facturado;
                    $Total=$Total+$Importe;
                    if($Facturado==0)
                        {
                            $Estado_Facturado='Sin Factura';
                            $Viene_Factura=0;
                            $borrable=1;
                            $Estado_Facturable=0;
                            $Total_NoFacturable=$Total_NoFacturable+$Importe;
                        }
                        if($Facturado==1)
                        {
                            $Estado_Facturado='Pendiente';
                            $Viene_Factura=0;
                            $borrable=1;
                            $Estado_Facturable=1;
                            $Total_Pendiente=$Total_Pendiente+$Importe;
                        }
                        if($Facturado==2)
                        {   
                            $Estado_Facturado='Facturado';
                            $Estado_Facturable=1;
                            $Viene_Factura=1;
                            $borrable=0;
                            $Total_Facturado=$Total_Facturado+$Importe;
                            $busqueda_factura = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT fe.Id, cc.Comprobante, fe.Numero
                                FROM facturas_emitidas fe
                                INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                                WHERE fe.ID_Operacion='{$ID_Operacion}' and fe.B=0
                                    ");
                            $ctrl_factura=count($busqueda_factura);
                            if(empty($ctrl_factura))
                                {
                                    $detalle_facturas='';
                                }
                            else
                                {
                                    for ($k=0; $k < count($busqueda_factura); $k++)
                                        {
                                            $ID_Factura=$busqueda_factura[$k]->Id;
                                            $Tipo_Comprobante = $busqueda_factura[$k]->Comprobante;
                                            $Numero_Comprobante = $busqueda_factura[$k]->Numero;
                                            $Comprobante=$Tipo_Comprobante.'-'.$Numero_Comprobante;
                                            $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice.php?id='.$ID_Factura;
                                            $detalle_facturas[$k] = array(
                                                                                
                                                'id_factura'=> $ID_Factura,
                                                'comprobante'=> $Comprobante,
                                                'tipo_movimiento'=> $Tipo_Movimiento,
                                                'enlace'=> $Enlace
                                                );
                                        }
                                }
                            

                        }
                    //CHEQUEO DE IMPUTACIONES
                    $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT ci.Importe,ci.Cancela,cc.Descripcion
                        FROM comprobantes_imputaciones ci
                        INNER JOIN cuenta_corriente cc ON ci.ID_Cta_Cte=cc.Id
                        WHERE ci.ID_Movimiento='{$ID_Operacion}' and ci.B=0
                            ");
                    $ctrl_imputaciones=count($busqueda_imputaciones);
                    if(empty($ctrl_imputaciones))
                        {
                            $detalle_imputaciones[0] = array(
                                'importe'=> '0.00',
                                'detalle_imputacion'=> 'No se han registrado Imputaciones'
                                                                
                                
                            );
                        }
                    else
                        {
                            for ($m=0; $m < count($busqueda_imputaciones); $m++)
                                {
                                    $Importe_Imputacion=$busqueda_imputaciones[$m]->Importe;
                                    $Cancela=$busqueda_imputaciones[$m]->Cancela;
                                    $Descripcion=$busqueda_imputaciones[$m]->Descripcion;
                                    
                                    if($Cancela==1)
                                        {
                                            $Imputacion='A cuenta de '.$Descripcion;
                                        }
                                    else
                                        {
                                            if($Cancela==2)
                                            {
                                                $Imputacion='Completa pago de '.$Descripcion;
                                            }
                                            else
                                            {
                                                $Imputacion='';
                                            }
                                        }
                                
                                $detalle_imputaciones[$m] = array(
                                    'importe'=> $Importe_Imputacion,
                                    'detalle_imputacion'=> $Imputacion                            
                                
                                    );
                                    
                                    
                                }
                        }
                    
                    $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoicep.php?id='.$ID_Operacion;
                                        
                    $busqueda_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT re.Nombre, re.Apellido
                        FROM responsabes_economicos re
                        WHERE re.Id='{$ID_Responsable}'
                            ");

                    $Nombre_R=$busqueda_responsable[0]->Nombre;
                    $Apellido_R=$busqueda_responsable[0]->Apellido;
                    $Responsable=$Apellido_R.', '.$Nombre_R;

                //EXPLORO LECTURA
                
                $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                FROM envio_comprobantes ec
                WHERE ec.B=0 and ec.Id_Comprobante={$ID_Operacion} and ec.Tipo_Comprobante=9

                    ");
                $Control_Envio=count($busqueda_lecturas);
                if(empty($Control_Envio))
                    {
                        $Leido=0;
                        $Detalla_Lectura='<p>Comprobante no enviado al destinatario';
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

                    
                    $resultado[0]['movimientos_efectivo'][$j] = array(
                                                                                
                        'id'=> $ID_Operacion,
                        'fecha'=> $FechayHora,
                        'tipo_movimiento'=> $Tipo_Movimiento,
                        'responsable'=> trim(utf8_decode($Responsable)),
                        'tipo_movimiento'=> trim(utf8_decode($Tipo_Movimiento)),
                        'caja'=> $ID_Caja,
                        'id_caja'=> $ID_Caja,
                        'usuario'=> $ID_User,
                        'id_usuario'=> $ID_User,
                        'importe'=> $Importe,
                        'id_medio_pago'=> $ID_Medio_Pago,
                        'detalle'=> trim(utf8_decode($Detalle)),
                        'detalle_imputaciones'=> $detalle_imputaciones,
                        'borrable'=> $borrable,
                        'facturado'=> trim(utf8_decode($Estado_Facturado)),
                        'estado_facturado'=> $Estado_Facturable,
                        'viene_factura'=> $Viene_Factura,
                        'detalle_facturas'=> $detalle_facturas,
                        'enlace_recibo'=> $Enlace_Recibo,
                        'leido'=> $Leido,
                        'detalle_lectura'=> trim(utf8_decode($Detalla_Lectura)),


                );

                $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT av.Id,av.Id_Alumno
                                    FROM alumnos_vinculados av
                                    WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                    ORDER BY av.Id
                                ");
                    $cant_vinculos=count($detalle);
                    
                    if($cant_vinculos>=1)
                        {
                        for ($k=0; $k < count($detalle); $k++) {
                                //$resultado[$j]['detalle_periodo'][$k] = 1;
                                $ID_Alumno = $detalle[$k]->Id_Alumno;
                                foreach($datos_alumnos as $estudiante) {

                                    $id_estudiante=$estudiante["id"];
                                    if($id_estudiante==$ID_Alumno)
                                        {
                                            $Nombre_A=$estudiante["nombre"];
                                            $Apellido_A=$estudiante["apellido"];
                                            $habil=1;
                                        }
                                    }
                                $resultado[0]['movimientos_efectivo'][$j]['detalle_vinculos'][$k] = array(
                                                                                
                                                                                'apellido'=> $Apellido_A,
                                                                                'nombre'=> $Nombre_A
                                                                                
                                                                            );
                                }
                        }
                    else
                        {
                            $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                
                                'apellido'=> '',
                                'nombre'=> ''
                                
                        );
                        }


                }

                
  

            }

             //TRANSFERENCIA
             $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
             SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.Id_Responsable,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre, re.Apellido, mc.Facturado
             FROM movimientos_caja mc
             INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
             INNER JOIN responsabes_economicos re ON mc.Id_Responsable=re.ID
             WHERE mc.Id_Caja='{$ID_Caja}}' and mc.B=0 and mc.Fecha='{$Fecha_Formateada}' and mc.ID_Tipo_Movimiento=2
             ORDER BY mc.ID desc
 
                 ");
 
             $ctrl_movimientos=count($lista_movientos);
             if(empty($ctrl_movimientos))
             {
                 $resultado[0]['movimientos_transferencia']=[];
             }
             else
             {
 
                $Total=0;
                $Total_NoFacturable=0;
                $Total_Pendiente=0;
                $Total_Facturado=0;
             for ($j=0; $j < count($lista_movientos); $j++)
                 {
                     unset($detalle_facturas);
                     unset($detalle_imputaciones);
                     $detalle_facturas=array();
                     $detalle_imputaciones=array();
                     $ID_Operacion=$lista_movientos[$j]->ID;
                     $ID_Responsable=$lista_movientos[$j]->Id_Responsable;
                     $Responsable = $lista_movientos[$j]->Apellido;
                     $Tipo_Movimiento = $lista_movientos[$j]->Nombre;
                     $FechayHora=$lista_movientos[$j]->Fecha;
                     $ID_Caja=$lista_movientos[$j]->ID_Caja;
                     $ID_User=$lista_movientos[$j]->ID_Usuario;
                     $Importe=$lista_movientos[$j]->Importe;
                     $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                     $Detalle=$lista_movientos[$j]->Detalle;
                     $Facturado=$lista_movientos[$j]->Facturado;
                     $Total=$Total+$Importe;
                     if($Facturado==0)
                         {
                             $Estado_Facturado='Sin Factura';
                             $Viene_Factura=0;
                             $borrable=1;
                             $Estado_Facturable=0;
                             $Total_NoFacturable=$Total_NoFacturable+$Importe;
                         }
                         if($Facturado==1)
                         {
                             $Estado_Facturado='Pendiente';
                             $Viene_Factura=0;
                             $borrable=1;
                             $Estado_Facturable=1;
                             $Total_Pendiente=$Total_Pendiente+$Importe;
                         }
                         if($Facturado==2)
                         {   
                             $Estado_Facturado='Facturado';
                             $Estado_Facturable=1;
                             $Viene_Factura=1;
                             $borrable=0;
                             $Total_Facturado=$Total_Facturado+$Importe;
                             $busqueda_factura = $this->dataBaseService->selectConexion($id_institucion)->select("
                                 SELECT fe.Id, cc.Comprobante, fe.Numero
                                 FROM facturas_emitidas fe
                                 INNER JOIN comprobantes_codigos cc ON fe.Tipo_Factura=cc.Codigo
                                 WHERE fe.ID_Operacion='{$ID_Operacion}' and fe.B=0
                                     ");
                             $ctrl_factura=count($busqueda_factura);
                             if(empty($ctrl_factura))
                                 {
                                     $detalle_facturas='';
                                 }
                             else
                                 {
                                     for ($k=0; $k < count($busqueda_factura); $k++)
                                         {
                                             $ID_Factura=$busqueda_factura[$k]->Id;
                                             $Tipo_Comprobante = $busqueda_factura[$k]->Comprobante;
                                             $Numero_Comprobante = $busqueda_factura[$k]->Numero;
                                             $Comprobante=$Tipo_Comprobante.'-'.$Numero_Comprobante;
                                             $Enlace='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoice.php?id='.$ID_Factura;
                                             $detalle_facturas[$k] = array(
                                                                                 
                                                 'id_factura'=> $ID_Factura,
                                                 'comprobante'=> $Comprobante,
                                                 'tipo_movimiento'=> $Tipo_Movimiento,
                                                 'enlace'=> $Enlace
                                                 );
                                         }
                                 }
                             
 
                         }
                     //CHEQUEO DE IMPUTACIONES
                     $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                     SELECT ci.Importe,ci.Cancela,cc.Descripcion
                         FROM comprobantes_imputaciones ci
                         INNER JOIN cuenta_corriente cc ON ci.ID_Cta_Cte=cc.Id
                         WHERE ci.ID_Movimiento='{$ID_Operacion}' and ci.B=0
                             ");
                     $ctrl_imputaciones=count($busqueda_imputaciones);
                     if(empty($ctrl_imputaciones))
                         {
                             $detalle_imputaciones[0] = array(
                                 'importe'=> '0.00',
                                 'detalle_imputacion'=> 'No se han registrado Imputaciones'
                                                                 
                                 
                             );
                         }
                     else
                         {
                             for ($m=0; $m < count($busqueda_imputaciones); $m++)
                                 {
                                     $Importe_Imputacion=$busqueda_imputaciones[$m]->Importe;
                                     $Cancela=$busqueda_imputaciones[$m]->Cancela;
                                     $Descripcion=$busqueda_imputaciones[$m]->Descripcion;
                                     
                                     if($Cancela==1)
                                         {
                                             $Imputacion='A cuenta de '.$Descripcion;
                                         }
                                     else
                                         {
                                             if($Cancela==2)
                                             {
                                                 $Imputacion='Completa pago de '.$Descripcion;
                                             }
                                             else
                                             {
                                                 $Imputacion='';
                                             }
                                         }
                                 
                                 $detalle_imputaciones[$m] = array(
                                     'importe'=> $Importe_Imputacion,
                                     'detalle_imputacion'=> $Imputacion                            
                                 
                                     );
                                     
                                     
                                 }
                         }
                     
                     $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoicep.php?id='.$ID_Operacion;
                                         
                     $busqueda_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                         SELECT re.Nombre, re.Apellido
                         FROM responsabes_economicos re
                         WHERE re.Id='{$ID_Responsable}'
                             ");
 
                     $Nombre_R=$busqueda_responsable[0]->Nombre;
                     $Apellido_R=$busqueda_responsable[0]->Apellido;
                     $Responsable=$Apellido_R.', '.$Nombre_R;
 
                 //EXPLORO LECTURA
                 
                 $busqueda_lecturas= $this->dataBaseService->selectConexion($id_institucion)->select("
                 SELECT ec.Destinatario, ec.MailD, ec.Envio, ec.Fecha_Envio, ec.Hora_Envio, ec.Leido, ec.Hora_Leido, ec.Fecha_Leido
                 FROM envio_comprobantes ec
                 WHERE ec.B=0 and ec.Id_Comprobante={$ID_Operacion} and ec.Tipo_Comprobante=9
 
                     ");
                 $Control_Envio=count($busqueda_lecturas);
                 if(empty($Control_Envio))
                     {
                         $Leido=0;
                         $Detalla_Lectura='<p>Comprobante no enviado al destinatario';
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
 
                     
                     $resultado[0]['movimientos_transferencia'][$j] = array(
                                                                                 
                         'id'=> $ID_Operacion,
                         'fecha'=> $FechayHora,
                         'tipo_movimiento'=> $Tipo_Movimiento,
                         'responsable'=> trim(utf8_decode($Responsable)),
                         'tipo_movimiento'=> trim(utf8_decode($Tipo_Movimiento)),
                         'caja'=> $ID_Caja,
                         'id_caja'=> $ID_Caja,
                         'usuario'=> $ID_User,
                         'id_usuario'=> $ID_User,
                         'importe'=> $Importe,
                         'id_medio_pago'=> $ID_Medio_Pago,
                         'detalle'=> trim(utf8_decode($Detalle)),
                         'detalle_imputaciones'=> $detalle_imputaciones,
                         'borrable'=> $borrable,
                         'facturado'=> trim(utf8_decode($Estado_Facturado)),
                         'estado_facturado'=> $Estado_Facturable,
                         'viene_factura'=> $Viene_Factura,
                         'detalle_facturas'=> $detalle_facturas,
                         'enlace_recibo'=> $Enlace_Recibo,
                         'leido'=> $Leido,
                         'detalle_lectura'=> trim(utf8_decode($Detalla_Lectura)),
 
 
                 );
 
                 $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                     SELECT av.Id,av.Id_Alumno
                                     FROM alumnos_vinculados av
                                     WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                     ORDER BY av.Id
                                 ");
                     $cant_vinculos=count($detalle);
                     
                     if($cant_vinculos>=1)
                         {
                         for ($k=0; $k < count($detalle); $k++) {
                                 //$resultado[$j]['detalle_periodo'][$k] = 1;
                                 $ID_Alumno = $detalle[$k]->Id_Alumno;
                                 foreach($datos_alumnos as $estudiante) {
 
                                     $id_estudiante=$estudiante["id"];
                                     if($id_estudiante==$ID_Alumno)
                                         {
                                             $Nombre_A=$estudiante["nombre"];
                                             $Apellido_A=$estudiante["apellido"];
                                             $habil=1;
                                         }
                                     }
                                 $resultado[0]['movimientos_transferencia'][$j]['detalle_vinculos'][$k] = array(
                                                                                 
                                                                                 'apellido'=> $Apellido_A,
                                                                                 'nombre'=> $Nombre_A
                                                                                 
                                                                             );
                                 }
                         }
                     else
                         {
                             $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                 
                                 'apellido'=> '',
                                 'nombre'=> ''
                                 
                         );
                         }
 
 
                 }
 
                 
   
 
             }

             //EGRESOS
            $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre
            FROM movimientos_caja mc
            INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
            WHERE mc.Id_Caja='{$ID_Caja}}' and mc.B=0 and mc.Fecha='{$Fecha_Formateada}' and mc.ID_Tipo_Movimiento=3
            ORDER BY mc.ID desc

                ");

            $ctrl_movimientos=count($lista_movientos);
            if(empty($ctrl_movimientos))
            {
                $resultado[0]['movimientos_egresos']=[];
            }
            else
            {

                $Total=0;
                $Total_NoFacturable=0;
                $Total_Pendiente=0;
                $Total_Facturado=0;
            for ($j=0; $j < count($lista_movientos); $j++)
                {
                    unset($detalle_facturas);
                    unset($detalle_imputaciones);
                    $detalle_facturas=array();
                    $detalle_imputaciones=array();
                    $ID_Operacion=$lista_movientos[$j]->ID;
                    
                    
                    $FechayHora=$lista_movientos[$j]->Fecha;
                    $ID_Caja=$lista_movientos[$j]->ID_Caja;
                    $ID_User=$lista_movientos[$j]->ID_Usuario;
                    $Importe=$lista_movientos[$j]->Importe;
                    $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                    $Detalle=$lista_movientos[$j]->Detalle;
                    $Total=$Total+$Importe;
                    
                        

                

                    
                    $resultado[0]['movimientos_egresos'][$j] = array(
                                                                                
                        'id'=> $ID_Operacion,
                        'fecha'=> $FechayHora,
                        'caja'=> $ID_Caja,
                        'id_caja'=> $ID_Caja,
                        'usuario'=> $ID_User,
                        'id_usuario'=> $ID_User,
                        'importe'=> $Importe,
                        'id_medio_pago'=> $ID_Medio_Pago,
                        'detalle'=> trim(utf8_decode($Detalle))


                );

                


                }

                
  

            }

            }


                
            

            return $resultado;
                        


    }

        public function estudiantes_vinculables($id)
        {

            $id_institucion=$id;

            //CONSULTO LOS NIVELES
            $headers = [
                'Content-Type: application/json',
            ];
            $curl = curl_init();
            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/niveles_educativos/'.$id_institucion;
            curl_setopt($curl, CURLOPT_URL,  $ruta_api);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); /**Permitimos recibir respuesta*/
            curl_setopt($curl, CURLOPT_HTTPGET,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, false);
            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); /** Archivo donde guardamos datos de sesion */
            $data0 = curl_exec($curl); /** Ejecutamos petición*/
            curl_close($curl);

            //$data=dd($data);
            $data0 = json_decode($data0, true);
            $datos_niveles0 = $data0['data'];
            //EJEMPLO
            $j=0;
            foreach($datos_niveles0 as $niveles0)
                {
                    $Array_niveles=$niveles0["niveles"];
                    foreach($Array_niveles as $niveles)
                        {
                            $ID_Nivel=$niveles["id"];
                            $Nivel=$niveles["nivel"];
                            $curl2 = curl_init();
                            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiantes/'.$id_institucion.'?id='.$ID_Nivel;
                            curl_setopt($curl2, CURLOPT_URL,  $ruta_api);
                            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl2, CURLOPT_HTTPGET,true);
                            curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($curl2, CURLOPT_POST, false);
                            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
                            $data2 = curl_exec($curl2);
                            curl_close($curl2);
                            //$data=dd($data);


                            if($ID_Nivel<=3)
                                {
                                    $data2 = json_decode($data2, true);
                                    $datos_alumnos0 = $data2['data'];
                                    foreach($datos_alumnos0 as $alumnos0)
                                        {
                                            $Array_alumnos=$alumnos0["alumnos"];
                                            foreach($Array_alumnos as $estudiante)
                                                {
                                                    $ID_Al=$estudiante["id"];
                                                    $Nombre_A=$estudiante["nombre"];
                                                    $Apellido_A=$estudiante["apellido"];
                                                    $Curso_A=$estudiante["curso"];
                                                    $DNI_A=$estudiante["dni"];
                                                    $Maternal=$estudiante["maternal"];
                                                    if($Maternal==0)
                                                        {
                                                            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT Id
                                                            FROM alumnos_vinculados
                                                            WHERE Id_Alumno='{$ID_Al}' and B=0
                                                    ");

                                                        $Cant_Vinculos=count($check_existencia);


                                                        $resultado[$j] = array(
                                                                'id'=> $ID_Al,
                                                                'apellido'=> $Apellido_A,
                                                                'nombre'=> $Nombre_A,
                                                                'dni' => $DNI_A,
                                                                'curso' => $Curso_A,
                                                                'nivel' => $Nivel,
                                                                'vinculado' => $Cant_Vinculos
                                                                );

                                                        $j=$j+1;
                                                        }

                                                }

                                        }
                                }
                                if($ID_Nivel==5)
                                {
                                    $data2 = json_decode($data2, true);
                                    $datos_alumnos0 = $data2['data'];
                                    foreach($datos_alumnos0 as $alumnos0)
                                        {
                                            $Array_alumnos=$alumnos0["alumnos"];
                                            foreach($Array_alumnos as $estudiante)
                                                {
                                                    $ID_Al=$estudiante["id"];
                                                    $Nombre_A=$estudiante["nombre"];
                                                    $Apellido_A=$estudiante["apellido"];
                                                    $Curso_A=$estudiante["curso"];
                                                    $DNI_A=$estudiante["dni"];
                                                    $Maternal=$estudiante["maternal"];
                                                    if($Maternal==1)
                                                        {
                                                            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT Id
                                                            FROM alumnos_vinculados
                                                            WHERE Id_Alumno='{$ID_Al}' and B=0
                                                    ");

                                                        $Cant_Vinculos=count($check_existencia);


                                                        $resultado[$j] = array(
                                                                'id'=> $ID_Al,
                                                                'apellido'=> $Apellido_A,
                                                                'nombre'=> $Nombre_A,
                                                                'dni' => $DNI_A,
                                                                'curso' => $Curso_A,
                                                                'nivel' => $Nivel,
                                                                'vinculado' => $Cant_Vinculos
                                                                );

                                                        $j=$j+1;
                                                        }

                                                }

                                        }
                                }
                        }
                }


        //$resultado=$Array_alumnos;
        return $resultado;

        }

        public function estudiantes_listado($id)
        {

            $id_institucion=$id;

            //CONSULTO LOS NIVELES
            $headers = [
                'Content-Type: application/json',
            ];
            $curl = curl_init();
            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/niveles_educativos/'.$id_institucion;
            curl_setopt($curl, CURLOPT_URL,  $ruta_api);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); /**Permitimos recibir respuesta*/
            curl_setopt($curl, CURLOPT_HTTPGET,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, false);
            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); /** Archivo donde guardamos datos de sesion */
            $data0 = curl_exec($curl); /** Ejecutamos petición*/
            curl_close($curl);

            //$data=dd($data);
            $data0 = json_decode($data0, true);
            $datos_niveles0 = $data0['data'];
            //EJEMPLO
            $j=0;
            $level=0;
            foreach($datos_niveles0 as $niveles0)
                {
                    $Array_niveles=$niveles0["niveles"];
                    foreach($Array_niveles as $niveles)
                        {
                            $ID_Nivel=$niveles["id"];
                            $Nivel=$niveles["nivel"];
                            $curl2 = curl_init();
                            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiantes/'.$id_institucion.'?id='.$ID_Nivel;
                            curl_setopt($curl2, CURLOPT_URL,  $ruta_api);
                            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl2, CURLOPT_HTTPGET,true);
                            curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($curl2, CURLOPT_POST, false);
                            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
                            $data2 = curl_exec($curl2);
                            curl_close($curl2);
                            //$data=dd($data);


                            if($ID_Nivel<=3)
                                {
                                    $data2 = json_decode($data2, true);
                                    $datos_alumnos0 = $data2['data'];
                                    foreach($datos_alumnos0 as $alumnos0)
                                        {
                                            $Array_alumnos=$alumnos0["alumnos"];
                                            foreach($Array_alumnos as $estudiante)
                                                {
                                                    $ID_Al=$estudiante["id"];
                                                    $Nombre_A=$estudiante["nombre"];
                                                    $Apellido_A=$estudiante["apellido"];
                                                    $Curso_A=$estudiante["curso"];
                                                    $ID_Curso_A=$estudiante["id_curso"];
                                                    $DNI_A=$estudiante["dni"];
                                                    $Maternal=$estudiante["maternal"];
                                                    if($Maternal==0)
                                                        {
                                                            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT av.Id, av.ID_Responsable
                                                            FROM alumnos_vinculados av
                                                            WHERE av.Id_Alumno='{$ID_Al}' and av.B=0
                                                    ");

                                                        $Cant_Vinculos=count($check_existencia);
                                                        if($Cant_Vinculos>=1)
                                                            {
                                                                $ID_Respo=$check_existencia[0]->ID_Responsable;
                                                                $lista_responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                    SELECT r.Nombre, r.Apellido
                                                                    FROM responsabes_economicos r
                                                                    WHERE r.Id='{$ID_Respo}'
                                                                    ");
                                                                $Cant_Respo=count($lista_responsable);
                                                                if($Cant_Respo>=1)
                                                                    {
                                                                        $Nombre_Respo=trim(utf8_decode($lista_responsable[0]->Nombre));
                                                                        $Apellido_Respo=trim(utf8_decode($lista_responsable[0]->Apellido));
                                                                        $Respo=$Apellido_Respo.', '.$Nombre_Respo;
                                                                    }
                                                                else
                                                                    {
                                                                        $Respo='NC';
                                                                    }
                                                            }
                                                        else
                                                            {
                                                                $Respo='';
                                                            }



                                                        $resultado[$j] = array(
                                                                'id'=> $ID_Al,
                                                                'apellido'=> $Apellido_A,
                                                                'nombre'=> $Nombre_A,
                                                                'dni' => $DNI_A,
                                                                'curso' => $Curso_A,
                                                                'id_curso' => $ID_Curso_A,
                                                                'nivel' => $Nivel,
                                                                'id_nivel' => $ID_Nivel,
                                                                'responsable' => $Respo
                                                                );

                                                        $j=$j+1;
                                                        }

                                                }

                                        }
                                }
                                if($ID_Nivel==5)
                                {
                                    $data2 = json_decode($data2, true);
                                    $datos_alumnos0 = $data2['data'];
                                    foreach($datos_alumnos0 as $alumnos0)
                                        {
                                            $Array_alumnos=$alumnos0["alumnos"];
                                            foreach($Array_alumnos as $estudiante)
                                                {
                                                    $ID_Al=$estudiante["id"];
                                                    $Nombre_A=$estudiante["nombre"];
                                                    $Apellido_A=$estudiante["apellido"];
                                                    $Curso_A=$estudiante["curso"];
                                                    $DNI_A=$estudiante["dni"];
                                                    $Maternal=$estudiante["maternal"];
                                                    if($Maternal==1)
                                                        {
                                                            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT av.Id, av.ID_Responsable
                                                            FROM alumnos_vinculados av
                                                            WHERE av.Id_Alumno='{$ID_Al}' and av.B=0
                                                    ");

                                                        $Cant_Vinculos=count($check_existencia);
                                                        if($Cant_Vinculos>=1)
                                                            {
                                                                $ID_Respo=$check_existencia[0]->ID_Responsable;
                                                                $lista_responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                    SELECT r.Nombre, r.Apellido
                                                                    FROM responsabes_economicos r
                                                                    WHERE r.Id='{$ID_Respo}'
                                                                    ");
                                                                $Cant_Respo=count($lista_responsable);
                                                                if($Cant_Respo>=1)
                                                                    {
                                                                        $Nombre_Respo=trim(utf8_decode($lista_responsable[0]->Nombre));
                                                                        $Apellido_Respo=trim(utf8_decode($lista_responsable[0]->Apellido));
                                                                        $Respo=$Apellido_Respo.', '.$Nombre_Respo;
                                                                    }
                                                                else
                                                                    {
                                                                        $Respo='NC';
                                                                    }

                                                            }
                                                        else
                                                            {
                                                                $Respo='';
                                                            }



                                                        $resultado[$j] = array(
                                                                'id'=> $ID_Al,
                                                                'apellido'=> $Apellido_A,
                                                                'nombre'=> $Nombre_A,
                                                                'dni' => $DNI_A,
                                                                'curso' => $Curso_A,
                                                                'nivel' => $Nivel,
                                                                'responsable' => $Respo
                                                                );

                                                        $j=$j+1;
                                                        }

                                                }

                                        }
                                }
                        }
                }


        //$resultado=$Array_alumnos;
        return $resultado;

        }


        public function ver_cc($id,$id_item,$id_tipo)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cc.Id, cc.Fecha, cc.Id_Comprobante, cc.Importe, cc.Descripcion, cc.Id_Tipo_Comprobante, cct.Tipo, cct.Clase, cct.Detalle
                            FROM cuenta_corriente cc
                            INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                            WHERE cc.B=0 and cc.Id_Responsable={$id_item}
                            ORDER by cc.Id

                      ");
          $Saldo=0;
          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Tipo_Comprobante=$listado[$j]->Id_Tipo_Comprobante;
                   $Importe=$listado[$j]->Importe;
                   $Clase=trim(utf8_decode($listado[$j]->Clase));
                   $ID_Comprobante=$listado[$j]->Id_Comprobante;

                   if($Clase==0)
                        {
                            $Saldo=$Importe;
                        }
                    if($Clase==1)
                        {
                            $Saldo=$Saldo+$Importe;
                        }
                    if($Clase==2)
                        {
                            $Saldo=$Saldo-$Importe;
                        }

                    if($ID_Comprobante>=1)
                        {
                            $comprobante = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT co.Id, co.Identificacion, co.Enlace, co.Detalle
                            FROM comprobantes co
                            WHERE co.B=0 and co.Id={$ID_Comprobante}
                            ORDER by co.Id

                                 ");
                            //$Identificacion_Comprobante=$comprobante[0]->Identificacion;
                            $ID_Comprobante=$listado[$j]->Id;
                            $Tipo_Comprobante=$listado[$j]->Tipo;
                            $Identificacion_Comprobante=$Tipo_Comprobante.'-'.$ID_Comprobante;
                            $Enlace_Comprobante=$comprobante[0]->Enlace;
                            $Observaciones_Comprobante=$comprobante[0]->Detalle;

                        }
                    else
                        {
                            $Identificacion_Comprobante='';
                            $Enlace_Comprobante='';
                            $Observaciones_Comprobante='';
                        }

                   $resultado[$j] = array(
                                                'id' => $ID_Comprobante,
                                                'fecha'=> $listado[$j]->Fecha,
                                                'comprobante'=> $Identificacion_Comprobante,
                                                'tipo'=> $listado[$j]->Tipo,
                                                'concepto'=> trim(utf8_decode($listado[$j]->Descripcion,)),
                                                'importe' => $Importe,
                                                'salddo' => $Saldo,
                                                'enlace' => $Enlace_Comprobante,
                                                'observaciones' => $Observaciones_Comprobante
                                          );

                }
          return $resultado;
        }

        public function saldo($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cc.Importe, cct.Clase
                            FROM cuenta_corriente cc
                            INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                            WHERE cc.B=0 and cc.Id_Responsable={$id_item}
                            ORDER by cc.Id

                      ");
          $Saldo=0;
          $Total_Movimientos=count($listado);
          if($Total_Movimientos>=1)
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   $Importe=$listado[$j]->Importe;
                   $Clase=$listado[$j]->Clase;

                   if($Clase==0)
                        {
                            $Saldo=$Importe;
                        }
                    if($Clase==1)
                        {
                            $Saldo=$Saldo+$Importe;
                        }
                    if($Clase==2)
                        {
                            $Saldo=$Saldo-$Importe;
                        }

                }
            }
          else
            {
                $Saldo='0.00';
                $Total_Movimientos=0;
            }
            $resultado[0] = array(
                'saldo' => $Saldo,
                'total_movimientos'=> $Total_Movimientos
                    );

        return $resultado;

        }

        public function lista_movimientos_cuenta($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cct.ID, cct.Tipo, cct.Clase, cct.Detalle
                            FROM cuenta_corriente_tipos cct
                            WHERE cct.Manual=1
                            ORDER by cct.Detalle

                      ");
    
            for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Item=$listado[$j]->ID;
                   $Detalle=$listado[$j]->Detalle;
                   $Tipo=$listado[$j]->Tipo;
                   
                   $Concepto=$Tipo.'-'.$Detalle;

            $resultado[$j] = array(
                'id_movimiento' => $ID_Item,
                'movimiento'=> $Concepto
                    );
                }

        return $resultado;

        }

        public function cargo_gen($id, $id_item, $id_movimiento, $descripcion, $importe, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          
          $id_institucion=$id;
          $id_estu=0;
          $ID_Comprobante=0;


          $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe)
                            VALUES ({$id_item},{$id_estu},'{$FechaActual}',$id_movimiento,'{$descripcion}',{$ID_Comprobante},{$importe})
                            ");
                            
        $resultado='El cargo fue agregado con éxito a la cuenta';
          
        return $resultado;

        }

        public function interes_gen($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();



          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT rp.Id,rp.Nombre,rp.Apellido,rp.DNI,rp.Estado
                          FROM responsabes_economicos rp
                          WHERE rp.B=0
                          ORDER BY rp.Apellido,rp.Nombre
                      ");
        $Cuenta=0;
          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Responsable = $listado[$j]->Id;
                   $ID_Estado = $listado[$j]->Estado;
                   if($ID_Estado==1)
                        {
                            $Estado='Activo';
                        }
                    if($ID_Estado==2)
                        {
                            $Estado='Suspendido';
                        }
                    if($ID_Estado==3)
                        {
                            $Estado='Archivado';
                        }
                   
                    $Saldo =$this->saldo($id_institucion,$ID_Responsable);
                    $Saldito = $Saldo[0]['saldo'];


                   


                    //$ID_Estado = $listado[$j]->Estado;



                    
                    //$Saldo = saldo($id_institucion,$ID_Responsable);
                    //$Saldo = $Saldo["Saldo"];
                    if($Saldito>0)
                        {
                            
                            $Porcentaje=0.0591;
                            $Interes=$Porcentaje*$Saldito;
                            $Interes=round($Interes);
                            $id_estu=0;
                            $Detalle_Factura='Recargo por Mora';
                            $Fecha_Recargo='2023-04-03';
                            $ID_Comprobante=0;
                            //GENERO MOVIMIENTO EN CUENTA
                            $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe)
                            VALUES ({$ID_Responsable},{$id_estu},'{$Fecha_Recargo}',7,'{$Detalle_Factura}',{$ID_Comprobante},{$Interes})
                            ");
                            
                            $resultado[$j] = array(
                                'id' => $ID_Responsable,
                                'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                'dni'=> $listado[$j]->DNI,
                                'estado'=> $Estado,
                                'saldo'=> $Saldito,
                                'interes'=> $Interes
                            );
                        }

                    
                }
          return $resultado;
        }

        public function estadistica_vinculacion($id)
        {

            $id_institucion=$id;

            //CONSULTO LOS NIVELES
            $headers = [
                'Content-Type: application/json',
            ];
            $curl = curl_init();
            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/niveles_educativos/'.$id_institucion;
            curl_setopt($curl, CURLOPT_URL,  $ruta_api);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); /**Permitimos recibir respuesta*/
            curl_setopt($curl, CURLOPT_HTTPGET,true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, false);
            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); /** Archivo donde guardamos datos de sesion */
            $data0 = curl_exec($curl); /** Ejecutamos petición*/
            curl_close($curl);

            //$data=dd($data);
            $data0 = json_decode($data0, true);
            $datos_niveles0 = $data0['data'];
            //EJEMPLO
            $j=0;
            $Total_Estudiantes=0;
            $Total_Vinculaciones=0;
            foreach($datos_niveles0 as $niveles0)
                {
                    $Array_niveles=$niveles0["niveles"];
                    foreach($Array_niveles as $niveles)
                        {
                            $ID_Nivel=$niveles["id"];
                            $Nivel=$niveles["nivel"];
                            $curl2 = curl_init();
                            $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiantes/'.$id_institucion.'?id='.$ID_Nivel;
                            curl_setopt($curl2, CURLOPT_URL,  $ruta_api);
                            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl2, CURLOPT_HTTPGET,true);
                            curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($curl2, CURLOPT_POST, false);
                            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
                            $data2 = curl_exec($curl2);
                            curl_close($curl2);
                            //$data=dd($data);


                            if($ID_Nivel<=3)
                                {
                                    $data2 = json_decode($data2, true);
                                    $datos_alumnos0 = $data2['data'];
                                    foreach($datos_alumnos0 as $alumnos0)
                                        {
                                            $Array_alumnos=$alumnos0["alumnos"];
                                            foreach($Array_alumnos as $estudiante)
                                                {
                                                    $ID_Al=$estudiante["id"];
                                                    $Maternal=$estudiante["maternal"];
                                                    if($Maternal==0)
                                                        {
                                                            $Total_Estudiantes++;
                                                            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                SELECT Id
                                                                                FROM alumnos_vinculados
                                                                                WHERE Id_Alumno='{$ID_Al}' and B=0
                                                                        ");

                                                            $Cant_Vinculos=count($check_existencia);
                                                            if($Cant_Vinculos>=1)
                                                                {
                                                                    $Total_Vinculaciones++;
                                                                }

                                                            $j=$j+1;
                                                        }

                                                }

                                        }
                                }
                            if($ID_Nivel==5)
                                {
                                    $data2 = json_decode($data2, true);
                                    $datos_alumnos0 = $data2['data'];
                                    foreach($datos_alumnos0 as $alumnos0)
                                        {
                                            $Array_alumnos=$alumnos0["alumnos"];
                                            foreach($Array_alumnos as $estudiante)
                                                {
                                                    $ID_Al=$estudiante["id"];
                                                    $Maternal=$estudiante["maternal"];
                                                    if($Maternal==1)
                                                        {
                                                            $Total_Estudiantes++;
                                                            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                                SELECT Id
                                                                                FROM alumnos_vinculados
                                                                                WHERE Id_Alumno='{$ID_Al}' and B=0
                                                                        ");

                                                            $Cant_Vinculos=count($check_existencia);
                                                            if($Cant_Vinculos>=1)
                                                                {
                                                                    $Total_Vinculaciones++;
                                                                }

                                                            $j=$j+1;
                                                        }

                                                }

                                        }
                                }

                        }
                }
                $Coef=$Total_Vinculaciones/$Total_Estudiantes;
                $Porc_Vinculacion=$Coef*100;
                $Porc_Vinculacion=round($Porc_Vinculacion, 2);
                $resultado[0] = array(
                    'total_estudiantes'=> $Total_Estudiantes,
                    'total_vinculaciones'=> $Total_Vinculaciones,
                    'porcentaje'=> $Porc_Vinculacion
                    );

        //$resultado=$Array_alumnos;
        return $resultado;

        }
        public function cambio_estado_facturacion($id, $id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $consulta_estado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT Facturado
                            FROM movimientos_caja
                            WHERE ID={$id_item}
                    ");

        $Estado_Anterior=$consulta_estado[0]->Facturado;
        if($Estado_Anterior==0)
            {
                $Facturado=1;
                $resultado='La operación de caja ha sido transformada en facturable';
            }
        else
            {
                $Facturado=0;
                $resultado='La operación de caja ha sido transformada en NO facturable';
            } 
          //ACTUALIZO ESTADO DE FACTURACION
          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                        UPDATE movimientos_caja
                        SET Facturado='{$Facturado}'
                        WHERE ID={$id_item}
                    ");
                    
          return $resultado;
        }


}
