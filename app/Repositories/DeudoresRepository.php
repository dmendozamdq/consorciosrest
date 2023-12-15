<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class DeudoresRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
        {
            $this->Alumno = $Alumno;
            $this->dataBaseService = $dataBaseService;
        }


    
   
    public function listado($id)
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

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT rp.Id,rp.Nombre,rp.Apellido,rp.DNI,rp.Estado,rp.Facturable
                          FROM responsabes_economicos rp
                          WHERE rp.B=0
                          ORDER BY rp.Apellido,rp.Nombre
                      ");
            $contador=0;
            $Intervenciones=0;
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
                   $vinculos = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT av.Id
                          FROM alumnos_vinculados av
                          WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}

                      ");
                    $Cant_Vinculos = count($vinculos);

                    $Saldo =$this->saldo($id_institucion,$ID_Responsable);
                    $Saldito = $Saldo[0]['saldo'];

                    $plan_consulta = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT rpmp.Tipo_Medio
                          FROM responsables_economicos_mp rpmp
                          WHERE rpmp.B=0 and rpmp.ID_Responsable={$ID_Responsable}

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


                    //$ID_Estado = $listado[$j]->Estado;


                    if($Saldito>=1)
                        {
                            $listado_intervenciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT ID
                                FROM deudores_intervenciones
                                WHERE B=0 and ID_Responsable={$ID_Responsable}
                                
                            ");
                            $Intervenciones=count($listado_intervenciones);
                            //$Saldo = saldo($id_institucion,$ID_Responsable);
                                                //$Saldo = $Saldo["Saldo"];
                                                $resultado[$contador] = array(
                                                    'id' => $ID_Responsable,
                                                    'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                    'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                    'dni'=> $listado[$j]->DNI,
                                                    'vinculos'=> $Cant_Vinculos,
                                                    'estado'=> $Estado,
                                                    'saldo'=> $Saldito,
                                                    'plan'=> $Tipo_Medio,
                                                    'facturable'=> $listado[$j]->Facturable,
                                                    'intervenciones'=> $Intervenciones



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
                                    $resultado[$contador]['detalle_vinculos'][$k] = array(
                                                                                        
                                                                                            'apellido'=> $Apellido_A,
                                                                                            'nombre'=> $Nombre_A,
                                                                                            'id_alumno'=> $ID_Alumno

                                                                                    );
                                                                        }
                                            }
                            $contador++;

                        }

                    
                }
          return $resultado;
        }


        public function comunicaciones_deudor($id, $id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT us.Name, di.Fecha, di.Hora, da.Medio, di.Mensaje, di.Leido, di.Fecha_Leido, di.Hora_Leido
                          FROM deudores_intervenciones di
                          INNER JOIN responsabes_economicos re ON di.ID_Responsable=re.Id
                          INNER JOIN users us ON di.ID_Usuario=us.id
                          INNER JOIN deudores_acciones da ON di.ID_Medio=da.ID
                          WHERE di.B=0 and di.ID_Responsable={$id_item}
                          ORDER BY di.ID desc
                      ");
          $Cantidad_Intervenciones=count($listado);
          if(empty($Cantidad_Intervenciones))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   
                                                $resultado[$j] = array(
                                                    'fecha' => $listado[$j]->Fecha,
                                                    'hora' => $listado[$j]->Hora,
                                                    'medio'=> trim(utf8_decode($listado[$j]->Medio)),
                                                    'usuario'=> trim(utf8_decode($listado[$j]->Name)),
                                                    'mensaje'=> trim(utf8_decode($listado[$j]->Mensaje)),
                                                    'leido'=> $listado[$j]->Leido,
                                                    'fecha_leido'=> $listado[$j]->Fecha_Leido,
                                                    'hora_leido'=> $listado[$j]->Hora_Leido
                                                    
                                                );
                           

                    
                }
            }
            
          
          return $resultado;
        }

        public function comunicaciones($id)
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
          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT us.Name, re.Nombre, di.ID_Responsable, re.Apellido, di.Fecha, di.Hora, da.Medio, di.Mensaje, di.Leido, di.Fecha_Leido, di.Hora_Leido
                          FROM deudores_intervenciones di
                          INNER JOIN responsabes_economicos re ON di.ID_Responsable=re.Id
                          INNER JOIN users us ON di.ID_Usuario=us.id
                          INNER JOIN deudores_acciones da ON di.ID_Medio=da.ID
                          WHERE di.B=0
                          ORDER BY di.ID desc
                      ");
          $Cantidad_Intervenciones=count($listado);
          if(empty($Cantidad_Intervenciones))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   $Apellido_R=trim(utf8_decode($listado[$j]->Apellido));
                   $Nombre_R=trim(utf8_decode($listado[$j]->Nombre));
                   $Responsable=$Apellido_R.', '.$Nombre_R;
                   $ID_Responsable=$listado[$j]->ID_Responsable;

                                                $resultado[$j] = array(
                                                    'fecha' => $listado[$j]->Fecha,
                                                    'hora' => $listado[$j]->Hora,
                                                    'medio'=> trim(utf8_decode($listado[$j]->Medio)),
                                                    'usuario'=> trim(utf8_decode($listado[$j]->Name)),
                                                    'mensaje'=> trim(utf8_decode($listado[$j]->Mensaje)),
                                                    'responsable'=> $Responsable,
                                                    'id_responsable' => $ID_Responsable,
                                                    'leido'=> $listado[$j]->Leido,
                                                    'fecha_leido'=> $listado[$j]->Fecha_Leido,
                                                    'hora_leido'=> $listado[$j]->Hora_Leido
                                                    
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
                                                                                            'nombre'=> $Nombre_A,
                                                                                            'id_alumno'=> $ID_Alumno

                                                                                    );
                                                                        }
                                            }
                        
                }
            }
            
          
          return $resultado;
        }

        public function medios_comunicacion($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT da.Medio, da.ID, da.Comunicable
                          FROM deudores_acciones da
                          WHERE da.Activo=1
                          ORDER BY da.Medio
                      ");
          $Cantidad_Intervenciones=count($listado);
          if(empty($Cantidad_Intervenciones))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   
                                                $resultado[$j] = array(
                                                    'id' => $listado[$j]->ID,
                                                    'medio' => $listado[$j]->Medio,
                                                    'comunicable' => $listado[$j]->Comunicable
                                                );       
                }
            }
            
          
          return $resultado;
        }

    
    public function nuevo_mensaje($id, $id_item, $id_medio, $id_usuario, $mensaje)
    {
      try {

              date_default_timezone_set('America/Argentina/Buenos_Aires');
              $FechaActual=date("Y-m-d");
              $HoraActual=date("H:i:s");
              $id_institucion=$id;
              
              $mensaje=utf8_encode($mensaje);
              $envio_mensaje = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO deudores_intervenciones
                              (Fecha,Hora,ID_Medio,ID_Usuario,Mensaje,ID_Responsable)
                              VALUES
                              ('{$FechaActual}','{$HoraActual}',{$id_medio},{$id_usuario},'{$mensaje}',{$id_item})
                          ");
            $confirmacion='Se ha registrado correctamente la intervencion';
              return $confirmacion;

          } catch (\Exception $e) {
              return $e;
          }
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

                    $si_consulta = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT cc.Importe
                        FROM cuenta_corriente cc
                        WHERE cc.B=0 AND cc.ID_Responsable={$id_item} AND cc.Id_Tipo_Comprobante=1

                    ");
               $Cant_SI=count($si_consulta);
               if($Cant_SI>=1)
                        {
                            //$Saldo_Inicial = $si_consulta[0]['Importe'];
                            $Saldo_Inicial=$si_consulta[0]->Importe;
                        }
                else
                        {
                            $Saldo_Inicial=0;
                        }

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
                                                'saldo_inicial' => $Saldo_Inicial,
                                                'plan'=> $Tipo_Medio,
                                                'facturable'=> $listado[$j]->Facturable,
                                                'condicion_iva'=> trim(utf8_decode($listado[$j]->Condicion_IVA)),
                                                'id_condicion_iva'=> $listado[$j]->ID_Condicion_IVA,
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

    public function simular_plan($id, $id_item, $importe, $detalle, $cuotas, $interes,$Dia_Tentativo, $Interes_desde_Cuota)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $Cuota_Pura=round(($importe/$cuotas),2);
          if($interes>0)
            {
                $coef_interes=round(($interes/100),2);
                $coef_Interes_dif=$coef_interes+1;
                if($Interes_desde_Cuota==1)
                    {
                        
                        $interes_total=$coef_interes*$cuotas;
                        $coef_interes=$interes_total+1;
                        $total_a_pagar=$importe*$coef_interes;
                        $total_a_pagar=round($total_a_pagar,2);
                        $total_intereses=$importe-$total_a_pagar;
                    }
                else
                    {
                        $Cuota_Pura=round(($importe/$cuotas),2);
                        $nro_cuota=1;
                        $total_a_pagar=0;
                        while($nro_cuota<=$cuotas)
                            {
                                if($nro_cuota<$Interes_desde_Cuota)
                                    {
                                        $Valor_Cuota_R=$Cuota_Pura;
                                        
                                    }
                                else
                                    {
                                       ;
                                        $Valor_Cuota_R=$Cuota_Pura*$coef_Interes_dif;
                                        $Valor_Cuota_R=round($Valor_Cuota_R,2);
                                    }
                                $total_a_pagar=$total_a_pagar+$Valor_Cuota_R;
                                $nro_cuota++;
                            }
                        $total_intereses=$total_a_pagar-$importe;
                    }
               
              
            }
        else
            {
                $valor_cuota=round(($importe/$cuotas),2);
                $total_intereses=0;
                $total_a_pagar=$importe;

            }

        $valor_cuota_promedio=round(($total_a_pagar/$cuotas),2);
        $resultado[0] = array(
                                                    'importe_a_cancelar' => $importe,
                                                    'cantidad_cuotas'=> $cuotas,
                                                    'interes'=> $interes,
                                                    'valor_cuota_promedio'=> $valor_cuota_promedio,
                                                    'total_intereses'=> $total_intereses,
                                                    'total_a_pagar'=> $total_a_pagar,
                                                );
        $nro_cuota=1;
        $MesActual=date("m");
        $AnioActual=date("Y");
        //$Dia_Tentativo=10;
        $Cont_Array=0;
        
        while($nro_cuota<=$cuotas)
            {
                
                $fecha_tentativa=$AnioActual.'-'.$MesActual.'-'.$Dia_Tentativo;
                if($nro_cuota<$Interes_desde_Cuota)
                                    {
                                        $Valor_Cuota_R=$Cuota_Pura;
                                        
                                    }
                                else
                                    {
                                        $Valor_Cuota_R=$Cuota_Pura*$coef_Interes_dif;
                                        $Valor_Cuota_R=round($Valor_Cuota_R,2);
                                    }
                
                
                $resultado[0]['detalle_cuotas'][$Cont_Array] = array(
                                                                                        
                    'nro_cuota'=> $nro_cuota,
                    'fecha_tentativa'=> $fecha_tentativa,
                    'importe'=> $Valor_Cuota_R

            );

            $nro_cuota++;
            $Cont_Array++;
            if($MesActual==12)
                {
                    $MesActual=01;
                    $AnioActual++;
                }
            else
                {
                    $MesActual++;
                }
      
          
        }
        return $resultado;
    }

    public function enviar_simulacion_plan($id, $id_item, $importe, $detalle, $cuotas, $interes,$Dia_Tentativo, $Interes_desde_Cuota, $metodo, $id_usuario)
    {
      $id_institucion=$id;
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $resultado = array();
      $Cuota_Pura=round(($importe/$cuotas),2);

     

      $consulta_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT Nombre,Apellido,Email
            FROM responsabes_economicos
            WHERE Id={$id_item}
        ");
      $Ctrl_Existencia=count($consulta_responsable);
      if($Ctrl_Existencia>=1)
        {
            //INSERTO EL PLAN
            $Hora_Insertada=$HoraActual;

            $Nombre_Responsable=$consulta_responsable[0]->Nombre;
            $Apellido_Responsable=$consulta_responsable[0]->Apellido;
            $Mail_Responsable=$consulta_responsable[0]->Email;
            $Responsable=$Apellido_Responsable.', '.$Nombre_Responsable;
            $nombre='Plan de Pago: '.$Responsable.' ('.$FechaActual.')';
            $nombre=utf8_encode($nombre);
            $inserto_plan = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                    INSERT INTO planes_pago
                    (Estado,Nombre,Fecha,Hora,ID_Usuario,ID_Responsable,Importe,Cantidad_Pagos,Interes,Vencimiento_Dia,Desde_Cuota,Metodo_envio)
                    VALUES
                    (1,'{$nombre}','{$FechaActual}','{$Hora_Insertada}',{$id_usuario},'{$id_item}','{$importe}',{$cuotas},'{$interes}',{$Dia_Tentativo},{$Interes_desde_Cuota},{$metodo})
                ");
            //INDAGO SOBRE EL ID DE PLAN
            $busco_id = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT Id
                            FROM planes_pago
                            WHERE Estado=1 and Nombre='{$nombre}' and Fecha='{$FechaActual}' and Hora='{$Hora_Insertada}'
                            ORDER BY Id desc
                        ");
            $cant_inserciones=count($busco_id);
            if($cant_inserciones>=1)
                {
                    $ID_Plan=$busco_id[0]->Id;
                    //INSERTO COMPROBANTES E IMPORTES ALCANZADOS EN EL PLAN
                    foreach($detalle as $comprobante) {

                        $id_cta=$comprobante["id_cta"];
                        $importe_cta=$comprobante["importe"];
                        $inserto_cte = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO planes_pago_composicion
                            (ID_Plan,ID_Cta_Cte,Importe)
                            VALUES
                            ({$ID_Plan},{$id_cta},'{$importe_cta}')
                        ");
                    }




                    if($interes>0)
                        {
                            $coef_interes=round(($interes/100),2);
                            $coef_Interes_dif=$coef_interes+1;
                            if($Interes_desde_Cuota==1)
                                {
                                    
                                    $interes_total=$coef_interes*$cuotas;
                                    $coef_interes=$interes_total+1;
                                    $total_a_pagar=$importe*$coef_interes;
                                    $total_a_pagar=round($total_a_pagar,2);
                                    $total_intereses=$importe-$total_a_pagar;
                                }
                            else
                                {
                                    $Cuota_Pura=round(($importe/$cuotas),2);
                                    $nro_cuota=1;
                                    $total_a_pagar=0;
                                    while($nro_cuota<=$cuotas)
                                        {
                                            if($nro_cuota<$Interes_desde_Cuota)
                                                {
                                                    $Valor_Cuota_R=$Cuota_Pura;
                                                    
                                                }
                                            else
                                                {
                                                ;
                                                    $Valor_Cuota_R=$Cuota_Pura*$coef_Interes_dif;
                                                    $Valor_Cuota_R=round($Valor_Cuota_R,2);
                                                }
                                            $total_a_pagar=$total_a_pagar+$Valor_Cuota_R;
                                            $nro_cuota++;
                                        }
                                    $total_intereses=$total_a_pagar-$importe;
                                }
                        
                        
                        }
                    else
                        {
                            $valor_cuota=round(($importe/$cuotas),2);
                            $total_intereses=0;
                            $total_a_pagar=$importe;
                        }
                
                    //$valor_cuota_promedio=round(($total_a_pagar/$cuotas),2);
                    
                    $nro_cuota=1;
                    $MesActual=date("m");
                    $AnioActual=date("Y");
                    //$Dia_Tentativo=10;
                    $Cont_Array=0;
                    
                    while($nro_cuota<=$cuotas)
                        {
                            
                            $fecha_tentativa=$AnioActual.'-'.$MesActual.'-'.$Dia_Tentativo;
                            if($nro_cuota<$Interes_desde_Cuota)
                                                {
                                                    $Valor_Cuota_R=$Cuota_Pura;
                                                    
                                                }
                                            else
                                                {
                                                    $Valor_Cuota_R=$Cuota_Pura*$coef_Interes_dif;
                                                    $Valor_Cuota_R=round($Valor_Cuota_R,2);
                                                }
                            
                            //INSERTO LA CUOTA
                            $inserto_cuota = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO planes_pago_detalle
                                (ID_Plan,Cuota,Fecha_Vencimiento,Importe)
                                VALUES
                                ({$ID_Plan},{$nro_cuota},'{$fecha_tentativa}','{$Valor_Cuota_R}')
                            ");

                
                        $nro_cuota++;
                        $Cont_Array++;
                        if($MesActual==12)
                            {
                                $MesActual=01;
                                $AnioActual++;
                            }
                        else
                            {
                                $MesActual++;
                            }
                
                    }

                    //INSERTO ENVIO
                    $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //posibles caracteres a usar
                    $numerodeletras=25; //numero de letras para generar el texto
                    $Cadena_Aleatoria = ""; //variable para almacenar la cadena generada
                    for($i=0;$i<$numerodeletras;$i++)
                        {
                        $Cadena_Aleatoria .= substr($caracteres,rand(0,strlen($caracteres)),1);
                        }
                    $inserto_envio = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO envio_planes
                                (ID_Plan,Tipo_Plan,Fecha,Hora,MailD,Destinatario,Aleatorio)
                                VALUES
                                ({$ID_Plan},1,'{$FechaActual}','{$HoraActual}','{$Mail_Responsable}','{$Responsable}','{$Cadena_Aleatoria}')
                            ");
                    $resultado='El plan ha sido generado y enviado con éxito. Se ha registrado bajo el número '.$ID_Plan;

                }
            else
                {
                    $resultado='ERROR: El plan no pudo ser generado';
                }

            
            


        }
    else
        {
            $resultado='ERROR: El Responsable Económico con se encuentra activo';
        }


      
      
    return $resultado;
}

