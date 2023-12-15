<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class ResponsablesRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
        {
            $this->Alumno = $Alumno;
            $this->dataBaseService = $dataBaseService;
        }


    public function agregar($id,$nombre,$apellido,$dni,$domicilio,$telefono,$email,$id_usuario,$nombre_fiscal,$cuit,$tipo_factura,$saldo_inicial,$plan,$facturable,$condicion_iva)
    {
      $habilitado=0;
      try {

               date_default_timezone_set('America/Argentina/Buenos_Aires');
               $FechaActual=date("Y-m-d");
               $HoraActual=date("H:i:s");
               $id_institucion=$id;
               $nombre=utf8_encode($nombre);
               $cuit2=$cuit;
               $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT Id
                                  FROM responsabes_economicos
                                  WHERE DNI='{$dni}' and B=0

                                    ");
                if(empty($facturable))
                    {
                        $facturable='0';
                    }
                else
                    {
                        $facturable='1';
                    }

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
                    //BUSQUEDA DE ID_GENERAL POR DNI y/o CORREO
                    $result = \DB::select("
                        SELECT rf.id
                        FROM users rf
                        WHERE rf.DNI='{$dni}'
                    ");
                    $habilitado2=0;
                    $ctrl_dni=count($result);

                    if(empty($ctrl_dni))
                        {
                            $result2 = \DB::select("
                                SELECT rf.id
                                FROM users rf
                                WHERE rf.email='{$email}'
                            ");
                            $ctrl_mail=count($result2);
                            if(empty($ctrl_mail))
                                {

                                }
                            else
                                {
                                    $id_gral = $result2[0]->id;
                                    $habilitado2 ++;
                                }
                        }
                    else
                        {
                            $id_gral = $result[0]->id;
                            $habilitado2 ++;
                        }

                    if(empty($habilitado2))
                        {
                            //CONTINUO
                            //INSERTO EN LA GENERAL
                            $name=$nombre.' '.$apellido;
                            $pass=bcrypt($dni);
                            $creo_registro = \DB::Insert("
                                    INSERT INTO users
                                    (name,email,password,DNI)
                                    VALUES
                                    ('{$name}','{$email}','{$pass}','{$dni}')
                                ");
                            $verifico_insercion = \DB::select("
                                    SELECT  rf.id
                                    FROM users rf
                                    WHERE rf.DNI='{$dni}'
                                ");
                            $id_gral = $verifico_insercion[0]->id;

                        }

                    //INSERTO
                    /*$creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO responsabes_economicos
                                    (Nombre,Apellido,DNI,Domicilio,Telefono,Email,Id_General,Fecha_Alta,Id_Usuario,Nombre_Fiscal,Tipo_Factura,CUIT)
                                    VALUES ('{$nombre}','{$apellido}','{$dni}','{$domicilio}','{$telefono}','{$email}','{$id_gral}','{$FechaActual}','{$id_usuario}','{$nombre_fiscal}',{$tipo_factura},'{$cuit2}')
                                ");
                    */

                    $seleccion_condicion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT ci.Condicion
                                    FROM condiciones_iva ci
                                    WHERE ci.ID={$condicion_iva}
                                ");

                    $condicion_iva_texto = $seleccion_condicion[0]->Condicion;
                    $condicion_iva_texto = utf8_encode($condicion_iva_texto);


                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO responsabes_economicos
                                    (Nombre,Apellido,DNI,Domicilio,Telefono,Email,Id_General,Fecha_Alta,Id_Usuario,Facturable,Condicion_IVA,ID_Condicion_IVA)
                                    VALUES ('{$nombre}','{$apellido}','{$dni}','{$domicilio}','{$telefono}','{$email}','{$id_gral}','{$FechaActual}','{$id_usuario}','{$facturable}','{$condicion_iva_texto}',{$condicion_iva})
                                ");


                    $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT id
                                    FROM responsabes_economicos
                                    WHERE DNI='{$dni}' and B=0
                                ");

                    $id_responsable = $verifico_insercion[0]->id;

                    if(empty($plan))
                        {
                            $plan=99;
                        }
                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                        INSERT INTO responsables_economicos_mp
                        (ID_Responsable,Tipo_Medio)
                        VALUES ('{$id_responsable}','{$plan}')
                    ");

                    if($cuit2>=1)
                        {
                            $actualizo_datos_fiscales = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                    UPDATE responsabes_economicos
                                    SET Nombre_Fiscal='{$nombre_fiscal}',CUIT='{$cuit2}'
                                    WHERE Id={$id_responsable}
                                    ");
                        }
                    if($saldo_inicial>=1)
                        {
                            $estado_cancelado=0;
                        }
                    else
                        {
                            $estado_cancelado=2;
                        }
                    $texto_saldo='Saldo Inicial';
                    $creo_saldo_inicial = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO cuenta_corriente
                                    (Id_Responsable,Fecha,Id_Tipo_Comprobante,Importe,Cancelado,Descripcion)
                                    VALUES ('{$id_responsable}','{$FechaActual}','1','{$saldo_inicial}',{$estado_cancelado},'{$texto_saldo}')
                                ");

                    $ok=$id_responsable;
                    //$ok=utf8_encode($ok);
                    return $ok;


                }
              else
                {
                    $error='Atención: El Responsable económico ya se encuentra cargado en el sistema';
                    $error=utf8_encode($error);
                    return $error;
                }

            //return $id_gral;



          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar($id,$id_item,$nombre,$apellido,$dni,$domicilio,$telefono,$email,$id_usuario,$nombre_fiscal,$cuit,$tipo_factura,$saldo_inicial,$plan,$facturable,$condicion_iva)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $nombre=utf8_encode($nombre);
            $apellido=utf8_encode($apellido);
            $cuit2=$cuit;
            $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id
                                    FROM responsabes_economicos
                                    WHERE DNI='{$dni}' and B=0 and Id<>'{$id_item}'

                                        ");
            $check_id_gral = \DB::select("
                                    SELECT rf.id
                                    FROM users rf
                                    WHERE rf.DNI='{$dni}'
                                        ");
            $ctrl_dni=count($check_id_gral);

            if(empty($ctrl_dni))
                    {
                        $result2 = \DB::select("
                            SELECT rf.id
                            FROM users rf
                            WHERE rf.email='{$email}'
                        ");
                        $ctrl_mail=count($result2);
                        if(empty($ctrl_mail))
                            {

                            }
                        else
                            {
                                $id_gral = $result2[0]->id;
                                $habilitado2 ++;
                            }
                    }
                else
                    {
                        $id_gral = $check_id_gral[0]->id;
                        $habilitado2 ++;
                    }
            

            $ctrl_e=count($control_existencia);
            if(empty($ctrl_e))
                {
                  $habilitado=0;
                }
              else
                {
                  $habilitado++;
                 
                }
            if(empty($condicion_iva))
                {
                    $condicion_iva=1;
                }
            $seleccion_condicion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT ci.Condicion
                    FROM condiciones_iva ci
                    WHERE ci.ID={$condicion_iva}
                ");
            if(empty($facturable))
                {
                    $facturable='0';
                }
            
            $condicion_iva_texto = $seleccion_condicion[0]->Condicion;
            $condicion_iva_texto = utf8_encode($condicion_iva_texto);
            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                        UPDATE responsabes_economicos
                        SET Facturable='{$facturable}',Condicion_IVA='{$condicion_iva_texto}',ID_Condicion_IVA={$condicion_iva}
                        WHERE Id={$id_item}
                            ");
              if(empty($habilitado))
                {
                    /*$result = \DB::select("
                        SELECT rf.id
                        FROM users rf
                        WHERE rf.DNI='{$dni}' and rf.id<>'{$id_gral}'
                    ");
                    $habilitado2=0;
                    $ctrl_dni=count($result);

                    if(empty($ctrl_dni))
                        {
                            $result2 = \DB::select("
                                SELECT rf.id
                                FROM users rf
                                WHERE rf.email='{$email}' and rf.id<>'{$id_gral}'
                            ");
                            $ctrl_mail=count($result2);
                            if(empty($ctrl_mail))
                                {

                                }
                            else
                                {
                                    $habilitado2 ++;
                                }
                        }
                    else
                        {
                            $habilitado2 ++;
                        }
                    */
                    if(empty($habilitado2))
                        {
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                            UPDATE responsabes_economicos
                            SET Nombre='{$nombre}',Apellido='{$apellido}',DNI='{$dni}',Domicilio='{$domicilio}',Telefono='{$telefono}',Email='{$email}'
                            WHERE Id={$id_item}
                                ");

                            if(empty($plan))
                                {
                                    $plan=99;
                                }
                            $control_plan = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT Id
                                FROM responsables_economicos_mp
                                WHERE Id_Responsable={$id_item} and B=0
                                    ");

                            $cant_control_plan=count($control_plan);
                            if(empty($cant_control_plan))
                                {
                                    $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO responsables_economicos_mp
                                    (ID_Responsable,Tipo_Medio)
                                    VALUES ('{$id_item}','{$plan}')
                                ");
                                }
                            else
                                {
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE responsables_economicos_mp
                                    SET Tipo_Medio='{$plan}'
                                    WHERE Id_Responsable={$id_item} and B=0
                                    ");
                                }
                            //if($cuit2<>'')
                                //{
                                    if(empty($tipo_factura))
                                        {
                                            $tipo_factura=2;
                                        }
                                    $modificar1 = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE responsabes_economicos
                                    SET Nombre_Fiscal='{$nombre_fiscal}',Tipo_Factura='{$tipo_factura}'
                                    WHERE Id={$id_item}
                                    ");
                                    $modificar2 = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE responsabes_economicos
                                    SET CUIT='{$cuit2}'
                                    WHERE Id={$id_item}
                                    ");
                                //}
                            $control_cuenta_corriente = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT Id
                                FROM cuenta_corriente
                                WHERE Id_Responsable={$id_item} and B=0 and Id_Tipo_Comprobante=1
                                    ");

                            $Cant_Registros_CC=count($control_cuenta_corriente);
                            if(empty($Cant_Registros_CC))
                                {
                                    $creo_saldo_inicial = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO cuenta_corriente
                                    (Id_Responsable,Fecha,Id_Tipo_Comprobante,Importe)
                                    VALUES ('{$id_item}','{$FechaActual}','1','{$saldo_inicial}')
                                        ");
                                }
                            else
                                {
                                    /*$actualizo_saldo_inicial = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                    UPDATE cuenta_corriente
                                    SET Importe='{$saldo_inicial}'
                                    WHERE Id_Responsable={$id_item} and B=0 and Id_Tipo_Comprobante=1
                                    ");*/
                                }

                            $ok='El Responsable Económico ha sido modificado con éxito';
                            //$ok=$cuit;
                            return $ok;
                        }
                    else
                        {
                            $ok='El Correo y/o DNI consignados, ya existen para otro Responsable Económico';
                            return $ok;
                        }

                }
            else
                {
                    $ok='El Correo y/o DNI consignados, ya existen para otro Responsable Económico en la plataforma GEO';
                    return $ok;
                }

    }

    public function borrar($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $check_id_gral = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id_General
                                    FROM responsabes_economicos
                                    WHERE Id='{$id_item}'

                                        ");

           $id_gral = $check_id_gral[0]->Id_General;


          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE responsabes_economicos
                          SET B=1,Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                          WHERE ID={$id_item}
                      ");
          $borrado_vinculos = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE alumnos_vinculados
                      SET B=1
                      WHERE Id_Responsable={$id_item}
                  ");
          $bloqueo = \DB::update("
                      UPDATE users
                      SET Estado='B'
                      WHERE ID={$id_gral}
                  ");

          $ok='El responsable Económico ha sido dado de baja con éxito.';
          return $ok;
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
                          SELECT rp.Id,rp.Nombre,rp.Apellido,rp.DNI,rp.Estado,rp.Facturable, Id_General
                          FROM responsabes_economicos rp
                          WHERE rp.B=0
                          ORDER BY rp.Apellido,rp.Nombre
                      ");

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




                    //$Saldo = saldo($id_institucion,$ID_Responsable);
                    //$Saldo = $Saldo["Saldo"];
                    $resultado[$j] = array(
                                              'id' => $ID_Responsable,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'apellido'=> trim(utf8_decode($listado[$j]->Apellido)),
                                              'dni'=> $listado[$j]->DNI,
                                              'vinculos'=> $Cant_Vinculos,
                                              'estado'=> $Estado,
                                              'saldo'=> $Saldito,
                                              'plan'=> $Tipo_Medio,
                                              'facturable'=> $listado[$j]->Facturable,
                                              'id_general'=> $listado[$j]->Id_General



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
                                                $Curso_A=$estudiante["curso"];    
                                                $habil=1;
                                            }


                                    }
                                $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                     
                                                                                      'apellido'=> $Apellido_A,
                                                                                      'nombre'=> $Nombre_A,
                                                                                      'id_alumno'=> $ID_Alumno,
                                                                                      'curso'=> $Curso_A

                                                                                );
                                                                   }
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

          if($id_tipo<=1)
            {
                $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cc.Id, cc.Fecha, cc.Id_Comprobante, cc.Importe, cc.Descripcion, cc.Id_Tipo_Comprobante, cc.Cancelado, cct.Tipo, cct.Clase, cct.Detalle, cc.Facturado
                            FROM cuenta_corriente cc
                            INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                            WHERE cc.B=0 and cc.Id_Responsable={$id_item}
                            ORDER by cc.Fecha, cc.Id, cc.ID_Tipo_Comprobante DESC

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
                   if($Cancelado<=1)
                        {
                            if(empty($Facturado))
                                {
                                    if(($ID_Tipo_Comprobante==2) or ($ID_Tipo_Comprobante==7) or ($ID_Tipo_Comprobante==9) or ($ID_Tipo_Comprobante==10) or ($ID_Tipo_Comprobante==11))
                                        {
                                            //CALCULO EL RESTO POR PAGAR
                                            $Importe_Pagado=0;
                                            $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT ci.Importe
                                            FROM comprobantes_imputaciones ci
                                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento}
                                            ORDER by ci.Id
                                            ");
                                            $Ctrl_Imputaciones=count($c_cancelacion);
                                            if($Ctrl_Imputaciones>=1)
                                                {
                                                    for ($f=0; $f < count($c_cancelacion); $f++)
                                                        {
                                                            $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                                            $Importe_Pagado=$Importe_Pagado+$Importe_Imputacion;
                                                        
                                                        }
                                                }
                                            $Maximo_Ajustable=$Importe-$Importe_Pagado;
                                            $Ajustable=1;

                                        }
                                    else
                                        {
                                            $Ajustable=0;
                                    $Maximo_Ajustable=0;
                                    
                                        }
                                }
                            else
                                {
                                    $Ajustable=0;
                                    $Maximo_Ajustable=0;
                                }
                            
                        }
                    else
                        {
                            $Ajustable=0;
                            $Maximo_Ajustable=0;
                        }


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
                            $ctrl_Comp=count($comprobante);
                            if($ctrl_Comp>=1)
                                {
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
                            SELECT ci.Fecha, ci.Importe, ci.ID_Comprobante, ci.Descripcion
                            FROM comprobantes_imputaciones ci
                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento} and ci.ID_Movimiento=0
                            ORDER by ci.Id
                            ");
                            $Ctrl_Imputaciones=count($c_cancelacion);
                            if($Ctrl_Imputaciones>=1)
                                {
                                    for ($f=0; $f < count($c_cancelacion); $f++)
                                        {
                                            $Descripcion_Imputacion=trim(utf8_decode($c_cancelacion[$f]->Descripcion));
                                            $Fecha_Imputacion=$c_cancelacion[$f]->Fecha;
                                            $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                            if(empty($Descripcion_Imputacion))
                                                {
                                                    $Detalle_Imputacion=trim(utf8_decode('Imputacion por saldo a Favor'));
                                                }
                                            else
                                                {
                                                    $Detalle_Imputacion=$Descripcion_Imputacion;
                                                }
                                            
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
                                                'borrable' => $Borrable,
                                                'ajustable' => $Ajustable,
                                                'maximo_ajustable' => $Maximo_Ajustable,



                                          );

                }

            }
        if($id_tipo==2)
            {
                $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cc.Id, cc.Fecha, cc.Id_Comprobante, cc.Importe, cc.Descripcion, cc.Id_Tipo_Comprobante, cc.Cancelado, cct.Tipo, cct.Clase, cct.Detalle, cc.Facturado
                            FROM cuenta_corriente cc
                            INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                            WHERE cc.B=0 and cc.Id_Responsable={$id_item}
                            ORDER by cc.Fecha, cc.ID_Tipo_Comprobante DESC

                      ");
                $Saldo=0;
                $contador=0;
                for ($j=0; $j < count($listado); $j++)
                        {
                            $mostrar=0;
                            $ID_Tipo_Comprobante=$listado[$j]->Id_Tipo_Comprobante;
                            $ID_Movimiento=$listado[$j]->Id;
                            $Facturado=$listado[$j]->Facturado;
                            $Importe=$listado[$j]->Importe;
                            $Cancelado=$listado[$j]->Cancelado;       
                            $Clase=trim(utf8_decode($listado[$j]->Clase));
                            $ID_Comprobante=$listado[$j]->Id_Comprobante;
                            
                            if(($ID_Tipo_Comprobante==2) or ($ID_Tipo_Comprobante==7) or ($ID_Tipo_Comprobante==8) or ($ID_Tipo_Comprobante==9) or ($ID_Tipo_Comprobante==10) or ($ID_Tipo_Comprobante==11))
                                    {
                                        $mostrar=1;
                                        if(empty($Facturado))
                                            {
                                                $Borrable=1;
                                            }
                                        else
                                            {
                                                $Borrable=0;
                                            }
                                        //REVISION DE PAGOS
                                        $Importe_Pagado_O=0;
                                        $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT ci.Importe
                                            FROM comprobantes_imputaciones ci
                                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento}
                                            ORDER by ci.Id
                                            ");
                                        $Ctrl_Imputaciones=count($c_cancelacion);
                                        if($Ctrl_Imputaciones>=1)
                                            {
                                                for ($f=0; $f < count($c_cancelacion); $f++)
                                                    {
                                                        $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                                        $Importe_Pagado_O=$Importe_Pagado_O+$Importe_Imputacion;
                                                    }
                                            }
                                        $Importe_Pagado_O=round($Importe_Pagado_O,2);
                                        $Saldo_Comprobante=round(($Importe-$Importe_Pagado_O),2);
                                            
                                    }
                            else
                                    {
                                        $Borrable=0;
                                        $Saldo_Comprobante=0;
                                        $Importe_Pagado_O=0;
                                        if($ID_Tipo_Comprobante==4)
                                        {
                                            //REVISO SI TIENE IMPUTACIONES
                                            $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                            SELECT ci.Importe
                                                            FROM comprobantes_imputaciones ci
                                                            WHERE ci.B=0 and ci.ID_Movimiento={$ID_Comprobante}
                                                            ");
                                            $Ctrl_Imputaciones=count($c_cancelacion);
                                            if($Ctrl_Imputaciones>=1)
                                                {
                                                    $mostrar=0;
                                                }
                                            else
                                                {
                                                    $mostrar=1;
                                                }
    
                                        }
                                        if($ID_Tipo_Comprobante==1)
                                        {
                                            $mostrar=1;
                                            $Importe_Pagado_O=0;
                                            $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT ci.Importe
                                                FROM comprobantes_imputaciones ci
                                                WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento}
                                                ORDER by ci.Id
                                                ");
                                            $Ctrl_Imputaciones=count($c_cancelacion);
                                            if($Ctrl_Imputaciones>=1)
                                                {
                                                    for ($f=0; $f < count($c_cancelacion); $f++)
                                                        {
                                                            $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                                            $Importe_Pagado_O=$Importe_Pagado_O+$Importe_Imputacion;
                                                        }
                                                }
                                            $Importe_Pagado_O=round($Importe_Pagado_O,2);
                                            $Saldo_Comprobante=round(($Importe-$Importe_Pagado_O),2);
    
                                        }
                                    }
                   
                           
                   
                            if($Cancelado<=1)
                                    {
                                        if(empty($Facturado))
                                            {
                                                if(($ID_Tipo_Comprobante==2) or ($ID_Tipo_Comprobante==7) or ($ID_Tipo_Comprobante==9) or ($ID_Tipo_Comprobante==10) or ($ID_Tipo_Comprobante==11))
                                                    {
                                                        //CALCULO EL RESTO POR PAGAR
                                                        $Importe_Pagado=0;
                                                        $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                        SELECT ci.Importe
                                                        FROM comprobantes_imputaciones ci
                                                        WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento}
                                                        ORDER by ci.Id
                                                        ");
                                                        $Ctrl_Imputaciones=count($c_cancelacion);
                                                        if($Ctrl_Imputaciones>=1)
                                                            {
                                                                for ($f=0; $f < count($c_cancelacion); $f++)
                                                                    {
                                                                        $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                                                        $Importe_Pagado=$Importe_Pagado+$Importe_Imputacion;
                                                                    
                                                                    }
                                                            }
                                                        $Maximo_Ajustable=$Importe-$Importe_Pagado;
                                                        $Ajustable=1;

                                                    }
                                                else
                                                    {
                                                        $Ajustable=0;
                                                        $Maximo_Ajustable=0;
                                                
                                                    }
                                            }
                                        else
                                            {
                                                $Ajustable=0;
                                                $Maximo_Ajustable=0;
                                            }
                                        
                                    }
                                else
                                    {
                                        $Ajustable=0;
                                        $Maximo_Ajustable=0;
                                    }


                            if($Clase==0)
                                {
                                    $Saldo=$Saldo+$Saldo_Comprobante;
                                    $mostrar=1;
                                }
                            if($Clase==1)
                                {
                                    $Saldo=$Saldo+$Saldo_Comprobante;
                                    $mostrar=1;
                                }
                            if(($Clase==2) and ($mostrar==1))
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
                if(($ID_Tipo_Comprobante==1) or($ID_Tipo_Comprobante==2) or ($ID_Tipo_Comprobante==7) or ($ID_Tipo_Comprobante==9) or ($ID_Tipo_Comprobante==10) or ($ID_Tipo_Comprobante==11))                    
                        {
                            $Descripcion_Cancelacion='';
                            $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ci.Fecha, ci.Importe, ci.ID_Comprobante, ci.Descripcion
                            FROM comprobantes_imputaciones ci
                            WHERE ci.B=0 and ci.ID_Cta_Cte={$ID_Movimiento} and ci.ID_Movimiento=0
                            ORDER by ci.Id
                            ");
                            $Ctrl_Imputaciones=count($c_cancelacion);
                            if($Ctrl_Imputaciones>=1)
                                {
                                    for ($f=0; $f < count($c_cancelacion); $f++)
                                        {
                                            $Descripcion_Imputacion=trim(utf8_decode($c_cancelacion[$f]->Descripcion));
                                            $Fecha_Imputacion=$c_cancelacion[$f]->Fecha;
                                            $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                                            if(empty($Descripcion_Imputacion))
                                                {
                                                    $Detalle_Imputacion=trim(utf8_decode('Imputación por saldo a Favor'));
                                                }
                                            else
                                                {
                                                    $Detalle_Imputacion=$Descripcion_Imputacion;
                                                }
                                            
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
                //$habilitado=1;
                if($mostrar>=1)
                    {
                            $resultado[$contador] = array(
                                //'id' => $ID_Comprobante,
                                'id' => $ID_Movimiento,
                                'fecha'=> $Fecha_Movimiento,
                                'comprobante'=> $Identificacion_Comprobante,
                                'tipo'=> $listado[$j]->Tipo,
                                'clase'=> $Clase,
                                'concepto'=> trim(utf8_decode($listado[$j]->Descripcion,)),
                                'importe' => $Importe,
                                'importe_pagado' => $Importe_Pagado_O,
                                'saldo_comprobante' => $Saldo_Comprobante,
                                'descripcion_cancelacion' => $Descripcion_Cancelacion,
                                'salddo' => $Saldo,
                                'enlace' => $Enlace_Comprobante,
                                'observaciones' => $Observaciones_Comprobante,
                                'cancelado' => $Cancelado,
                                'borrable' => $Borrable,
                                'ajustable' => $Ajustable,
                                'maximo_ajustable' => $Maximo_Ajustable,
                                'mostrar'=> $mostrar



                          );
                        $contador++;
                    }
                

                }

            
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

        public function cargo_gen($id, $id_item, $id_movimiento, $descripcion, $importe, $id_usuario, $fecha, $id_alumno, $interes, $id_empresa)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          if(empty($interes))
            {
                $interes=0;
            }

          $list_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id
                FROM periodos_detalle
                WHERE '{$fecha}' BETWEEN Inicio AND Fin
            ");

          $ID_Periodo=$list_periodo[0]->Id;
          
          //$ID_Periodo=6;
         
          if(empty($id_alumno))
            {
                $id_estu=0;
            }
        else
            {
                $id_estu=$id_alumno;
            }
          //$id_estu=0;
          $ID_Comprobante=0;
          if($id_movimiento==8)
            {
                $cancelacion=2;
            }
        else
            {
                $cancelacion='0';
            }


          $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe,Cancelado,Interes,ID_Empresa,ID_Periodo)
                            VALUES ({$id_item},{$id_estu},'{$fecha}',$id_movimiento,'{$descripcion}',{$ID_Comprobante},{$importe},{$cancelacion},{$interes},{$id_empresa},{$ID_Periodo})
                            ");
                            
        $resultado='El cargo fue agregado con éxito a la cuenta';
          
        return $resultado;

        }

        public function generar_ajuste($id, $id_item, $id_responsable, $importe, $descripcion, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          
        
          $list_periodo = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT Id
                FROM periodos_detalle
                WHERE '{$FechaActual}' BETWEEN Inicio AND Fin
            ");
            $ID_Periodo=$list_periodo[0]->Id;

          
          
          //$ID_Periodo=6;
         
          
          //$ID_Comprobante=0;
          
          $list_estudiante = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT cc.ID_Alumno, cc.ID_Empresa, cc.Importe, cc.Id_Comprobante
                FROM cuenta_corriente cc
                WHERE cc.Id={$id_item}
            ");
          $ctrl_estudiante=count($list_estudiante);
          if(empty($ctrl_estudiante))
            {
                $id_estu=0;
            }
          else
            {
                $id_estu=$list_estudiante[0]->ID_Alumno;
            }
        $id_empresa=$list_estudiante[0]->ID_Empresa;
        $importe_original=$list_estudiante[0]->Importe;
        $id_comp=$list_estudiante[0]->Id_Comprobante;
        if($id_comp>=1)
            {

            }
        else
            {
                $id_comp=0;
            }

        $Importe_Pagado=0;
        $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
        SELECT ci.Importe
        FROM comprobantes_imputaciones ci
        WHERE ci.B=0 and ci.ID_Cta_Cte={$id_item}
        ORDER by ci.Id
        ");
        $Ctrl_Imputaciones=count($c_cancelacion);
        if($Ctrl_Imputaciones>=1)
            {
                for ($f=0; $f < count($c_cancelacion); $f++)
                    {
                        $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                        $Importe_Pagado=$Importe_Pagado+$Importe_Imputacion;
                    
                    }
            }
        $Total_Abonado=$importe+$Importe_Pagado;
        if($Total_Abonado>=$importe_original)
            {
                $cancelacion=2;
            }
        else
            {
                if($Total_Abonado==0)
                    {
                        $cancelacion=0;
                    }
                else
                    {
                        $cancelacion=1;
                    }
            }




        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO cuenta_corriente
                            (Id_Responsable,ID_Alumno,Fecha,Id_Tipo_Comprobante,Descripcion,Id_Comprobante,Importe,Cancelado,Interes,ID_Empresa,ID_Periodo)
                            VALUES ({$id_responsable},{$id_estu},'{$FechaActual}',8,'{$descripcion}',0,{$importe},2,0,{$id_empresa},{$ID_Periodo})
                            ");
                        
        $control_insercion= $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cc.Id
                            FROM cuenta_corriente cc
                            WHERE cc.Id_Responsable={$id_responsable} and cc.Fecha='{$FechaActual}' and cc.Id_Tipo_Comprobante=8 and cc.Importe={$importe}
                            ORDER BY cc.Id desc
                        ");
        $ID_Ajuste=$control_insercion[0]->Id;
        
        $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
        UPDATE cuenta_corriente
        SET Cancelado={$cancelacion}
        WHERE Id={$id_item}
        ");
        
        if($importe<0)
            {
                $importe_para_imputar=$importe * (-1);
            }
        else
            {
                $importe_para_imputar=$importe;
            }
        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                            INSERT INTO comprobantes_imputaciones
                            (Importe,Fecha,Cancela,ID_Cta_Cte,Descripcion,ID_Ajuste,ID_Comprobante)
                            VALUES ({$importe_para_imputar},'{$FechaActual}',1,'{$id_item}','{$descripcion}',{$ID_Ajuste},{$id_comp})
                            ");
        $Importe_Pagado=0;
        $c_cancelacion = $this->dataBaseService->selectConexion($id_institucion)->select("
        SELECT ci.Importe
        FROM comprobantes_imputaciones ci
        WHERE ci.B=0 and ci.ID_Cta_Cte={$id_item}
        ORDER by ci.Id
        ");
        $Ctrl_Imputaciones=count($c_cancelacion);
        if($Ctrl_Imputaciones>=1)
            {
                for ($f=0; $f < count($c_cancelacion); $f++)
                    {
                        $Importe_Imputacion=$c_cancelacion[$f]->Importe;
                        $Importe_Pagado=$Importe_Pagado+$Importe_Imputacion;
                    
                    }
            }
        $Total_Abonado=$importe+$Importe_Pagado;
        if($Total_Abonado>=$importe_original)
            {
                $cancelacion=2;
            }
        else
            {
                if($Total_Abonado==0)
                    {
                        $cancelacion=0;
                    }
                else
                    {
                        $cancelacion=1;
                    }
            }
        $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
        UPDATE cuenta_corriente
        SET Cancelado={$cancelacion}
        WHERE Id={$id_item}
        ");
                            
        $resultado='El Ajuste fue agregado con éxito a la cuenta';
          
        return $resultado;

        }

        public function borrar_gen($id, $id_item, $id_usuario)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          
          $id_institucion=$id;
          
          $control_ajuste = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT cc.Id
                            FROM cuenta_corriente cc
                            WHERE cc.Id_Tipo_Comprobante=8 and cc.Id={$id_item}
                      ");
          $coincidencias_ajuste=count($control_ajuste);
          if(empty($coincidencias_ajuste))
            {

            }
        else
            {
                $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                UPDATE comprobantes_imputaciones
                SET B=1
                WHERE ID_Ajuste={$id_item}
                ");

            }

          $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE cuenta_corriente
                                SET B=1, Fecha_B='{$FechaActual}', Hora_B='{$HoraActual}', ID_B={$id_usuario}
                                WHERE Id={$id_item}
                                ");

                            
        $resultado='El movimiento fue eliminado con éxito da la cuenta';
          
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

        public function condiciones_iva($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ci.Condicion, ci.Codigo, ci.ID
                            FROM condiciones_iva ci
                            WHERE ci.B=0
                            ORDER by ci.ID

                      ");
    
            for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Item=$listado[$j]->ID;
                   $Condicion=$listado[$j]->Condicion;
                   $Codigo=$listado[$j]->Codigo;
                   
                 

            $resultado[$j] = array(
                'id' => $ID_Item,
                'condicion'=> $Condicion,
                'codigo'=> $Codigo

                    );
                }

        return $resultado;

        }

        /*
        public function cargo_gen($id, $id_item, $id_movimiento, $descripcion, $importe, $id_usuario, $fecha)
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
                            VALUES ({$id_item},{$id_estu},'{$fecha}',$id_movimiento,'{$descripcion}',{$ID_Comprobante},{$importe})
                            ");
                            
        $resultado='El cargo fue agregado con éxito a la cuenta';
          
        return $resultado;

        }

        */

}
