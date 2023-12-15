<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;
use App\Services\ResponsablesService;

class LotesRepository
{

    private $Alumno;
    protected $connection = 'mysql2';
    protected $responsablesService;

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService, ResponsablesService $responsablesService)
        {
            $this->Alumno = $Alumno;
            $this->dataBaseService = $dataBaseService;
            $this->responsablesService = $responsablesService;
            
        }
    
   

      
        
        
    public function agregar_p1($id,$nombre,$id_empresa,$tipo_facturacion,$id_campana,$id_periodo,$vencimiento1,$vencimiento2,$vencimiento3,$id_usuario,$interes)
    {
      $habilitado=0;
      try {
               date_default_timezone_set('America/Argentina/Buenos_Aires');
               $FechaActual=date("Y-m-d");
               $HoraActual=date("H:i:s");
               $id_institucion=$id;
               $nombre=utf8_encode($nombre);
               $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT Id
                                  FROM facturacion
                                  WHERE Nombre='{$nombre}' and Id_Campana={$id_campana} and Id_Periodo={$id_periodo} and B=0
                                    ");
              $ctrl_e=count($control_existencia);        
              if(empty($ctrl_e))
                {
                  $habilitado=0;
                }
              else
                {
                  $habilitado++;
                }
              if(empty($habilitado))
                {
                    if(empty($vencimiento2))
                        {
                            $vencimiento2=$vencimiento1;
                        }
                    if(empty($vencimiento3))
                        {
                            $vencimiento3=$vencimiento1;
                        }
                    
                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO facturacion
                                    (Nombre,Id_Empresa,Tipo_Facturacion,Id_Campana,Id_Periodo,Vencimiento_1,Vencimiento_2,Vencimiento_3,Interes,ID_Usuario,Estado)
                                    VALUES
                                    ('{$nombre}',{$id_empresa},{$tipo_facturacion},{$id_campana},{$id_periodo},'{$vencimiento1}','{$vencimiento2}','{$vencimiento3}','{$interes}','{$id_usuario}',1)
                           ");
                    $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT fac.Id
                                    FROM facturacion fac
                                    WHERE fac.Id_Campana='{$id_campana}' and fac.Id_Periodo='{$id_periodo}' and fac.Id_Empresa='{$id_empresa}'
                           ");
                    $id_lote = $verifico_insercion[0]->Id; 
                    $ok=$id_lote; 
                    return $ok;  
                }
            else
                {
                    //$error='Atención: El Lote que intenta generar, ya se encuentra activo en el sistema';
                    //$error=utf8_encode($error);
                    $error='error';
                    return $error;
                }
                    

              
              

          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar_p1($id,$id_item,$nombre,$id_empresa,$tipo_facturacion,$id_campana,$id_periodo,$vencimiento1,$vencimiento2,$vencimiento3,$id_usuario,$interes)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $nombre=utf8_encode($nombre);

            $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item} and Estado<=3 and Estado>=1
                    ");
            
            $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id
                                    FROM facturacion
                                    WHERE Id_Campana='{$id_campana}' and Id_Periodo='{$id_periodo}' and B=0 and Id<>'{$id_item}'

                                        ");
                    $ctrl_e=count($control_existencia);
                    if(empty($ctrl_e))
                        {
                        $habilitado=0;
                        }
                    else
                        {
                        $habilitado=1;
                        }
                    
                    if(empty($habilitado))
                        {
                                    if(empty($vencimiento2))
                                        {
                                            $vencimiento2=$vencimiento1;
                                        }
                                    if(empty($vencimiento3))
                                        {
                                            $vencimiento3=$vencimiento1;
                                        }
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE facturacion
                                    SET Nombre='{$nombre}',Id_Empresa='{$id_empresa}',Tipo_Facturacion='{$tipo_facturacion}',Id_Campana='{$id_campana}',Id_Periodo='{$id_periodo}',Vencimiento_1='{$vencimiento1}',Vencimiento_2='{$vencimiento2}',Vencimiento_3='{$vencimiento3}',Interes='{$interes}',Id_Usuario='{$id_usuario}',Estado=1
                                    WHERE Id={$id_item}
                                        ");

                                    $ok='Los datos del Lote de Facturación han sido modificados con éxito';
                                    return $ok;
                            
                        }
                    else
                        {
                            //$ok='Atención: Los datos que intenta modificar son coincidentes con otro lote existente';
                            $ok='error0';
                            return $ok;
                        }
                }




            
          
    }   
    
    public function periodos_libres($id, $id_item)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $listado1 = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cf.Id_Periodo
                          FROM campanas_facturacion cf
                          WHERE cf.B=0 and cf.Id={$id_item} 
                      ");
            $ID_Periodo_zero=$listado1[0]->Id_Periodo;
            $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pd.Id, pd.Id_Mes, pd.Vencimiento
                          FROM periodos_detalle pd
                          WHERE pd.B=0 and pd.Id_Periodo={$ID_Periodo_zero}
                          ORDER BY pd.Id_Mes
                      ");
          $control_existencia=count($listado);
          if(empty($control_existencia))
            {
                $resultado=array();
            }
        else
            {
                $code=0;
                for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Periodo = $listado[$j]->Id;
                   $ID_Mes = $listado[$j]->Id_Mes;
                   $Vencimiento = $listado[$j]->Vencimiento;
                   $control = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id_Campana={$id_item} and fac.Id_Periodo={$ID_Periodo}
                          
                      ");
                    $control_existencia=count($control);
                    if(empty($control_existencia))
                        {
                            $resultado[$code] = array(
                                'id' => $ID_Periodo,
                                'id_mes'=> $ID_Mes,
                                'vencimiento'=> $Vencimiento
                            );
                            $code++;
                        }
                    
                }
          
            }
          return $resultado;
              
            
    }   
    public function periodos($id, $id_item)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $resultado=array();
            $listado1 = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cf.Id_Periodo
                          FROM campanas_facturacion cf
                          WHERE cf.B=0 and cf.Id={$id_item} 
                      ");
            $ID_Periodo_zero=$listado1[0]->Id_Periodo;
            $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pd.Id, pd.Id_Mes, pd.Vencimiento
                          FROM periodos_detalle pd
                          WHERE pd.B=0 and pd.Id_Periodo={$ID_Periodo_zero}
                          ORDER BY pd.Id_Mes
                      ");
          $control_existencia=count($listado);
          if(empty($control_existencia))
            {
                $resultado=array();
            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Periodo = $listado[$j]->Id;
                   $ID_Mes = $listado[$j]->Id_Mes;
                   $Vencimiento = $listado[$j]->Vencimiento;
                   
                            $resultado[$j] = array(
                                'id' => $ID_Periodo,
                                'id_mes'=> $ID_Mes,
                                'vencimiento'=> $Vencimiento
                            );

                }
          
            }
          return $resultado;
              
            
    }   
    
    public function borrar($id,$id_item,$id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $check_status = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id
                                    FROM facturacion
                                    WHERE Id='{$id_item}' and B=0 and Estado>=5

                                        ");
            
           $ctrol_permisos = count($check_status);
           if(empty($ctrol_permisos))
            {
                //SE PUEDE BORRAR
                $borrado1 = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE facturacion_destinatarios
                          SET B=1
                          WHERE Id_Lote={$id_item}
                      ");
                      $borrado2 = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE facturacion_conceptos
                          SET B=1
                          WHERE Id_Lote={$id_item}
                      ");
                      $borrado3 = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE facturacion
                          SET B=1, Fecha_B='{$FechaActual}' and ID_B='{$id_usuario}'
                          WHERE Id={$id_item}
                      ");
                      $ok='El Lote de Facturación ha sido eliminado con éxito.';
                      return $ok;

            }
        else
            {
                //NO SE PUEDE BORRAR POR ESTAR CERRADO
                //$ok='Atención: El Lote de Facturación no se puede eliminar por encontrarse cerrado y/o publicado. Contáctese con Mesa de ayuda.';
                $ok='error';
                return $ok;
            }
      
        }
        
    public function ver_p1($id,$id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.*
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item}
                          
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                    $resultado[$j] = array(
                                              'id' => $listado[$j]->Id,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'id_empresa'=> $listado[$j]->Id_Empresa,
                                              'id_tipo_facturacion'=> $listado[$j]->Tipo_Facturacion,
                                              'id_campana'=> $listado[$j]->Id_Campana,
                                              'id_periodo'=> $listado[$j]->Id_Periodo,
                                              'vencimiento_1'=> $listado[$j]->Vencimiento_1,
                                              'vencimiento_2'=> $listado[$j]->Vencimiento_2,
                                              'vencimiento_3'=> $listado[$j]->Vencimiento_3,
                                              'interes'=> $listado[$j]->Interes,
                                              'id_estado'=> $listado[$j]->Estado,
                                          );
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
                          SELECT fac.* 
                          FROM facturacion fac
                          INNER JOIN empresas emp ON fac.Id_Empresa=emp.ID
                          WHERE fac.B=0
                          ORDER BY fac.Id desc
                          
                      ");
        $control_existencia=count($listado);
        if(empty($control_existencia))
            {
                $resultado=array();
            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                    $ID_Lote=$listado[$j]->Id;
                    $status=$listado[$j]->Estado;
                    $id_periodo=$listado[$j]->Id_Periodo;
                    $id_campana=$listado[$j]->Id_Campana;
                    $id_tipo_facturacion=$listado[$j]->Tipo_Facturacion;
                    if($status==1)
                        {
                            $estado='En Producción 1/3';
                        }
                    if($status==2)
                        {
                            $estado='En Producción 2/3';
                        }
                    if($status==3)
                        {
                            $estado='En Producción 3/3';
                        }
                    if($status==4)
                        {
                            $estado='Finalizado';
                        }
                    if($status==5)
                        {
                            $estado='Publicado';
                        }
                    if($status>=4)
                        {
                            $Monto=$listado[$j]->Monto_Total;
                            $Coeficiente_Cobranza=0;
                            //CALCULAR COEFICIENTE
                            $listado_pagos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT c.Id, ci.Importe
                                FROM comprobantes c
                                INNER JOIN comprobantes_imputaciones ci ON c.Id=ci.ID_Comprobante
                                WHERE c.B=0 and c.Id_Lote={$ID_Lote} and c.Cancelado>=1 and ci.B=0
                                ORDER BY c.Id
                                
                            ");
                            $Monto_Cobrado=0;
                            for ($ji=0; $ji < count($listado_pagos); $ji++)
                                {
                                    $Importe_Cobrado=$listado_pagos[$ji]->Importe;
                                    $Monto_Cobrado=$Monto_Cobrado+$Importe_Cobrado;

                                }

                            $Coeficiente_Cobranza=round(($Monto_Cobrado/$Monto),2);    
                            //$Coeficiente_Cobranza=0.75;
                            //*************************                            

                        }
                    else
                        {
                            $Monto=0;
                            $Coeficiente_Cobranza=0;
                        }
                    if($id_tipo_facturacion==0)
                        {
                            $tipo_facturacion='Sin emisión de Factura';
                        }
                    if($id_tipo_facturacion==1)
                        {
                            $tipo_facturacion='Emisión Completa de Facturas';
                        }
                    if($id_tipo_facturacion==2)
                        {
                            $tipo_facturacion='Emisión de Facturas al percibir el pago';
                        }
                        if($id_tipo_facturacion==3)
                        {
                            $tipo_facturacion='Emisión Selectiva preconfigurada';
                        }
                    $tipo_facturacion=trim(utf8_decode($tipo_facturacion));
                    $c_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT pd.Id_Mes, pe.Nombre
                        FROM periodos_detalle pd
                        INNER JOIN periodos pe ON pd.Id_Periodo=pe.ID
                        WHERE pd.Id={$id_periodo} and pe.B=0
                                               
                    ");
                    $Nombre_Periodo=$c_periodo[0]->Nombre;
                    $ID_Mes_Periodo=$c_periodo[0]->Id_Mes;
                    if($ID_Mes_Periodo==1){ $Mes='Enero';}
                    if($ID_Mes_Periodo==2){ $Mes='Febrero';}
                    if($ID_Mes_Periodo==3){ $Mes='Marzo';}
                    if($ID_Mes_Periodo==4){ $Mes='Abril';}
                    if($ID_Mes_Periodo==5){ $Mes='Mayo';}
                    if($ID_Mes_Periodo==6){ $Mes='Junio';}
                    if($ID_Mes_Periodo==7){ $Mes='Julio';}
                    if($ID_Mes_Periodo==8){ $Mes='Agosto';}
                    if($ID_Mes_Periodo==9){ $Mes='Septiembre';}
                    if($ID_Mes_Periodo==10){ $Mes='Octubre';}
                    if($ID_Mes_Periodo==11){ $Mes='Noviembre';}
                    if($ID_Mes_Periodo==12){ $Mes='Diciembre';}
                    $Periodo_Completo=$Mes. ' - '.$Nombre_Periodo;

                    $c_campana = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT cf.Nombre
                        FROM campanas_facturacion cf
                        WHERE cf.Id={$id_campana}
                                               
                    ");
                    $Campana=$c_campana[0]->Nombre;
                    $resultado[$j] = array(
                                              'id' => $ID_Lote,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'id_empresa'=> $listado[$j]->Id_Empresa,
                                              //'empresa'=> trim(utf8_decode($listado[$j]->Empresa)),
                                              
                                              'id_tipo_facturacion'=> $id_tipo_facturacion,
                                              'tipo_facturacion'=> trim(utf8_decode($tipo_facturacion)),
                                              'id_campana'=> $id_campana,
                                              'campana'=> trim(utf8_decode($Campana)),
                                              
                                              'id_periodo'=> $listado[$j]->Id_Periodo,
                                              'periodo'=> trim(utf8_decode($Periodo_Completo)),
                                              'vencimiento_1'=> $listado[$j]->Vencimiento_1,
                                              'vencimiento_2'=> $listado[$j]->Vencimiento_2,
                                              'vencimiento_3'=> $listado[$j]->Vencimiento_3,
                                              'id_estado'=> $status,
                                              'estado'=> trim(utf8_encode($estado)),
                                              'monto'=> $Monto,
                                              'cobranza'=> $Coeficiente_Cobranza,
                                                                                            

                                          );
                }
            }
          
          return $resultado;
        }
    
        public function ver_p2($id,$id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $Consulta_Campana = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT f.Id_Campana
                          FROM facturacion f
                          WHERE f.Id={$id_item}
                          
                      ");
          $ID_Campana=$Consulta_Campana[0]->Id_Campana;

          $listado_completo = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT cc.Id, cc.Id_Conceptos, con.Nombre, con.Importe
            FROM campanas_conceptos cc
            INNER JOIN conceptos con ON cc.Id_Conceptos=con.Id
            WHERE cc.B=0 and cc.Id_Campanas={$ID_Campana} and con.Plan=10
            ORDER BY con.Importe desc
            
            ");

            

            for ($j=0; $j < count($listado_completo); $j++)
                {
                   
                    $ID_CC=$listado_completo[$j]->Id;
                    $ID_Concepto=$listado_completo[$j]->Id_Conceptos;
                    $Concepto=$listado_completo[$j]->Nombre;
                    $Importe=$listado_completo[$j]->Importe;
                    $listado_check = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT fc.Id, fc.Importe
                        FROM facturacion_conceptos fc
                        WHERE fc.B=0 and fc.Id_Lote={$id_item} and fc.Id_Concepto={$ID_Concepto}
                        
                        ");
                    $Control_check=count($listado_check);
                    if(empty($Control_check))
                        {
                            $listado_check2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT fc.Id, fc.Importe
                                FROM facturacion_conceptos fc
                                WHERE fc.B=0 and fc.Id_Lote={$id_item}
                                
                                ");
                            $Control_check2=count($listado_check2);
                            if(empty($Control_check2))
                                {
                                    $Sel=1;
                                }
                            else
                                {
                                    $Sel=0;
                                }
                        }
                    else
                        {
                            $Sel=1;
                        }

                    $resultado[$j] = array(
                                              'id' => $ID_CC,
                                              'concepto'=> $Concepto,
                                              'id_concepto'=> $ID_Concepto,
                                              'importe'=> $Importe,
                                              'seleccion'=> $Sel

                                          );
                }

            
          return $resultado;
        }


        public function modificar_p2($id,$id_item,$conceptos)
        {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado = array();
            $Sumatoria=0;
        
            $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado<=3 and fac.Estado>=1
                    ");
            
            $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fc.Id
                            FROM facturacion_conceptos fc
                            WHERE fc.B=0 and fc.Id_Lote={$id_item}
                        ");
                      $control_e=count($control_existencia);
                      if($control_e>=1)
                        {
                            //BORRO TODO LO QUE HAY
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion_conceptos
                                SET B=1
                                WHERE Id_Lote= {$id_item}

                            ");
                        }



                    foreach($conceptos as $Linea)
                    {
                      $id_concepto=$Linea['id_concepto'];
                      $importe=$Linea['importe'];
                      $Sumatoria=$Sumatoria+$importe;
                      $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fc.Id
                            FROM facturacion_conceptos fc
                            WHERE fc.B=0 and fc.Id_Lote={$id_item} and Id_Concepto=$id_concepto
                        ");
                      $control_e=count($control_existencia);

                        if(empty($control_e))
                            {
                                $creo_lote_conceptos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO facturacion_conceptos
                                    (Id_Lote, Id_Concepto, Importe)
                                    VALUES
                                    ('{$id_item}','{$id_concepto}' ,'{$importe}')
                                    ");
                            }
                      
                    }

                    $actualizo_lote= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion
                                SET Estado=2
                                WHERE Id= {$id_item}

                            ");
                    $actualizo_total = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion
                                SET Monto='{$Sumatoria}'
                                WHERE Id= {$id_item}

                            ");
                    $ok='Los datos del Lote de Facturación han sido modificados con éxito';
                    return $ok;

                }


        }

        public function ver_p3($id,$id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $Consulta_Destinatarios = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT fd.Id
                FROM facturacion_destinatarios fd
                WHERE fd.Id_Lote={$id_item} and fd.B=0
                
            ");
           $Ctrl_Destinatarios=count($Consulta_Destinatarios);

          $Consulta_Campana = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT f.Id_Campana, f.Monto
                FROM facturacion f
                WHERE f.Id={$id_item}
                
            ");
            $ID_Campana=$Consulta_Campana[0]->Id_Campana;
            $Monto_Factura=$Consulta_Campana[0]->Monto;
            $Total_Facturado=0;

            $listado_completo = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT cac.Id_Curso
                FROM campanas_alcance_cursos cac
                WHERE cac.B=0 and cac.Id_Campana={$ID_Campana}
                ORDER BY cac.Id

                ");

            for ($j=0; $j < count($listado_completo); $j++)
            {
            
                $ID_Curso=$listado_completo[$j]->Id_Curso;
                $headers = [
                    'Content-Type: application/json',
                ];
                $curl = curl_init();
                $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiantes_curso/'.$id_institucion.'?id='.$ID_Curso;
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
                $datos_cursos0 = $data0['data'];
                $i=0;
                foreach($datos_cursos0 as $cursos0)
                    {
                        $ii=0;
                        $Array_cursos=$cursos0["alumnos"];
                        
                        foreach($Array_cursos as $cursos)
                            {
                                $ID_Alumno=$cursos["id"];
                                $Nombre_A=$cursos["nombre"];
                                $Apellido_A=$cursos["apellido"];
                                $Alumno_Completo=$Apellido_A.', '.$Nombre_A;
                                
                                if($ii==0)
                                    {
                                        $Curso_A=$cursos["curso"];
                                        $ID_Nivel_A=$cursos["id_nivel"];
                                        $resultado[$j]['curso'][$i] = array(
                                            'id_curso' => $ID_Curso,
                                            'curso'=> $Curso_A,
                                            'id_nivel'=> $ID_Nivel_A
                                        );
                                    }
                                //CONSULTO RESPONSABLE
                                $Consulta_Responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT re.Nombre, re.Apellido, re.Id
                                    FROM alumnos_vinculados av
                                    INNER JOIN responsabes_economicos re ON av.Id_Responsable=re.Id
                                    WHERE av.Id_Alumno={$ID_Alumno} and av.B=0 and re.B=0   
                                ");
                                $Ctrl_Responsable=count($Consulta_Responsable);
                                if(empty($Consulta_Responsable))
                                    {
                                        $Responsable='';
                                    }
                                else
                                    {
                                        $Nombre_R=$Consulta_Responsable[0]->Nombre;
                                        $Apellido_R=$Consulta_Responsable[0]->Apellido;
                                        $Responsable=$Apellido_R.', '.$Nombre_R;
                                        $ID_Responsable=$Consulta_Responsable[0]->Id;
                                        $Consulta_Plan = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT remp.Tipo_Medio
                                            FROM responsables_economicos_mp remp
                                            WHERE remp.ID_Responsable={$ID_Responsable} and remp.B=0   
                                        ");
                                        $Ctrl_Plan=count($Consulta_Plan);
                                        if(empty($Consulta_Plan))
                                            {
                                                $Plan='10';
                                            }
                                        else
                                            {
                                                $Plan=$Consulta_Plan[0]->Tipo_Medio;
                                            }



                                    }
                                
                                $Valor_Cuota=$Monto_Factura;
                                if(empty($Ctrl_Destinatarios))
                                    {
                                        $Sel=1;
                                    }
                                else
                                    {
                                        $Consulta_Destinatarios2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT fd.Id
                                                FROM facturacion_destinatarios fd
                                                WHERE fd.Id_Lote={$id_item} and B=0 and Id_Alumno={$ID_Alumno}
                                                
                                            ");
                                        $Ctrl_Destinatarios2=count($Consulta_Destinatarios2);
                                        if(empty($Ctrl_Destinatarios2))
                                            {
                                                $Sel=0;
                                            }
                                        else
                                            {
                                                $Sel=1;
                                            }
                                    }
                                //AQUI SE DEBERIA INCLUIR LA REVISION DE LOS CONCEPTOS DE 10 CUOTAS
                                if($Plan==10)
                                    {

                                    }
                                else
                                    {
                                        $Descuento_Adicional=0;
                                        
                                                $Consulta_Planes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT con.Plan, con.Importe
                                                    FROM campanas_conceptos cc
                                                    INNER JOIN conceptos con ON cc.Id_Conceptos=con.Id
                                                    WHERE cc.Id_Campanas={$ID_Campana} and cc.B=0 and con.Plan={$Plan} and con.B=0
                                                ");
                                                $Ctrl_Planes=count($Consulta_Planes);
                                                if(empty($Ctrl_Planes))
                                                    {
                                                        $Descuento_Adicional=0;
                                                    }
                                                else
                                                    {
                                                        $Importe_Plan=$Consulta_Planes[0]->Importe;
                                                        $Descuento_Adicional=$Importe_Plan;
                                                    }
                                            
                                        $Valor_Cuota=$Valor_Cuota+$Descuento_Adicional;
                                    }
                                //CONSULTO BENEFICIOS
                                $Beneficio=0;
                                $Consulta_Beneficio = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ba.Id, re.Tipo_Descuento, re.Descuento, re.Aplica_Total
                                    FROM beneficios_asignaciones ba
                                    INNER JOIN beneficios re ON ba.Id_Beneficio=re.Id
                                    WHERE ba.Id_Alumno={$ID_Alumno} and ba.B=0 and ba.Fecha_Vencimiento>='{$FechaActual}'
                                ");
                                $Ctrol_Beneficio=count($Consulta_Beneficio);
                                if(empty($Ctrol_Beneficio))
                                    {
                                        $Beneficio=0;
                                    }
                                else
                                    {
                                        $Descuentos_Aplicados=0;
                                        for ($y=0; $y < count($Consulta_Beneficio); $y++)
                                            {
                                                $ID_Beneficio=$Consulta_Beneficio[$y]->Id;
                                                $Tipo_Descuento=$Consulta_Beneficio[$y]->Tipo_Descuento;
                                                $Descuento=$Consulta_Beneficio[$y]->Descuento;
                                                $Aplica_Total=$Consulta_Beneficio[$y]->Aplica_Total;
                                                if($Aplica_Total==1)
                                                    {
                                                        //APLICA AL TOTAL
                                                        if($Tipo_Descuento==1)
                                                            {
                                                                //EL DESCUENTO ES PORCENTUAL, LO CACLCULO
                                                                $Coeficiente_Descuento=$Descuento/100;
                                                                $Monto_Descontado=$Coeficiente_Descuento*$Valor_Cuota;
                                                                $Monto_Descontado=round($Monto_Descontado,2);
                                                            }
                                                        else
                                                            {
                                                                $Monto_Descontado=round($Descuento,2);
                                                            }
                                                    }
                                                else
                                                    {
                                                        //NO APLICA AL TOTAL, RECORRO LOS ITEMS                                                        
                                                        $Consulta_Alcances = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT bda.Id_Concepto, con.Importe
                                                            FROM beneficios_detalle_aplicacion bda
                                                            INNER JOIN conceptos con ON bda.Id_Concepto=con.Id
                                                            WHERE bda.Id_Beneficio={$ID_Beneficio} and bda.B=0
                                                        ");
                                                        $Ctrol_Alcances_Beneficio=count($Consulta_Alcances);
                                                        if(empty($Ctrol_Alcances_Beneficio))
                                                            {
                                                                $Monto_Descontado=0;
                                                            }
                                                        else
                                                            {
                                                                $Acumula_Descuento=0;
                                                                for ($x=0; $x < count($Consulta_Alcances); $x++)
                                                                    {
                                                                        $ID_Concepto_Alcanzado=$Consulta_Alcances[$x]->Id_Concepto;
                                                                        $Importe_Concepto=$Consulta_Alcances[$x]->Importe;
                                                                        if($Tipo_Descuento==1)
                                                                            {
                                                                                //EL DESCUENTO ES PORCENTUAL, LO CACLCULO
                                                                                $Coeficiente_Descuento=$Descuento/100;
                                                                                $Monto_Descontado=$Coeficiente_Descuento*$Importe_Concepto;
                                                                                $Monto_Descontado=round($Monto_Descontado,2);
                                                                            }
                                                                        else
                                                                            {
                                                                                $Monto_Descontado=round($Descuento,2);
                                                                            }
                                                                        $Acumula_Descuento=$Acumula_Descuento+$Monto_Descontado;
                                                                    }
                                                                $Monto_Descontado=$Acumula_Descuento;
                                                                
                                                            }


                                                    }
                                                $Descuentos_Aplicados=$Descuentos_Aplicados+$Monto_Descontado;
                                            }
                                        $Beneficio=$Descuentos_Aplicados;
                                    }
                                $Valor_Cuota=$Valor_Cuota-$Beneficio;
                                $resultado[$j]['curso'][$i]['alumnos'][$ii] = array(
                                    'id_alumno' => $ID_Alumno,
                                    'nombre'=> $Alumno_Completo,
                                    'responsable'=> $Responsable,
                                    'beneficio'=> $Beneficio,
                                    'importe'=> $Valor_Cuota,
                                    'sel'=> $Sel
                                );

                                $ii++;
                            }
                        $i++;
                    }
            }

            return $resultado;

        }

        public function modificar_p3($id,$id_item,$alumnos)
        {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado = array();
            $Sumatoria=0;
        
            $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado<=3 and fac.Estado>=1
                    ");
            
            $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fd.Id
                            FROM facturacion_destinatarios fd
                            WHERE fd.B=0 and fd.Id_Lote={$id_item}
                        ");
                      $control_e=count($control_existencia);
                      if($control_e>=1)
                        {
                            //BORRO TODO LO QUE HAY
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion_destinatarios
                                SET B=1
                                WHERE Id_Lote= {$id_item}

                            ");
                        }



                    foreach($alumnos as $Linea)
                    {
                      $id_alumno=$Linea['id_alumno'];
                      $beneficio=$Linea['beneficio'];
                      $importe=$Linea['importe'];
                      $Sumatoria=$Sumatoria+$importe;
                      $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT fd.Id
                            FROM facturacion_destinatarios fd
                            WHERE fd.B=0 and fd.Id_Lote={$id_item} and Id_Alumno=$id_alumno
                        ");
                      $control_e=count($control_existencia);

                        if(empty($control_e))
                            {
                                $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO facturacion_destinatarios
                                    (Id_Lote, Id_Alumno, Beneficio, Importe)
                                    VALUES
                                    ('{$id_item}','{$id_alumno}','{$beneficio}' ,'{$importe}')
                                    ");
                            }
                        else
                            {
                                $actualizo_total = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion_destinatarios
                                SET Beneficio='{$beneficio}', Importe='{$importe}'
                                WHERE Id= {$id_item}

                            ");
                
                            }
                      
                    }

                    
                    $actualizo_total = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion
                                SET Monto_Total='{$Sumatoria}'
                                WHERE Id= {$id_item}

                            ");
                    
                    $ok='Los datos del Lote de Facturación han sido modificados con éxito';
                    return $ok;

                }


        } 
          
        public function confirmar($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado=2
                    ");
            
          $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    $actualizo_total = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion
                                SET Estado=3
                                WHERE Id= {$id_item}

                            ");
                    $ok='El Lote ha sido confirmado con éxito. Solo resta generarlo';
                    return $ok;
                }
          
        }

        public function generar($id,$id_item,$id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id, fac.Interes
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado=3
                    ");
            
          $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    //GENERAR MOVIMIENTOS EN CUENTAS CORRIENTES
                    $lista_destinatarios = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fd.Id_Alumno, fd.Beneficio, fd.Importe
                          FROM facturacion_destinatarios fd
                          WHERE fd.B=0 and fd.Id_Lote={$id_item}
                    ");
            
                    $control_destinatarios=count($lista_destinatarios);
                    if(empty($control_destinatarios))
                        {
                            //NO HAY DESTINATARIOS
                        }
                    else
                        {
                            $Aplica_Interes=$control_list [0]->Interes;
                            $Consulta_Campana = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT f.Id_Campana, f.Tipo_Facturacion, f.Nombre, f.Id_Periodo
                                FROM facturacion f
                                WHERE f.Id={$id_item}
                                
                            ");
                            $ID_Campana=$Consulta_Campana[0]->Id_Campana;
                            $Tipo_Facturacion=$Consulta_Campana[0]->Tipo_Facturacion;
                            $Detalle_Factura=$Consulta_Campana[0]->Nombre;
                            $ID_Periodo=$Consulta_Campana[0]->Id_Periodo;
                            $Consulta_Periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT pe.Nombre, pd.Mes_Completo, pe.Anio
                                FROM periodos_detalle pd
                                INNER JOIN periodos pe ON pd.Id_Periodo=pe.Id
                                WHERE pd.Id={$ID_Periodo}
                                
                            ");
                            $Periodo=$Consulta_Periodo[0]->Nombre;
                            $Mes_Periodo=$Consulta_Periodo[0]->Mes_Completo;
                            $Anio_Periodo=$Consulta_Periodo[0]->Anio;
                            $Detalle_Periodo=$Mes_Periodo.' '.$Anio_Periodo;
                            $Consulta_CampanaN = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cf.Nombre
                                FROM campanas_facturacion cf
                                WHERE cf.Id={$ID_Campana}
                                
                            ");
                            $Camapana=$Consulta_CampanaN[0]->Nombre;
                            $Identificacion=$Camapana.'-'.$Periodo;
                            


                            if($Tipo_Facturacion==0)
                                {
                                    $Facturable=0;
                                    $Factura_Ahora=0;
                                }
                            if($Tipo_Facturacion==1)
                                {
                                    $Facturable=1;
                                    $Factura_Ahora=1;
                                }
                            if($Tipo_Facturacion==2)
                                {
                                    $Facturable=1;
                                    $Factura_Ahora=0;
                                }
                            $Total_Lote=0;
                            for ($i=0; $i < count($lista_destinatarios); $i++)
                                {
                                    $ID_Estudiante=$lista_destinatarios[$i]->Id_Alumno;
                                    $Total_Beneficio=$lista_destinatarios[$i]->Beneficio;
                                    $Total_Cuota=$lista_destinatarios[$i]->Importe;
                                    $Consulta_Responsable = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT re.Id
                                        FROM alumnos_vinculados av
                                        INNER JOIN responsabes_economicos re ON av.Id_Responsable=re.Id
                                        WHERE av.Id_Alumno={$ID_Estudiante} and av.B=0 and re.B=0   
                                    ");
                                    $Ctrl_Responsable=count($Consulta_Responsable);
                                    if(empty($Ctrl_Responsable))
                                        {
                                            $Responsable='';
                                            //VER QUE SE HACE EN ESTOS CASOS
                                        }
                                    else
                                        {
                                            $Monto_Factura=0;
                                            $Estado_Cancelacion=0;
                                            $ID_Responsable=$Consulta_Responsable[0]->Id;
                                            $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                SELECT cc.Importe, cct.Clase
                                                                FROM cuenta_corriente cc
                                                                INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                                                                WHERE cc.B=0 and cc.Id_Responsable={$ID_Responsable}
                                                                ORDER by cc.Fecha, cc.Id

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
                                            
                                            $Saldo=round($Saldo,2);
                                            $Saldo_Cta_Cte=$Saldo;    

                                            
                                            //$Saldo_Cta_Cte = $Saldo_Cta_Cte_Array['data'];
                                            if($Saldo_Cta_Cte<0)
                                                {
                                                    $Saldo_Cta_Cte_P = $Saldo_Cta_Cte * (-1);
                                                    if($Saldo_Cta_Cte_P>=$Total_Cuota)
                                                        {
                                                            $Estado_Cancelacion=2;
                                                            $Importe_Imputado_por_Saldo=$Total_Cuota;
                                                        }
                                                    if($Saldo_Cta_Cte_P<$Total_Cuota)
                                                        {
                                                            $Estado_Cancelacion=1;
                                                            //$Importe_Imputado_por_Saldo=$Total_Cuota-$Saldo_Cta_Cte_P;
                                                            $Importe_Imputado_por_Saldo=$Saldo_Cta_Cte_P;
                                                        }
                                                }
                                            

                                            $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                INSERT INTO comprobantes
                                                (Id_Tipo,Id_Alumno,Id_Responsable,Identificacion,Importe,Detalle,Facturable,Id_Lote,Cancelado)
                                                VALUES (2,{$ID_Estudiante},{$ID_Responsable},'{$Identificacion}','{$Total_Cuota}','{$Detalle_Factura}','{$Facturable}','{$id_item}','{$Estado_Cancelacion}')
                                                ");
                                            $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT comp.Id
                                            FROM comprobantes comp
                                            WHERE comp.ID_Responsable={$ID_Responsable} and comp.B=0 and comp.Id_Alumno={$ID_Estudiante} and comp.Id_Lote={$id_item}
                                        ");
                                        if(($Estado_Cancelacion==1) or ($Estado_Cancelacion==2))
                                                {
                                                    //INSERTO IMPUTACION
                                                }
                                        
                                        
                                        if(empty($verifico_insercion))
                                            {
                                                //NO SE PUDO INSERTAR COMPROBANTE
                                            }
                                        else
                                            {
                                                $ID_Comprobante=$verifico_insercion[0]->Id;
                                            }

                                        $Consulta_Plan = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT remp.Tipo_Medio
                                                FROM responsables_economicos_mp remp
                                                WHERE remp.ID_Responsable={$ID_Responsable} and remp.B=0   
                                            ");
                                        $Ctrl_Plan=count($Consulta_Plan);
                                        if(empty($Consulta_Plan))
                                            {
                                                $Plan='10';
                                            }
                                        else
                                            {
                                                $Plan=$Consulta_Plan[0]->Tipo_Medio;
                                            }

                                        $listado_completo = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT fc.Importe, fc.Id_Concepto, con.Nombre
                                                FROM facturacion_conceptos fc
                                                INNER JOIN conceptos con ON fc.Id_Concepto=con.Id
                                                WHERE fc.B=0 and fc.Id_Lote={$id_item} and con.Plan=10
                                                ORDER BY fc.Importe desc
                                                
                                                ");
                                        for ($j=0; $j < count($listado_completo); $j++)
                                                {
                                                    $ID_Concepto=$listado_completo[$j]->Id_Concepto;
                                                    $Concepto=$listado_completo[$j]->Nombre;
                                                    $Concepto=$Concepto.' '.$Detalle_Periodo;

                                                    $Importe=$listado_completo[$j]->Importe;
                                                    $Monto_Factura=$Monto_Factura+$Importe;
                                                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                        INSERT INTO comprobantes_detalles
                                                        (Id_Comprobante,Id_Tipo_Concepto,Id_Concepto,Descripcion,Importe)
                                                        VALUES ({$ID_Comprobante},1,{$ID_Concepto},'{$Concepto}','{$Importe}')
                                                        ");
                                                    
                                                }
                                        //VERIFICACION DE PLANES
                                        if($Plan==10)
                                                {
            
                                                }
                                            else
                                                {
                                                    
                                                   $Importe_Plan=0;
                                                    $Consulta_Planes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                        SELECT con.Plan, con.Importe, con.Nombre, con.Id
                                                        FROM campanas_conceptos cc
                                                        INNER JOIN conceptos con ON cc.Id_Conceptos=con.Id
                                                        WHERE cc.Id_Campanas={$ID_Campana} and cc.B=0 and con.Plan={$Plan} and con.B=0
                                                        ");
                                                    $Ctrl_Planes=count($Consulta_Planes);
                                                    if(empty($Ctrl_Planes))
                                                        {
                                
                                                        }
                                                    else                                                               
                                                        {
                                                            $Importe_Plan=$Consulta_Planes[0]->Importe;
                                                            $Nombre_Plan=$Consulta_Planes[0]->Nombre;
                                                            $ID_Plan=$Consulta_Planes[0]->Id;  
                                                        }
                                                    if($Importe_Plan<>0)
                                                        {
                                                            if($Importe_Plan<0)
                                                                {
                                                                    $Importe_Plan = $Importe_Plan * (-1);
                                                                    
                                                                }
                                                            
                                                            $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                                INSERT INTO comprobantes_detalles
                                                                (Id_Comprobante,Id_Tipo_Concepto,Id_Concepto,Descripcion,Importe)
                                                                VALUES ({$ID_Comprobante},2,{$ID_Plan},'{$Nombre_Plan}','{$Importe_Plan}')
                                                                ");
                                                            $Monto_Factura=$Monto_Factura-$Importe_Plan;
                                                        }
                                                }
                                        //VERIFICACION DE DESCUENTOS
                                        //CONSULTO BENEFICIOS
                                        $Beneficio=0;
                                        $Consulta_Beneficio = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT ba.Id, re.Tipo_Descuento, re.Descuento, re.Aplica_Total, re.Nombre
                                            FROM beneficios_asignaciones ba
                                            INNER JOIN beneficios re ON ba.Id_Beneficio=re.Id
                                            WHERE ba.Id_Alumno={$ID_Estudiante} and ba.B=0 and ba.Fecha_Vencimiento>='{$FechaActual}'
                                        ");
                                        $Ctrol_Beneficio=count($Consulta_Beneficio);
                                        if(empty($Ctrol_Beneficio))
                                            {
                                                $Beneficio=0;
                                            }
                                        else
                                            {
                                                $Descuentos_Aplicados=0;
                                                for ($y=0; $y < count($Consulta_Beneficio); $y++)
                                                    {
                                                        $ID_Beneficio=$Consulta_Beneficio[$y]->Id;
                                                        $Tipo_Descuento=$Consulta_Beneficio[$y]->Tipo_Descuento;
                                                        $Descuento=$Consulta_Beneficio[$y]->Descuento;
                                                        $Aplica_Total=$Consulta_Beneficio[$y]->Aplica_Total;
                                                        $Nombre_Descuento=$Consulta_Beneficio[$y]->Nombre;
                                                        if($Aplica_Total==1)
                                                            {
                                                                //APLICA AL TOTAL
                                                                if($Tipo_Descuento==1)
                                                                    {
                                                                        //EL DESCUENTO ES PORCENTUAL, LO CACLCULO
                                                                        $Coeficiente_Descuento=$Descuento/100;
                                                                        $Monto_Descontado=$Coeficiente_Descuento*$Monto_Factura;
                                                                        $Monto_Descontado=round($Monto_Descontado,2);
                                                                    }
                                                                else
                                                                    {
                                                                        $Monto_Descontado=round($Descuento,2);
                                                                    }
                                                            }
                                                        else
                                                            {
                                                                //NO APLICA AL TOTAL, RECORRO LOS ITEMS                                                        
                                                                $Consulta_Alcances = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                    SELECT bda.Id_Concepto, con.Importe
                                                                    FROM beneficios_detalle_aplicacion bda
                                                                    INNER JOIN conceptos con ON bda.Id_Concepto=con.Id
                                                                    WHERE bda.Id_Beneficio={$ID_Beneficio} and bda.B=0
                                                                ");
                                                                $Ctrol_Alcances_Beneficio=count($Consulta_Alcances);
                                                                if(empty($Ctrol_Alcances_Beneficio))
                                                                    {
                                                                        $Monto_Descontado=0;
                                                                    }
                                                                else
                                                                    {
                                                                        $Acumula_Descuento=0;
                                                                        for ($x=0; $x < count($Consulta_Alcances); $x++)
                                                                            {
                                                                                $ID_Concepto_Alcanzado=$Consulta_Alcances[$x]->Id_Concepto;
                                                                                $Importe_Concepto=$Consulta_Alcances[$x]->Importe;
                                                                                if($Tipo_Descuento==1)
                                                                                    {
                                                                                        //EL DESCUENTO ES PORCENTUAL, LO CACLCULO
                                                                                        $Coeficiente_Descuento=$Descuento/100;
                                                                                        $Monto_Descontado=$Coeficiente_Descuento*$Importe_Concepto;
                                                                                        $Monto_Descontado=round($Monto_Descontado,2);
                                                                                    }
                                                                                else
                                                                                    {
                                                                                        $Monto_Descontado=round($Descuento,2);
                                                                                    }
                                                                                $Acumula_Descuento=$Acumula_Descuento+$Monto_Descontado;
                                                                            }
                                                                        $Monto_Descontado=$Acumula_Descuento;
                                                                        
                                                                    }


                                                            }
                                                        $Descuentos_Aplicados=$Descuentos_Aplicados+$Monto_Descontado;
                                                    }
                                                $Beneficio=$Descuentos_Aplicados;
                                                if($Beneficio<>0)
                                                    {
                                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                                INSERT INTO comprobantes_detalles
                                                                (Id_Comprobante,Id_Tipo_Concepto,Id_Concepto,Descripcion,Importe)
                                                                VALUES ({$ID_Comprobante},2,{$ID_Beneficio},'{$Nombre_Descuento}','{$Beneficio}')
                                                                ");
                                                    }
                                            }
                                        //GENERO FACTURA SI ES REQUERIDO
                                        if($Factura_Ahora==1)
                                            {

                                            }

                                        //GENERO MOVIMIENTO EN CUENTA
                                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                                INSERT INTO cuenta_corriente
                                                                (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe,Cancelado,ID_Periodo,Interes)
                                                                VALUES ({$ID_Responsable},{$ID_Estudiante},'{$FechaActual}',2,'{$Detalle_Factura}',{$ID_Comprobante},{$Total_Cuota},{$Estado_Cancelacion},{$ID_Periodo},{$Aplica_Interes})
                                                                ");
                                       
                                        //REVISO SI EL MOVIMIENTO DEBE SER CANCELADO POR SALDO A FAVOR
                                        if(($Estado_Cancelacion==1) or ($Estado_Cancelacion==2))
                                                {
                                                        $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                        SELECT cc.Id
                                                        FROM cuenta_corriente cc
                                                        WHERE cc.Id_Responsable={$ID_Responsable} and cc.ID_Alumno={$ID_Estudiante} and cc.Id_Comprobante={$ID_Comprobante} AND cc.B=0
                                                         ");
                            
                                                    if(empty($verifico_insercion))
                                                        {
                                                            //NO SE PUDO INSERTAR COMPROBANTE
                                                        }
                                                    else
                                                        {
                                                            $ID_Cta_Cte=$verifico_insercion[0]->Id;
                                                        }
                                                    //INSERTO IMPUTACION
                                                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                                INSERT INTO comprobantes_imputaciones
                                                                (Id_Comprobante,Importe,Cancela,ID_Cta_Cte)
                                                                VALUES ({$ID_Comprobante},{$Importe_Imputado_por_Saldo},{$Estado_Cancelacion},{$ID_Cta_Cte})
                                                                ");
                                                    //INSERTO EL MOVIMIENTO EN CUENTA
                                                    /*
                                                    if($Estado_Cancelacion==1)
                                                        {
                                                            $Descripcion_Cancelacion='CANCELACION PARCIAL DE COMP: '.$ID_Comprobante.' POR SALDO A FAVOR';

                                                        }
                                                    if($Estado_Cancelacion==2)
                                                        {
                                                            $Descripcion_Cancelacion='CANCELACION DE COMP: '.$ID_Comprobante.' POR SALDO A FAVOR';
                                                            
                                                        }
                                                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                        INSERT INTO cuenta_corriente
                                                        (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Importe)
                                                        VALUES ({$ID_Responsable},{$ID_Estudiante},'{$FechaActual}',4,'{$Descripcion_Cancelacion}',{$Importe_Imputado_por_Saldo})
                                                        ");    
                                                    */

                                                    
                                                }
                                        
                                        
                                        $Total_Lote=$Total_Lote+$Total_Cuota;



                                        }

                                    
                                }                             
                        }


                    //SI EL LOTE TIENE FACTURACIÓN COMPLETA, SE DEBERÍAN GENERAR LAS FACTURAS
                    
                    $actualizo_total = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion
                                SET Estado=4, Hora_Gen='{$HoraActual}', Fecha_Gen='{$FechaActual}', ID_Gen={$id_usuario}
                                WHERE Id= {$id_item}

                            ");
                    
                    $Consulta_Comprobantes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                                    SELECT comp.Id, comp.Id_Alumno, comp.Identificacion, comp.Importe, comp.ID_Factura, ct.Tipo, re.Nombre, re.Apellido, comp.Id_Tipo, comp.Id_Responsable
                                                                    FROM comprobantes comp
                                                                    INNER JOIN comprobantes_tipos ct ON comp.Id_Tipo=ct.Id
                                                                    INNER JOIN responsabes_economicos re ON comp.Id_Responsable=re.Id
                                                                    WHERE comp.Id_Lote={$id_item} and comp.B=0
                                                                    ORDER BY comp.Id
                                                                ");
                    $Ctrol_Comprobantes=count($Consulta_Comprobantes);
                    $resultado=array();
                    if(empty($Ctrol_Comprobantes))
                        {

                        }
                    else
                        {
                            $resultado[0]['estado']='1';
                            $resultado[0]['cantidad_comprobantes']=$Ctrol_Comprobantes;
                            $resultado[0]['monto_emitido']=$Total_Lote;
                            /*
                            for ($y=0; $y < count($Consulta_Comprobantes); $y++)
                                {
                                    $ID_Al=$Consulta_Comprobantes[$y]->Id_Alumno;
                                    $Apellido_Responsable=$Consulta_Comprobantes[$y]->Apellido;
                                    $Nombre_Responsable=$Consulta_Comprobantes[$y]->Nombre;
                                    $Responsable_Completo=$Apellido_Responsable.', '.$Nombre_Responsable;

                                    $headers = [
                                        'Content-Type: application/json',
                                    ];
                                    $curl = curl_init();
                                    $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Al;
                                    curl_setopt($curl, CURLOPT_URL,  $ruta_api); 
                                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($curl, CURLOPT_HTTPGET,true);
                                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($curl, CURLOPT_POST, false);
                                    //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' ); 
                                    $data0 = curl_exec($curl);
                                    curl_close($curl);
                        
                                    //$data=dd($data);
                                    $data0 = json_decode($data0, true);
                                    $datos_estudiante0 = $data0['data'];
                                    foreach($datos_estudiante0 as $niveles0)
                                        {
                                            $Nombre_Al=$niveles0["nombre"];
                                            $Apellido_Al=$niveles0["apellido"];
                                            $Curso_Al=$niveles0["curso"];
                                            $Alumno_Completo=$Apellido_Al.', '.$Nombre_Al;
                                        }
                                    




                                    $Consulta_CampanaN[0]->Nombre;
                                    $resultado[0]['detalle_comprobantes'][$y] = array(
                                        'id_comprobante' => $Consulta_Comprobantes[$y]->Id,
                                        'id_tipo_comprobante' => $Consulta_Comprobantes[$y]->Id_Tipo,
                                        'tipo_comprobante' => $Consulta_Comprobantes[$y]->Tipo,
                                        'id_responsable' => $Consulta_Comprobantes[$y]->Id_Responsable,
                                        'responsable' => $Responsable_Completo,
                                        'id_alumno' => $ID_Al,
                                        'alumno' => $Alumno_Completo,
                                        'detalle' => $Consulta_Comprobantes[$y]->Identificacion,
                                        'importe' => $Consulta_Comprobantes[$y]->Importe,
                                        'id_factura' => $Consulta_Comprobantes[$y]->ID_Factura
                                    );
                                }
                                */
                        }
                    $ok='El Lote ha sido generado con éxito';
                    return $resultado;
                }
          
        }

        public function publicar($id,$id_item,$id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Id
                          FROM facturacion fac
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado=4
                    ");
            
          $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    //GENERAR ENVIOS
                    $actualizo_envio = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes
                                SET Envio=0, Hora_Envio='{$HoraActual}', Fecha_Envio='{$FechaActual}', ID_Envio={$id_usuario}
                                WHERE Id_Lote= {$id_item} and B=0 and Envio=2
                            ");
                    
                    $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT comp.Id
                            FROM comprobantes comp
                            WHERE comp.B=0 and comp.Envio=0 and comp.Id_Lote={$id_item}
                      ");
              
                    $control=count($control_list);
  

                    
                    $actualizo_total = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE facturacion
                                SET Estado=5
                                WHERE Id= {$id_item}

                            ");
                    $ok='El Lote ha sido publicado con éxito. Se han generado '.$control.' notificaciones';
                    return $ok;
                }
          
        }

        public function ver($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Monto_Total, pd.Id_Periodo, pd.Id_Mes, fac.Nombre, fac.Vencimiento_1, fac.Id_Campana, pd.Id
                          FROM facturacion fac
                          INNER JOIN periodos_detalle pd ON fac.Id_Periodo=pd.Id
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado>=4
                    ");
            
          $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    for ($i=0; $i < count($control_list); $i++)
                               {
                                    $Lote=utf8_decode($control_list[$i]->Nombre);
                                    //$Monto_Total=$control_list[$i]->Monto_Total;
                                    $ID_Mes_Periodo=$control_list[$i]->Id_Mes;
                                    $ID_Periodo=$control_list[$i]->Id_Periodo;
                                    $ID_Periodo_Actual=$control_list[$i]->Id;
                                    $ID_Periodo_Anterior=$ID_Periodo_Actual-1;
                                    $ID_Campana=$control_list[$i]->Id_Campana;
                                    $Vencimiento=$control_list[$i]->Vencimiento_1;
                                    if($ID_Mes_Periodo==1){ $Mes='Enero';}
                                    if($ID_Mes_Periodo==2){ $Mes='Febrero';}
                                    if($ID_Mes_Periodo==3){ $Mes='Marzo';}
                                    if($ID_Mes_Periodo==4){ $Mes='Abril';}
                                    if($ID_Mes_Periodo==5){ $Mes='Mayo';}
                                    if($ID_Mes_Periodo==6){ $Mes='Junio';}
                                    if($ID_Mes_Periodo==7){ $Mes='Julio';}
                                    if($ID_Mes_Periodo==8){ $Mes='Agosto';}
                                    if($ID_Mes_Periodo==9){ $Mes='Septiembre';}
                                    if($ID_Mes_Periodo==10){ $Mes='Octubre';}
                                    if($ID_Mes_Periodo==11){ $Mes='Noviembre';}
                                    if($ID_Mes_Periodo==12){ $Mes='Diciembre';}
                                    $c_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT pe.Nombre
                                            FROM periodos pe
                                            WHERE pe.Id={$ID_Periodo} and pe.B=0
                                                                
                                        ");
                                    //$ID_Periodo_Anterior=$ID_Mes_Periodo-1;

                                    $Monto_Total=0;
                                    $listado_comprobantes_Lote = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT c.Importe
                                            FROM comprobantes c
                                            WHERE c.B=0 and c.Id_Lote={$id_item} and c.Id_Tipo=2
                                            ORDER BY c.Id
                                        ");
                                    for ($iz=0; $iz < count($listado_comprobantes_Lote); $iz++)
                                        {
                                            $Monto_Parcial=$listado_comprobantes_Lote[$iz]->Importe;
                                            $Monto_Total=$Monto_Total+$Monto_Parcial;
                                        }

                                    $Nombre_Periodo=$c_periodo[0]->Nombre;
                                    $Periodo_Completo=$Mes. ' - '.$Nombre_Periodo;

                                    //CALCULAR COEFICIENTE
                                    $listado_pagos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT c.Id, ci.Importe, mc.Fecha
                                            FROM comprobantes c
                                            INNER JOIN comprobantes_imputaciones ci ON c.Id=ci.ID_Comprobante
                                            INNER JOIN movimientos_caja mc ON ci.ID_Movimiento=mc.ID
                                            WHERE c.B=0 and c.Id_Lote={$id_item} and c.Cancelado>=1 and ci.B=0
                                            ORDER BY c.Id
                                            
                                        ");
                                    $Monto_Cobrado=0;
                                    $Monto_Cumplimiento=0;
                                    for ($ji=0; $ji < count($listado_pagos); $ji++)
                                        {
                                            $Importe_Cobrado=$listado_pagos[$ji]->Importe;
                                            $Fecha_Cobrado=$listado_pagos[$ji]->Fecha;
                                            $Fecha_Cobrado== date("Y-m-d", strtotime($Fecha_Cobrado));
                                            $Monto_Cobrado=$Monto_Cobrado+$Importe_Cobrado;
                                            if($Fecha_Cobrado<=$Vencimiento)
                                                {
                                                    $Monto_Cumplimiento=$Monto_Cumplimiento+$Importe_Cobrado;
                                                }

                                        }
                                    if($ID_Periodo_Anterior>=1)
                                        {
                                            $control_list2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT fac.Monto_Total
                                                    FROM facturacion fac
                                                    WHERE fac.B=0 and fac.Id_Campana={$ID_Campana} and fac.Id_Periodo={$ID_Periodo_Anterior}
                                                ");
                                            $Verificacion=count($control_list2);
                                            if(empty($Verificacion))
                                                {
                                                    $Monto_Anterior=0;
                                                }
                                            else
                                                {
                                                    $Monto_Anterior=$control_list2[0]->Monto_Total;
                                                }
                                        }
                                    else
                                        {
                                            $Monto_Anterior=0;
                                        }
                                     
                                        
                                    $Cobranza=$Monto_Cobrado;
                                    $Coeficiente_Cobranza=round(($Monto_Cobrado/$Monto_Total),2);
                                    $Porcentaje_Cobranza=round(($Coeficiente_Cobranza*100));

                                    $Coeficiente_Cumplimiento=round(($Monto_Cumplimiento/$Monto_Cobrado),2);
                                    $Porcentaje_Cumplimiento=round(($Coeficiente_Cumplimiento*100));

                                    if($Monto_Anterior==0)
                                        {
                                            $Inscripcion_Facturado='No hay datos Anteriores';
                                        }
                                    else
                                        {
                                            $Coeficiente_Anterior=round(($Monto_Total/$Monto_Anterior),2);
                                            if($Coeficiente_Anterior>1)
                                                {
                                                    $Coeficiente_Anterior=$Coeficiente_Anterior-1;
                                                    $Porcentaje_Anterior=round($Coeficiente_Anterior*100);
                                                    $Inscripcion_Facturado=$Porcentaje_Anterior.' % más que el mes anterior';
                                                }
                                            else
                                                {
                                                    if($Coeficiente_Anterior<1)
                                                        {
                                                            $Coeficiente_Anterior=1-$Coeficiente_Anterior;
                                                            $Porcentaje_Anterior=round($Coeficiente_Anterior*100);
                                                            $Inscripcion_Facturado=$Porcentaje_Anterior.'  % menos que el mes anterior';
                                                        }
                                                    else
                                                        {
                                                            $Inscripcion_Facturado='igual que el mes anterior';
                                                        }
                                                }
                                            
                                        }

                                    
                                    $Inscripcion_Cobranza=$Porcentaje_Cobranza.' % del Total';
                                    $Inscripcion_Vencimiento=$Porcentaje_Cumplimiento.' % Cumplimiento';

                                    //$Cobranza=0;
                                    $resultado[0] = array(
                                        'id_lote' => $id_item,
                                        'lote' => $Lote,
                                        'periodo' => $Periodo_Completo,
                                        'facturado' => $Monto_Total,
                                        'inscripcion_facturado' => $Inscripcion_Facturado,
                                        'cobranza' => $Cobranza,
                                        'inscripcion_cobranza' => $Inscripcion_Cobranza,
                                        'vencimiento' => $Vencimiento,
                                        'inscripcion_vencimiento' => $Inscripcion_Vencimiento
                                    );

                               }    
                          return $resultado;          
                    
                }
          
        }
    
        public function detalle($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

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

          $control_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT fac.Monto_Total, pd.Id_Periodo, pd.Id_Mes, fac.Nombre, fac.Vencimiento_1, fac.Id_Campana, pd.Id
                          FROM facturacion fac
                          INNER JOIN periodos_detalle pd ON fac.Id_Periodo=pd.Id
                          WHERE fac.B=0 and fac.Id={$id_item} and fac.Estado>=4
                    ");
            
          $control=count($control_list);

            if(empty($control))
                {
                    $ok='error';
                    return $ok;
                }
            else
                {
                    for ($i=0; $i < count($control_list); $i++)
                               {
                                    $Lote=$control_list[$i]->Nombre;
                                    //$Monto_Total=$control_list[$i]->Monto_Total;
                                    $ID_Mes_Periodo=$control_list[$i]->Id_Mes;
                                    $ID_Periodo=$control_list[$i]->Id_Periodo;
                                    $ID_Periodo_Actual=$control_list[$i]->Id;
                                    $ID_Periodo_Anterior=$ID_Periodo_Actual-1;
                                    $ID_Campana=$control_list[$i]->Id_Campana;
                                    $Vencimiento=$control_list[$i]->Vencimiento_1;
                                    if($ID_Mes_Periodo==1){ $Mes='Enero';}
                                    if($ID_Mes_Periodo==2){ $Mes='Febrero';}
                                    if($ID_Mes_Periodo==3){ $Mes='Marzo';}
                                    if($ID_Mes_Periodo==4){ $Mes='Abril';}
                                    if($ID_Mes_Periodo==5){ $Mes='Mayo';}
                                    if($ID_Mes_Periodo==6){ $Mes='Junio';}
                                    if($ID_Mes_Periodo==7){ $Mes='Julio';}
                                    if($ID_Mes_Periodo==8){ $Mes='Agosto';}
                                    if($ID_Mes_Periodo==9){ $Mes='Septiembre';}
                                    if($ID_Mes_Periodo==10){ $Mes='Octubre';}
                                    if($ID_Mes_Periodo==11){ $Mes='Noviembre';}
                                    if($ID_Mes_Periodo==12){ $Mes='Diciembre';}
                                    $c_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT pe.Nombre
                                            FROM periodos pe
                                            WHERE pe.Id={$ID_Periodo} and pe.B=0
                                                                
                                        ");

                                    $Nombre_Periodo=$c_periodo[0]->Nombre;
                                    $Periodo_Completo=$Mes. ' - '.$Nombre_Periodo;

                                    $Monto_Total=0;
                                    $listado_comprobantes_Lote = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT c.Importe
                                            FROM comprobantes c
                                            WHERE c.B=0 and c.Id_Lote={$id_item} and c.Id_Tipo=2
                                            ORDER BY c.Id
                                        ");
                                    for ($iz=0; $iz < count($listado_comprobantes_Lote); $iz++)
                                        {
                                            $Monto_Parcial=$listado_comprobantes_Lote[$iz]->Importe;
                                            $Monto_Total=$Monto_Total+$Monto_Parcial;
                                        }
                                    

                                    
                                    //CALCULAR COEFICIENTE
                                    $listado_pagos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT c.Id, ci.Importe, mc.Fecha
                                            FROM comprobantes c
                                            INNER JOIN comprobantes_imputaciones ci ON c.Id=ci.ID_Comprobante
                                            INNER JOIN movimientos_caja mc ON ci.ID_Movimiento=mc.ID
                                            WHERE c.B=0 and c.Id_Lote={$id_item} and c.Cancelado>=1 and ci.B=0
                                            ORDER BY c.Id
                                            
                                        ");
                                    $Monto_Cobrado=0;
                                    $Monto_Cumplimiento=0;
                                    for ($ji=0; $ji < count($listado_pagos); $ji++)
                                        {
                                            $Importe_Cobrado=$listado_pagos[$ji]->Importe;
                                            $Fecha_Cobrado=$listado_pagos[$ji]->Fecha;
                                            $Fecha_Cobrado== date("Y-m-d", strtotime($Fecha_Cobrado));
                                            $Monto_Cobrado=$Monto_Cobrado+$Importe_Cobrado;
                                            if($Fecha_Cobrado<=$Vencimiento)
                                                {
                                                    $Monto_Cumplimiento=$Monto_Cumplimiento+$Importe_Cobrado;
                                                }

                                        }
                                    if($ID_Periodo_Anterior>=1)
                                        {
                                            $control_list2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT fac.Monto_Total
                                                    FROM facturacion fac
                                                    WHERE fac.B=0 and fac.Id_Campana={$ID_Campana} and fac.Id_Periodo={$ID_Periodo_Anterior}
                                                ");
                                            $Verificacion=count($control_list2);
                                            if(empty($Verificacion))
                                                {
                                                    $Monto_Anterior=0;
                                                }
                                            else
                                                {
                                                    $Monto_Anterior=$control_list2[0]->Monto_Total;
                                                }
                                        }
                                    else
                                        {
                                            $Monto_Anterior=0;
                                        }
                                     
                                        
                                    $Cobranza=$Monto_Cobrado;
                                    $Coeficiente_Cobranza=round(($Monto_Cobrado/$Monto_Total),2);
                                    $Porcentaje_Cobranza=round(($Coeficiente_Cobranza*100));
                                    if($Monto_Cobrado==0)
                                        {
                                            $Porcentaje_Cumplimiento=0;
                                        }
                                    else
                                        {
                                            $Coeficiente_Cumplimiento=round(($Monto_Cumplimiento/$Monto_Cobrado),2);
                                            $Porcentaje_Cumplimiento=round(($Coeficiente_Cumplimiento*100));
        
                                        }

                                    
                                    if($Monto_Anterior==0)
                                        {
                                            $Inscripcion_Facturado='No hay datos Anteriores';
                                        }
                                    else
                                        {
                                            $Coeficiente_Anterior=round(($Monto_Total/$Monto_Anterior),2);
                                            if($Coeficiente_Anterior>1)
                                                {
                                                    $Coeficiente_Anterior=$Coeficiente_Anterior-1;
                                                    $Porcentaje_Anterior=round($Coeficiente_Anterior*100);
                                                    $Inscripcion_Facturado=$Porcentaje_Anterior.' % más que el mes anterior';
                                                }
                                            else
                                                {
                                                    if($Coeficiente_Anterior<1)
                                                        {
                                                            $Coeficiente_Anterior=1-$Coeficiente_Anterior;
                                                            $Porcentaje_Anterior=round($Coeficiente_Anterior*100);
                                                            $Inscripcion_Facturado=$Porcentaje_Anterior.'  % menos que el mes anterior';
                                                        }
                                                    else
                                                        {
                                                            $Inscripcion_Facturado='igual que el mes anterior';
                                                        }
                                                }
                                            
                                        }

                                    
                                    $Inscripcion_Cobranza=$Porcentaje_Cobranza.' % del Total';
                                    $Inscripcion_Vencimiento=$Porcentaje_Cumplimiento.' % Cumplimiento';

                                    $resultado[0] = array(
                                        'id_lote' => $id_item,
                                        'lote' => $Lote,
                                        'periodo' => $Periodo_Completo,
                                        'facturado' => $Monto_Total,
                                        'inscripcion_facturado' => $Inscripcion_Facturado,
                                        'cobranza' => $Cobranza,
                                        'inscripcion_cobranza' => $Inscripcion_Cobranza,
                                        'vencimiento' => $Vencimiento,
                                        'inscripcion_vencimiento' => $Inscripcion_Vencimiento
                                    );

                                    //EXPLORO EL DETALLE
                                    $comp_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT c.Id, c.Detalle, c.Importe, c.Cancelado, c.Id_Alumno, re.Nombre, re.Apellido
                                        FROM comprobantes c
                                        INNER JOIN responsabes_economicos re ON c.Id_Responsable=re.Id
                                        WHERE c.B=0 and c.Id_Lote={$id_item}
                                        ORDER BY c.Id
                                    ");
                                    
            
                                    $control2=count($comp_list);
                                    if(empty($control2))
                                        {

                                        }
                                    else
                                        {
                                            for ($pi=0; $pi < count($comp_list); $pi++)
                                                {
                                                    $ID_Alumno=$comp_list[$pi]->Id_Alumno;
                                                    $Cancelado=$comp_list[$pi]->Cancelado;
                                                    if($Cancelado==0)
                                                        {
                                                            $Estado='Impago';
                                                            $Borrable=1;
                                                        }
                                                        if($Cancelado==1)
                                                        {
                                                            $Estado='Pago Parcial';
                                                            $Borrable=0;
                                                        }
                                                        if($Cancelado==2)
                                                        {
                                                            $Estado='Pago';
                                                            $Borrable=0;
                                                        }
                                                foreach($datos_alumnos as $estudiante) {

                                                    $id_estudiante=$estudiante["id"];
                                                    if($id_estudiante==$ID_Alumno)
                                                        {
                                                            $Nombre_A=$estudiante["nombre"];
                                                            $Apellido_A=$estudiante["apellido"];
                                                            $Curso_A=$estudiante["curso"];    
                                                            $habil=1;
                                                            $Alumno_Completo=$Apellido_A.', '.$Nombre_A.' ('.$Curso_A.')';
                                                        }
            
            
                                                }
                                                    $Nombre_R=trim(utf8_decode($comp_list[$pi]->Nombre));
                                                    $Apellido_R=trim(utf8_decode($comp_list[$pi]->Apellido));
                                                    $Responsable=$Apellido_R.', '.$Nombre_R;
                                                    $resultado[0]['detalle'][$pi] = array(
                                                        'id_item' => $comp_list[$pi]->Id,
                                                        'detalle' => trim(utf8_decode($comp_list[$pi]->Detalle)),
                                                        'importe' => round(($comp_list[$pi]->Importe),2),
                                                        'responsable' => $Responsable,
                                                        'alumno' => $Alumno_Completo,
                                                        'estado' => $Estado,
                                                        'borrable' => $Borrable

                                                    );


                                                }
                                        }



                               }    
                          return $resultado;          
                    
                }
          
        }

        public function detalle_comprobante($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

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

            //EXPLORO EL DETALLE
            $comp_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT c.Id, c.Detalle, c.Importe, c.Cancelado, c.Id_Alumno, re.Nombre, re.Apellido
                FROM comprobantes c
                INNER JOIN responsabes_economicos re ON c.Id_Responsable=re.Id
                WHERE c.B=0 and c.Id={$id_item}
                ORDER BY c.Id
            ");
            

            $control2=count($comp_list);
            if(empty($control2))
                {

                }
            else
                {
                    for ($pi=0; $pi < count($comp_list); $pi++)
                        {
                            $ID_Alumno=$comp_list[$pi]->Id_Alumno;
                            $Cancelado=$comp_list[$pi]->Cancelado;
                            if($Cancelado==0)
                                {
                                    $Estado='Impago';
                                    $Borrable=1;
                                }
                                if($Cancelado==1)
                                {
                                    $Estado='Pago Parcial';
                                    $Borrable=0;
                                }
                                if($Cancelado==2)
                                {
                                    $Estado='Pago';
                                    $Borrable=0;
                                }
                        foreach($datos_alumnos as $estudiante) {

                                    $id_estudiante=$estudiante["id"];
                                    if($id_estudiante==$ID_Alumno)
                                        {
                                            $Nombre_A=$estudiante["nombre"];
                                            $Apellido_A=$estudiante["apellido"];
                                            $Curso_A=$estudiante["curso"];    
                                            $habil=1;
                                            $Alumno_Completo=$Apellido_A.', '.$Nombre_A.' ('.$Curso_A.')';
                                        }


                                }
                        $Nombre_R=trim(utf8_decode($comp_list[$pi]->Nombre));
                        $Apellido_R=trim(utf8_decode($comp_list[$pi]->Apellido));
                        $Responsable=$Apellido_R.', '.$Nombre_R;
                        $resultado[0] = array(
                            'id_item' => $comp_list[$pi]->Id,
                            'detalle' => trim(utf8_decode($comp_list[$pi]->Detalle)),
                            'importe' => round(($comp_list[$pi]->Importe),2),
                            'responsable' => $Responsable,
                            'alumno' => $Alumno_Completo,
                            'id_estudiante' => $ID_Alumno,

                            );
                        $conc_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cd.Id, cd.Descripcion, cd.Importe, cd.Id_Tipo_Concepto
                                FROM comprobantes_detalles cd
                                
                                WHERE cd.B=0 and cd.Id_Comprobante={$id_item}
                                ORDER BY cd.Id
                            ");
                        for ($ci=0; $ci < count($conc_list); $ci++)
                            {
                                $ID_Tipo=$conc_list[$ci]->Id_Tipo_Concepto;
                                $Importe_Concepto=$conc_list[$ci]->Importe;
                                $ID_Con=$conc_list[$ci]->Id;

                                if($ID_Tipo==2)
                                    {
                                        $Importe_Concepto=$Importe_Concepto * (-1);

                                    }
                                $resultado[0]['conceptos'][$ci] = array(
                                        'id_item_concepto' => $conc_list[$ci]->Id,
                                        'id_tipo_concepto' => $ID_Tipo,
                                        'descripcion' => trim(utf8_decode($conc_list[$ci]->Descripcion)),
                                        'importe' => round($Importe_Concepto,2)
                                
            
                                        );

                            }




                        }
                }

  
                          return $resultado;          
                    
              
        }

        public function borrar_comprobante($id,$id_item, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

            $comp_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id
                FROM comprobantes
                WHERE Id={$id_item} and Cancelado=0
            ");

            $Ctrl1=count($comp_list);

            $comp_list2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id
                FROM cuenta_corriente
                WHERE Id_Comprobante={$id_item} and Cancelado=0 and Id_Tipo_Comprobante=2
            ");

            $Ctrl2=count($comp_list2);

            if(($Ctrl1>=1) and ($Ctrl2>=1))
                {
                    //BORRO COMPROBANTE
                    $borro = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes
                                SET B=1, Fecha_B='{$FechaActual}', ID_B={$id_usuario}
                                WHERE Id={$id_item}

                            ");

                    //BORRRO DETALLE COMPROBANTE
                    $borro = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes_detalles
                                SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B={$id_usuario}
                                WHERE Id_Comprobante={$id_item} and B=0

                            ");

                    //BORRO CUENTA CORREINTE
                    $borro = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE cuenta_corriente
                                SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B={$id_usuario}
                                WHERE Id_Comprobante={$id_item} and B=0 and Id_Tipo_Comprobante=2

                            ");
                    $resultado='El comprobante ha sido eliminado con éxito';
                }
            else
                {
                    $resultado='error';
                }
            return $resultado;          
                 
        }

        public function borrar_concepto($id,$id_item, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

            $comp_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id, Id_Comprobante
                FROM comprobantes_detalles
                WHERE Id={$id_item}
            ");

            $Ctrl1=count($comp_list);
            if($Ctrl1>=1)
                {
                    $ID_Comprobante=$comp_list[0]->Id_Comprobante;
                    $comp_list2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT Id
                        FROM comprobantes
                        WHERE Id={$ID_Comprobante} and Cancelado=0 and Id_Tipo=2
                    ");
    
                    $Ctrl2=count($comp_list2);
                    if(($Ctrl1>=1) and ($Ctrl2>=1))
                        {
                            //BORRO CONCEPTO
                                $borro = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes_detalles
                                SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B={$id_usuario}
                                WHERE Id={$id_item}

                            ");
                            //RECALCULO VALOR NUEVO
                            $total_cuota=0;
                            $conceptos_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT Importe, Id_Tipo_Concepto
                                FROM comprobantes_detalles
                                WHERE Id_Comprobante={$ID_Comprobante} and B=0
                            ");
                            for ($ci=0; $ci < count($conceptos_list); $ci++)
                                {
                                    $ID_Tipo=$conceptos_list[$ci]->Id_Tipo_Concepto;
                                    $Importe_Concepto=$conceptos_list[$ci]->Importe;
                                    if($ID_Tipo==2)
                                        {
                                            $total_cuota=$total_cuota-$Importe_Concepto;
                                        }
                                    else
                                        {
                                            $total_cuota=$total_cuota+$Importe_Concepto;
                                        }
                                }

                            //ACTUALIZO EL VALOR DEL COMPROBANTE
                            $actualizo= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes
                                SET Importe='{$total_cuota}'
                                WHERE Id={$ID_Comprobante}

                            ");
                            //ACTUALIZO EN CUENTA CORRIENTE
                            $actualizo= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE cuenta_corriente
                                SET Importe='{$total_cuota}'
                                WHERE Id_Comprobante={$ID_Comprobante} and B=0

                            ");
                        $resultado='El concepto ha sido eliminado con éxito y el valor de la cuota recalculado';

                        }
                    else
                        {
                            $resultado='error';
                        }

                }
            else
                {
                    $resultado='error';
                }

            return $resultado;          
                 
        }

        public function modificar_concepto($id, $id_item, $descripcion, $importe, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;
          $descripcion=utf8_encode($descripcion);
            $comp_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id, Id_Comprobante
                FROM comprobantes_detalles
                WHERE Id={$id_item}
            ");

            $Ctrl1=count($comp_list);
            if($Ctrl1>=1)
                {
                    $ID_Comprobante=$comp_list[0]->Id_Comprobante;
                    $comp_list2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT Id
                        FROM comprobantes
                        WHERE Id={$ID_Comprobante} and Cancelado=0 and Id_Tipo=2
                    ");
    
                    $Ctrl2=count($comp_list2);
                    if(($Ctrl1>=1) and ($Ctrl2>=1))
                        {
                            //MODIFICO CONCEPTO
                                $borro = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes_detalles
                                SET Descripcion='{$descripcion}', Importe='{$importe}', Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B={$id_usuario}
                                WHERE Id={$id_item}

                            ");
                            //RECALCULO VALOR NUEVO
                            $total_cuota=0;
                            $conceptos_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT Importe, Id_Tipo_Concepto
                                FROM comprobantes_detalles
                                WHERE Id_Comprobante={$ID_Comprobante} and B=0
                            ");
                            for ($ci=0; $ci < count($conceptos_list); $ci++)
                                {
                                    $ID_Tipo=$conceptos_list[$ci]->Id_Tipo_Concepto;
                                    $Importe_Concepto=$conceptos_list[$ci]->Importe;
                                    if($ID_Tipo==2)
                                        {
                                            $total_cuota=$total_cuota-$Importe_Concepto;
                                        }
                                    else
                                        {
                                            $total_cuota=$total_cuota+$Importe_Concepto;
                                        }
                                }

                            //ACTUALIZO EL VALOR DEL COMPROBANTE
                            $actualizo= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes
                                SET Importe='{$total_cuota}'
                                WHERE Id={$ID_Comprobante}

                            ");
                            //ACTUALIZO EN CUENTA CORRIENTE
                            $actualizo= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE cuenta_corriente
                                SET Importe='{$total_cuota}'
                                WHERE Id_Comprobante={$ID_Comprobante} and B=0

                            ");
                        $resultado='El concepto ha sido modificado con éxito y el valor de la cuota recalculado';

                        }
                    else
                        {
                            $resultado='error2';
                        }

                }
            else
                {
                    $resultado='error1';
                }

            return $resultado;          
                 
        }

        public function agregar_concepto($id, $id_item, $id_concepto, $id_tipo_concepto, $descripcion, $importe, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $ID_Comprobante=$id_item;
          
          $descripcion=utf8_encode($descripcion);

          

          $comp_list2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id
                FROM comprobantes
                WHERE Id={$ID_Comprobante} and Cancelado=0 and Id_Tipo=2
            ");

           $Ctrl2=count($comp_list2);
            if($Ctrl2>=1)
                        {
                            //AGREGO CONCEPTO
                            $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO comprobantes_detalles
                            (Id_Comprobante,Id_Tipo_Concepto,Id_Concepto,Descripcion,Importe)
                            VALUES
                            ({$ID_Comprobante},{$id_tipo_concepto},{$id_concepto},'{$descripcion}','{$importe}')
                   ");
                            //RECALCULO VALOR NUEVO
                            $total_cuota=0;
                            $conceptos_list = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT Importe, Id_Tipo_Concepto
                                FROM comprobantes_detalles
                                WHERE Id_Comprobante={$ID_Comprobante} and B=0
                            ");
                            for ($ci=0; $ci < count($conceptos_list); $ci++)
                                {
                                    $ID_Tipo=$conceptos_list[$ci]->Id_Tipo_Concepto;
                                    $Importe_Concepto=$conceptos_list[$ci]->Importe;
                                    if($ID_Tipo==2)
                                        {
                                            $total_cuota=$total_cuota-$Importe_Concepto;
                                        }
                                    else
                                        {
                                            $total_cuota=$total_cuota+$Importe_Concepto;
                                        }
                                }

                            //ACTUALIZO EL VALOR DEL COMPROBANTE
                            $actualizo= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE comprobantes
                                SET Importe='{$total_cuota}'
                                WHERE Id={$ID_Comprobante}

                            ");
                            //ACTUALIZO EN CUENTA CORRIENTE
                            $actualizo= $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE cuenta_corriente
                                SET Importe='{$total_cuota}'
                                WHERE Id_Comprobante={$ID_Comprobante} and B=0

                            ");
                        $resultado='El concepto ha sido modificado con éxito y el valor de la cuota recalculado';

                        }
                    else
                        {
                            $resultado='error';
                        }

               

            return $resultado;          
                 
        }

}