public function confirmar_plan_nuevo($id, $id_item, $importe, $detalle, $cuotas, $interes,$Dia_Tentativo, $Interes_desde_Cuota, $metodo, $id_usuario, $cancelacion, $generacion)
    {
      $id_institucion=$id;
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $resultado = array();
      $Cuota_Pura=round(($importe/$cuotas),2);

      

      $consulta_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT Nombre,Apellido,Email
            FROM responsabes_economicos
            WHERE Id={$id_item}
        ");
      $Ctrl_Existencia=count($consulta_responsable);
      if($Ctrl_Existencia>=1)
        {
            //INSERTO EL PLAN
            $Hora_Insertada=$HoraActual;

            $Nombre_Responsable=$consulta_responsable[0]->Nombre;
            $Apellido_Responsable=$consulta_responsable[0]->Apellido;
            $Mail_Responsable=$consulta_responsable[0]->Email;
            $Responsable=$Apellido_Responsable.', '.$Nombre_Responsable;

            $nombre='Plan de Pago: '.$Responsable.' ('.$FechaActual.')';
            $nombre=utf8_encode($nombre);

            $inserto_plan = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                    INSERT INTO planes_pago
                    (Estado,Nombre,Fecha,Hora,ID_Usuario,ID_Responsable,Importe,Cantidad_Pagos,Interes,Vencimiento_Dia,Desde_Cuota,Metodo_envio,Fecha_Alta,Hora_Alta,ID_Alta,Generacion,Cancelacion)
                    VALUES
                    (2,'{$nombre}','{$FechaActual}','{$Hora_Insertada}',{$id_usuario},'{$id_item}','{$importe}',{$cuotas},'{$interes}',{$Dia_Tentativo},{$Interes_desde_Cuota},{$metodo},'{$FechaActual}','{$HoraActual}',{$id_usuario},{$generacion},{$cancelacion})
                ");
            //INDAGO SOBRE EL ID DE PLAN
            $busco_id = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT Id
                            FROM planes_pago
                            WHERE Estado=1 and Nombre='{$nombre}' and Fecha='{$FechaActual}' and Hora='{$Hora_Insertada}'
                            ORDER BY Id desc
                        ");
            $cant_inserciones=count($busco_id);
            if($cant_inserciones>=1)
                {
                    $ID_Plan=$busco_id[0]->Id;
                    //INSERTO COMPROBANTES E IMPORTES ALCANZADOS EN EL PLAN
                    $sumatoria_comprobantes=0;
                    foreach($detalle as $comprobante) {

                        $id_cta=$comprobante["id_cta"];
                        $importe_cta=$comprobante["importe"];
                        $sumatoria_comprobantes= $sumatoria_comprobantes+$importe_cta;
                        $inserto_cte = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO planes_pago_composicion
                            (ID_Plan,ID_Cta_Cte,Importe)
                            VALUES
                            ({$ID_Plan},{$id_cta},'{$importe_cta}')
                        ");
                        if($cancelacion==5)
                            {
                                //CANCELO COMPROBANTES Y GENERO IMPUTACION
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado=2
                                    WHERE Id={$id_cta}
                                ");
                                $Descripcion_Imputacion='Cancelación por plan de pagos Nro '.$ID_Plan;
                                $Descripcion_Imputacion=utf8_encode($Descripcion_Imputacion);
                                $inserto_impu = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO comprobantes_imputaciones
                                    (Importe,Cancela,ID_Cta_Cte,Descripcion)
                                    VALUES
                                    ({$importe_cta},2,{$id_cta},'{$Descripcion_Imputacion}')
                                ");
                            }
                        if($cancelacion==6)
                            {
                                //CANCELO COMPROBANTES Y GENERO IMPUTACION
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Interes=0
                                    WHERE Id={$id_cta}
                                ");
                                
                            }



                    }

                    if($cancelacion==5)
                        {
                            $inserto_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Importe)
                            VALUES
                            ({$id_item},'{$FechaActual}',4,'{$Descripcion_Imputacion}','{$sumatoria_comprobantes}')
                        ");
                        }





                    if($interes>0)
                        {
                            $coef_interes=round(($interes/100),2);
                            $coef_Interes_dif=$coef_interes+1;
                            if($Interes_desde_Cuota==1)
                                {
                                    
                                    $interes_total=$coef_interes*$cuotas;
                                    $coef_interes=$interes_total+1;
                                    $total_a_pagar=$importe*$coef_interes;
                                    $total_a_pagar=round($total_a_pagar,2);
                                    $total_intereses=$importe-$total_a_pagar;
                                }
                            else
                                {
                                    $Cuota_Pura=round(($importe/$cuotas),2);
                                    $nro_cuota=1;
                                    $total_a_pagar=0;
                                    while($nro_cuota<=$cuotas)
                                        {
                                            if($nro_cuota<$Interes_desde_Cuota)
                                                {
                                                    $Valor_Cuota_R=$Cuota_Pura;
                                                    
                                                }
                                            else
                                                {
                                                ;
                                                    $Valor_Cuota_R=$Cuota_Pura*$coef_Interes_dif;
                                                    $Valor_Cuota_R=round($Valor_Cuota_R,2);
                                                }
                                            $total_a_pagar=$total_a_pagar+$Valor_Cuota_R;
                                            $nro_cuota++;
                                        }
                                    $total_intereses=$total_a_pagar-$importe;
                                }
                        
                        
                        }
                    else
                        {
                            $valor_cuota=round(($importe/$cuotas),2);
                            $total_intereses=0;
                            $total_a_pagar=$importe;
                        }
                
                    //$valor_cuota_promedio=round(($total_a_pagar/$cuotas),2);
                    
                    $nro_cuota=1;
                    $MesActual=date("m");
                    $AnioActual=date("Y");
                    //$Dia_Tentativo=10;
                    $Cont_Array=0;
                    
                    while($nro_cuota<=$cuotas)
                        {
                            
                            $fecha_tentativa=$AnioActual.'-'.$MesActual.'-'.$Dia_Tentativo;
                            //VER COMO AGREGAR LA FECHA DE INSERCION DE CUOTA
                            if($nro_cuota<$Interes_desde_Cuota)
                                                {
                                                    $Valor_Cuota_R=$Cuota_Pura;
                                                    
                                                }
                                            else
                                                {
                                                    $Valor_Cuota_R=$Cuota_Pura*$coef_Interes_dif;
                                                    $Valor_Cuota_R=round($Valor_Cuota_R,2);
                                                }
                            
                            //INSERTO LA CUOTA
                            $inserto_cuota = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO planes_pago_detalle
                                (ID_Plan,Cuota,Fecha_Vencimiento,Importe)
                                VALUES
                                ({$ID_Plan},{$nro_cuota},'{$fecha_tentativa}','{$Valor_Cuota_R}')
                            ");

                            if($generacion==3)
                             {
                                //INSERTO LA CUOTA EN CUENTA CORRIENTE CON FECHA DE VENCIMIENTO
                                $Descripcion_Cuota='Plan de Pago Nro '.$ID_Plan.' Cuota '.$nro_cuota.' de '.$cuotas;
                                $inserto_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO cuenta_corriente
                                    (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Importe,Interes)
                                    VALUES
                                    ({$id_item},'{$fecha_tentativa}',12,'{$Descripcion_Cuota}','{$Valor_Cuota_R}',1)
                                ");

                             }
                             if($generacion==4)
                                {
                                    if($nro_cuota==1)
                                        {
                                            $Descripcion_Cuota='Plan de Pago Nro '.$ID_Plan.' Cuota '.$nro_cuota.' de '.$cuotas;
                                            $inserto_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                INSERT INTO cuenta_corriente
                                                (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Importe,Interes)
                                                VALUES
                                                ({$id_item},'{$fecha_tentativa}',12,'{$Descripcion_Cuota}','{$Valor_Cuota_R}',1)
                                            ");
                                        }
                                }


                
                        $nro_cuota++;
                        $Cont_Array++;
                        if($MesActual==12)
                            {
                                $MesActual=01;
                                $AnioActual++;
                            }
                        else
                            {
                                $MesActual++;
                            }
                
                    }

                    //INSERTO ENVIO
                    $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //posibles caracteres a usar
                    $numerodeletras=25; //numero de letras para generar el texto
                    $Cadena_Aleatoria = ""; //variable para almacenar la cadena generada
                    for($i=0;$i<$numerodeletras;$i++)
                        {
                        $Cadena_Aleatoria .= substr($caracteres,rand(0,strlen($caracteres)),1);
                        }
                    $inserto_envio = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                INSERT INTO envio_planes
                                (ID_Plan,Tipo_Plan,Fecha,Hora,MailD,Destinatario,Aleatorio)
                                VALUES
                                ({$ID_Plan},2,'{$FechaActual}','{$HoraActual}','{$Mail_Responsable}','{$Responsable}','{$Cadena_Aleatoria}')
                            ");
                    $resultado='El plan ha sido generado y enviado con éxito. Se ha registrado bajo el número '.$ID_Plan;

                }
            else
                {
                    $resultado='ERROR: El plan no pudo ser generado';
                }

            
            


        }
    else
        {
            $resultado='ERROR: El Responsable Económico con se encuentra activo';
        }


      
      
    return $resultado;
}

public function confirmar_plan_simulado($id, $id_item, $id_plan, $id_usuario, $cancelacion, $generacion)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pg.Id, pg.Estado, pg.Nombre, pg.Fecha, pg.Hora, pg.Importe, pg.Cantidad_Pagos, pg.Interes, re.Apellido, us.name
                          FROM planes_pago pg
                          INNER JOIN responsabes_economicos re ON pg.ID_Responsable=re.Id
                          INNER JOIN users us ON pg.ID_Usuario=us.id
                          WHERE pg.B=0 and pg.Id={$id_item}
                          ORDER BY pg.Id desc
                      ");
          $Cantidad_Planes=count($listado);
          if(empty($Cantidad_Planes))
            {

            }
        else
            {
                $consulta_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT Nombre,Apellido,Email
                        FROM responsabes_economicos
                        WHERE Id={$id_item}
                    ");
                $Nombre_Responsable=$consulta_responsable[0]->Nombre;
                $Apellido_Responsable=$consulta_responsable[0]->Apellido;
                $Mail_Responsable=$consulta_responsable[0]->Email;
                $Responsable=$Apellido_Responsable.', '.$Nombre_Responsable;

                $nombre='Plan de Pago: '.$Responsable.' ('.$FechaActual.')';
                $nombre=utf8_encode($nombre);
                
                for ($j=0; $j < count($listado); $j++)
                {
                   $State=$listado[$j]->Estado;
                   $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                        UPDATE planes_pago
                        SET Nombre='{$nombre}',Estado=2,Fecha_Alta='{$FechaActual}',Hora_Alta='{$HoraActual}',ID_Alta={$id_usuario},Generacion={$generacion},Cancelacion={$cancelacion}
                        WHERE Id={$id_plan}
                    ");

                    $cuotas=$listado[$j]->Cantidad_Pagos;


                    /*
                    $resultado[$j] = array(
                        'id' => $listado[$j]->Id,
                        'fecha' => $listado[$j]->Fecha,
                        'hora' => $listado[$j]->Hora,
                        'nombre' => trim(utf8_decode($listado[$j]->Nombre)),
                        'estado'=> $Estado,
                        'responsable'=> trim(utf8_decode($listado[$j]->Apellido)),
                        'importe'=> $listado[$j]->Importe,
                        'cuotas'=> $listado[$j]->Cantidad_Pagos,
                        'interes'=> $listado[$j]->Interes,
                        'creador'=>trim(utf8_decode($listado[$j]->name)),
                    );
                    */

                    //CUOTAS
                    $listado_cuotas = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ppd.Cuota, ppd.Fecha_Vencimiento, ppd.Importe, ppd.Id_Cobro
                          FROM planes_pago_detalle ppd
                          WHERE ppd.ID_Plan={$id_item} and ppd.B=0
                          ORDER BY ppd.Cuota
                      ");
                    for ($k=0; $k < count($listado_cuotas); $k++)
                        {
                           $ID_Cobranza=$listado_cuotas[$k]->Id_Cobro;
                           $Fecha_V=$listado_cuotas[$k]->Fecha_Vencimiento;
                           $nro_cuota=$listado_cuotas[$k]->Cuota;
                           $Valor_Cuota_R=$listado_cuotas[$k]->Importe;
                           $fecha_tentativa=$Fecha_V;

                           if($generacion==3)
                             {
                                //INSERTO LA CUOTA EN CUENTA CORRIENTE CON FECHA DE VENCIMIENTO
                                $Descripcion_Cuota='Plan de Pago Nro '.$id_plan.' Cuota '.$nro_cuota.' de '.$cuotas;
                                $inserto_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO cuenta_corriente
                                    (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Importe,Interes)
                                    VALUES
                                    ({$id_item},'{$fecha_tentativa}',12,'{$Descripcion_Cuota}','{$Valor_Cuota_R}',1)
                                ");

                             }
                             if($generacion==4)
                                {
                                    if($nro_cuota==1)
                                        {
                                            $Descripcion_Cuota='Plan de Pago Nro '.$id_plan.' Cuota '.$nro_cuota.' de '.$cuotas;
                                            $inserto_cta = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                INSERT INTO cuenta_corriente
                                                (Id_Responsable,Fecha,Id_Tipo_Comprobante,Descripcion,Importe,Interes)
                                                VALUES
                                                ({$id_item},'{$fecha_tentativa}',12,'{$Descripcion_Cuota}','{$Valor_Cuota_R}',1)
                                            ");
                                        }
                                }
                           

                            /*
                            $resultado[$j]['detalle_cuotas'][$k]= array(
                                
                                'cuota' => $listado_cuotas[$k]->Cuota,
                                'vencimiento' => $Fecha_V,
                                'importe'=> $listado_cuotas[$k]->Importe,
                                'estado'=> $Estado
                            );
                            */
                        }

              //ALCANCES
                    $listado_alcance = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT ppc.ID_Cta_Cte, ppc.Importe, cc.Descripcion
                    FROM planes_pago_composicion ppc
                    INNER JOIN cuenta_corriente cc ON ppc.ID_Cta_Cte=cc.Id
                    WHERE ppc.ID_Plan={$id_item} and ppc.B=0
                    ORDER BY ppc.ID
                ");
              for ($k=0; $k < count($listado_alcance); $k++)
                  {
                     
                    $id_cta=$listado_alcance[$k]->ID_Cta_Cte;
                    $importe_cta=$listado_alcance[$k]->Importe;

                    if($cancelacion==5)
                            {
                                //CANCELO COMPROBANTES Y GENERO IMPUTACION
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Cancelado=2
                                    WHERE Id={$id_cta}
                                ");
                                $Descripcion_Imputacion='Cancelación por plan de pagos Nro '.$id_plan;
                                $Descripcion_Imputacion=utf8_encode($Descripcion_Imputacion);
                                $inserto_impu = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO comprobantes_imputaciones
                                    (Importe,Cancela,ID_Cta_Cte,Descripcion)
                                    VALUES
                                    ({$importe_cta},2,{$id_cta},'{$Descripcion_Imputacion}')
                                ");
                            }
                        if($cancelacion==6)
                            {
                                //CANCELO COMPROBANTES Y GENERO IMPUTACION
                                $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE cuenta_corriente
                                    SET Interes=0
                                    WHERE Id={$id_cta}
                                ");
                                
                            }
                     /*
                      $resultado[$j]['detalle_comprobantes'][$k]= array(
                          
                          'id_cta' => $listado_alcance[$k]->ID_Cta_Cte,
                          'importe'=> $listado_alcance[$k]->Importe,
                          'descripcion'=> trim(utf8_decode($listado_alcance[$k]->Descripcion))
                      );
                      */
                  }

                        
                }

                //INSERTO ENVIO
                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //posibles caracteres a usar
                $numerodeletras=25; //numero de letras para generar el texto
                $Cadena_Aleatoria = ""; //variable para almacenar la cadena generada
                for($i=0;$i<$numerodeletras;$i++)
                    {
                    $Cadena_Aleatoria .= substr($caracteres,rand(0,strlen($caracteres)),1);
                    }
                $inserto_envio = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO envio_planes
                            (ID_Plan,Tipo_Plan,Fecha,Hora,MailD,Destinatario,Aleatorio)
                            VALUES
                            ({$id_plan},2,'{$FechaActual}','{$HoraActual}','{$Mail_Responsable}','{$Responsable}','{$Cadena_Aleatoria}')
                        ");
                $resultado='El plan ha sido generado y enviado con éxito. Se ha registrado bajo el número '.$id_plan;
            }
            
          
          return $resultado;
        }

public function listado_planes($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pg.Id, pg.Estado, pg.Nombre, pg.Fecha, pg.Importe, pg.Cantidad_Pagos, pg.Interes, re.Apellido
                          FROM planes_pago pg
                          INNER JOIN responsabes_economicos re ON pg.ID_Responsable=re.Id
                          WHERE pg.B=0
                          ORDER BY pg.Id desc
                      ");
          $Cantidad_Planes=count($listado);
          if(empty($Cantidad_Planes))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   $State=$listado[$j]->Estado;
                   if($State==1)
                    {
                        $Estado='Simulado';

                    }
                    if($State==2)
                        {
                            $Estado='Confirmado';
                        }
                                                $resultado[$j] = array(
                                                    'id' => $listado[$j]->Id,
                                                    'fecha' => $listado[$j]->Fecha,
                                                    'nombre' => trim(utf8_decode($listado[$j]->Nombre)),
                                                    'estado'=> $Estado,
                                                    'responsable'=> trim(utf8_decode($listado[$j]->Apellido)),
                                                    'importe'=> $listado[$j]->Importe,
                                                    'cuotas'=> $listado[$j]->Cantidad_Pagos,
                                                    'interes'=> $listado[$j]->Interes
                                                    
                                                );
                        
                }
            }
            
          
          return $resultado;
        }

    public function consulta_plan($id, $id_item)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pg.Id, pg.Estado, pg.Nombre, pg.Fecha, pg.Hora, pg.Importe, pg.Cantidad_Pagos, pg.Interes, re.Apellido, us.name, pg.ID_Responsable, re.Nombre AS Nombre_R
                          FROM planes_pago pg
                          INNER JOIN responsabes_economicos re ON pg.ID_Responsable=re.Id
                          INNER JOIN users us ON pg.ID_Usuario=us.id
                          WHERE pg.B=0 and pg.Id={$id_item}
                          ORDER BY pg.Id desc
                      ");
          $Cantidad_Planes=count($listado);
          if(empty($Cantidad_Planes))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   $State=$listado[$j]->Estado;
                   if($State==1)
                        {
                            $Estado='Simulado';

                        }
                    if($State==2)
                        {
                            $Estado='Confirmado';
                        }
                    $Res_Ap=$listado[$j]->Apellido;
                    $Res_Nom=$listado[$j]->Nombre_R;
                    $Res=$Res_Ap.', '.$Res_Nom;
                    $resultado[$j] = array(
                        'id' => $listado[$j]->Id,
                        'fecha' => $listado[$j]->Fecha,
                        'hora' => $listado[$j]->Hora,
                        'nombre' => trim(utf8_decode($listado[$j]->Nombre)),
                        'estado'=> $Estado,
                        'responsable'=> trim(utf8_decode($Res)),
                        'id_responsable'=> $listado[$j]->ID_Responsable,
                        'importe'=> $listado[$j]->Importe,
                        'cuotas'=> $listado[$j]->Cantidad_Pagos,
                        'interes'=> $listado[$j]->Interes,
                        'creador'=>trim(utf8_decode($listado[$j]->name)),
                    );

                    //CUOTAS
                    $listado_cuotas = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ppd.Cuota, ppd.Fecha_Vencimiento, ppd.Importe, ppd.Id_Cobro
                          FROM planes_pago_detalle ppd
                          WHERE ppd.ID_Plan={$id_item} and ppd.B=0
                          ORDER BY ppd.Cuota
                      ");
                    for ($k=0; $k < count($listado_cuotas); $k++)
                        {
                           $ID_Cobranza=$listado_cuotas[$k]->Id_Cobro;
                           $Fecha_V=$listado_cuotas[$k]->Fecha_Vencimiento;

                           if($ID_Cobranza==0)
                            {
                                if($FechaActual>$Fecha_V)
                                    {
                                        $Estado='Vencido';
                                    }
                                else
                                    {
                                        $Estado='Pendiente';
                                    }
                            }
                            if($ID_Cobranza==1)
                            {
                                $Estado='Pago Parcial';
                            }
                            if($ID_Cobranza==2)
                            {
                                $Estado='Pagado';
                            }
                           
                            $resultado[$j]['detalle_cuotas'][$k]= array(
                                
                                'cuota' => $listado_cuotas[$k]->Cuota,
                                'vencimiento' => $Fecha_V,
                                'importe'=> $listado_cuotas[$k]->Importe,
                                'estado'=> $Estado
                            );
                        }

              //ALCANCES
                    $listado_alcance = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT ppc.ID_Cta_Cte, ppc.Importe, cc.Descripcion
                    FROM planes_pago_composicion ppc
                    INNER JOIN cuenta_corriente cc ON ppc.ID_Cta_Cte=cc.Id
                    WHERE ppc.ID_Plan={$id_item} and ppc.B=0
                    ORDER BY ppc.ID
                ");
              for ($k=0; $k < count($listado_alcance); $k++)
                  {
                     
                     
                      $resultado[$j]['detalle_comprobantes'][$k]= array(
                          
                          'id_cta' => $listado_alcance[$k]->ID_Cta_Cte,
                          'importe'=> $listado_alcance[$k]->Importe,
                          'descripcion'=> trim(utf8_decode($listado_alcance[$k]->Descripcion))
                      );
                  }

                        
                }
            }
            
          
          return $resultado;
        }
        
    public function borrar_plan($id, $id_item, $id_usuario)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $Ctrol_1=0;
          $Ctrol_2=0;

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT pg.Id, pg.Estado, pg.Nombre, pg.Fecha, pg.Hora, pg.Importe, pg.Cantidad_Pagos, pg.Interes, re.Apellido, us.name
                          FROM planes_pago pg
                          INNER JOIN responsabes_economicos re ON pg.ID_Responsable=re.Id
                          INNER JOIN users us ON pg.ID_Usuario=us.id
                          WHERE pg.B=0 and pg.Id={$id_item}
                          ORDER BY pg.Id desc
                      ");
          $Cantidad_Planes=count($listado);


          if(empty($Cantidad_Planes))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   $State=$listado[$j]->Estado;
                   if($State==1)
                        {
                            $Ctrol_1=1;
                            $Ctrol_2=1;

                        }
                    if($State==2)
                        {
                            
                        }
                    if(($Ctrol_1==1) and ($Ctrol_2==1))
                        {
                            //ELIMINO
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE planes_pago
                                SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B='{$id_usuario}'
                                WHERE Id={$id_item}
                            ");
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE planes_pago_composicion
                                SET B=1
                                WHERE ID_Plan={$id_item}
                            ");
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                UPDATE planes_pago_detalle
                                SET B=1
                                WHERE ID_Plan={$id_item}
                            ");

                            $resultado='El plan ha sido eliminado con éxito';

                        }
                    else
                        {
                            $resultado='El no ha podido ser eliminado';
                        }
                }

          
         
        }
        return $resultado;
    }


    public function parametros_plan($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ppp.ID, ppp.Parametro
                          FROM planes_pago_parametros ppp
                          WHERE ppp.B=0
                          ORDER BY ppp.ID desc
                      ");
          $Cantidad_Parametros=count($listado);
          if(empty($Cantidad_Parametros))
            {

            }
        else
            {
                for ($j=0; $j < count($listado); $j++)
                {
                   
                    $ID_Parametro=$listado[$j]->ID;
                    $resultado[$j] = array(
                        'id_parametro' => $ID_Parametro,
                        'parametro' => trim(utf8_decode($listado[$j]->Parametro))   
                    ); 
                    
                    $listado2 = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT pppd.ID, pppd.Valor
                                    FROM planes_pago_parametros_detalle pppd
                                    WHERE pppd.B=0 and pppd.ID_Parametro={$ID_Parametro}
                                    ORDER BY pppd.ID
                                ");
                    $Cantidad_Parametros2=count($listado2);
                    if(empty($Cantidad_Parametros2))
                        {

                        }
                    else
                        {
                            for ($k=0; $k < count($listado2); $k++)
                                {
                                
                                    $ID_Parametro_D=$listado2[$k]->ID;

                                    $resultado[$j]['valores'][$k] = array(
                                        'id_valor' => $ID_Parametro_D,
                                        'valor' => trim(utf8_decode($listado2[$k]->Valor))
                                    ); 
                                    
                                }
                        }
                    
     
                }
            }
            
          
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
                            SELECT cc.Id, cc.Fecha, cc.Id_Comprobante, cc.Importe, cc.Descripcion, cc.Id_Tipo_Comprobante, cc.Cancelado, cct.Tipo, cct.Clase, cct.Detalle, cc.Facturado
                            FROM cuenta_corriente cc
                            INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                            WHERE cc.B=0 and cc.Id_Responsable={$id_item}
                            ORDER by cc.Fecha, cc.ID_Tipo_Comprobante DESC

                      ");
          $Saldo=0;
          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Tipo_Comprobante=$listado[$j]->Id_Tipo_Comprobante;
                   $Facturado=$listado[$j]->Facturado;
                   if(($ID_Tipo_Comprobante==7) or ($ID_Tipo_Comprobante==8) or ($ID_Tipo_Comprobante==9) or ($ID_Tipo_Comprobante==10) or ($ID_Tipo_Comprobante==11))
                        {
                            if(empty($Facturado))
                                {
                                    $Borrable=1;
                                }
                            else
                                {
                                    $Borrable=0;
                                }
                            
                        }
                        else
                        {
                            $Borrable=0;
                        }
                   $Importe=$listado[$j]->Importe;
                   $Cancelado=$listado[$j]->Cancelado;
                   $ID_Movimiento=$listado[$j]->Id;
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

                    if(($ID_Comprobante>=1) and ($ID_Tipo_Comprobante==2))
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
                if($Cancelado>=1)
                        {
                            $Descripcion_Cancelacion='';
                            $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ci.Fecha, ci.Importe
                            FROM comprobantes_imputaciones ci
                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento} and ci.ID_Movimiento=0
                            ORDER by ci.Id
                            ");
                            $Ctrl_Imputaciones=count($c_cancelacion);
                            if($Ctrl_Imputaciones>=1)
                                {
                                    for ($f=0; $f < count($c_cancelacion); $f++)
                                        {
                                            $Fecha_Imputacion=$c_cancelacion[$f]->Fecha;
                                            $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                            $Detalle_Imputacion=trim(utf8_decode('Imputacion por saldo a Favor'));
                                            $Descripcion_Cancelacion=$Descripcion_Cancelacion.'<p>$ '.$Importe_Imputacion.' - '.$Detalle_Imputacion.' ('.$Fecha_Imputacion.')';
                                        }
                                }
                            $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ci.Fecha,ci.ID_Movimiento, ci.Importe, mc.Detalle
                            FROM comprobantes_imputaciones ci
                            INNER JOIN movimientos_caja mc ON ci.ID_Movimiento=mc.Id
                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento}
                            ORDER by ci.Id
                            ");
                            $Ctrl_Imputaciones=count($c_cancelacion);
                            if($Ctrl_Imputaciones>=1)
                                {
                                    for ($f=0; $f < count($c_cancelacion); $f++)
                                        {
                                            $Fecha_Imputacion=$c_cancelacion[$f]->Fecha;
                                            $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                            $Detalle_Imputacion=trim(utf8_decode($c_cancelacion[$f]->Detalle));
                                            $Descripcion_Cancelacion=$Descripcion_Cancelacion.'<p>$ '.$Importe_Imputacion.' - '.$Detalle_Imputacion.' ('.$Fecha_Imputacion.')';
                                        }
                                }
                            
                        }
                else
                        {
                            $Descripcion_Cancelacion='';
                        }
                $Saldo=round($Saldo,2);
                $Fecha_Movimiento=$listado[$j]->Fecha;
                $Fecha_Movimiento=date('d-m-Y',strtotime($Fecha_Movimiento));
                $resultado[$j] = array(
                                                //'id' => $ID_Comprobante,
                                                'id' => $ID_Movimiento,
                                                'fecha'=> $Fecha_Movimiento,
                                                'comprobante'=> $Identificacion_Comprobante,
                                                'tipo'=> $listado[$j]->Tipo,
                                                'clase'=> $Clase,
                                                'concepto'=> trim(utf8_decode($listado[$j]->Descripcion,)),
                                                'importe' => $Importe,
                                                'salddo' => $Saldo,
                                                'enlace' => $Enlace_Comprobante,
                                                'observaciones' => $Observaciones_Comprobante,
                                                'cancelado' => $Cancelado,
                                                'descripcion_cancelacion' => $Descripcion_Cancelacion,
                                                'borrable' => $Borrable


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
                            ORDER by cc.Fecha

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


}
