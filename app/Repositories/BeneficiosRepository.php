<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class BeneficiosRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
        {
            $this->Alumno = $Alumno;
            $this->dataBaseService = $dataBaseService;
        }


        public function agregar($id,$nombre,$id_categoria,$tipo,$descuento,$aplica,$conceptos)
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
                                      FROM beneficios
                                      WHERE Nombre='{$nombre}' and B=0

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

                        $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                        INSERT INTO beneficios
                                        (nombre,Tipo_Descuento,Descuento,ID_Categoria,Aplica_Total)
                                        VALUES
                                        ('{$nombre}','{$tipo}','{$descuento}','{$id_categoria}','{$aplica}')
                                    ");
                        $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->Select("
                                        SELECT  bf.Id
                                        FROM beneficios bf
                                        WHERE bf.B=0
                                        ORDER BY bf.Id desc limit 1
                                    ");
                        $id_insertado = $verifico_insercion[0]->Id;

                        if($aplica==0)
                            {
                                foreach($conceptos as $Linea)
                                 {
                                        $id_concepto=$Linea['id_concepto'];
                                        $creo_beneficio_detalle = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                        INSERT INTO beneficios_detalles_aplicacion
                                        (Id_Beneficio, Id_Concepto)
                                        VALUES
                                        ('{$id_insertado}','{$id_concepto}')
                                    ");
                                }
                            }
                        if($id_insertado>=1)
                            {
                                $ok='El Beneficio ha sido agregado con éxito';
                                $ok=utf8_encode($ok);
                                return $ok;
                            }
                        else
                            {

                                $ok='Atención: El beneficio no ha podido ser cargado';
                        $ok=utf8_encode($ok);
                        return $ok;
                            }

                    }
                  else
                    {
                        $error='Atención: El Beneficio ya se encuentra cargado en el sistema';
                        $error=utf8_encode($error);
                        return $error;
                    }

                //return $id_gral;



              } catch (\Exception $e) {
                  return $e;
              }
            }


    public function modificar($id,$id_item,$nombre,$id_categoria,$tipo,$descuento,$aplica,$conceptos)
    {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $nombre=utf8_encode($nombre);
            $control_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id
                                    FROM beneficios
                                    WHERE nombre='{$nombre}' and B=0 and Id<>'{$id_item}'

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
                            $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                            UPDATE beneficios
                            SET Nombre='{$nombre}',Id_Categoria='{$id_categoria}',Tipo_Descuento='{$tipo}',Descuento='{$descuento}',Aplica_Total='{$aplica}'
                            WHERE Id={$id_item}
                                ");

                            if($aplica==0)
                                {
                                    $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                                    UPDATE beneficios_detalles_aplicacion
                                    SET B=1, Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                                    WHERE Id_Beneficio={$id_item} and B=0
                                        ");
                                    foreach($conceptos as $Linea)
                                         {
                                                $id_concepto=$Linea['id_concepto'];
                                                $creo_beneficio_detalle = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                                INSERT INTO beneficios_detalles_aplicacion
                                                (Id_Beneficio, Id_Concepto)
                                                VALUES
                                                ('{$id_item}','{$id_concepto}')
                                                    ");
                                        }

                                }

                            $ok='El Beneficio fue modificado con éxito';
                            return $ok;
                }
            else
                {
                    $ok='Atención: El beneficio ya existe en el sistema.';
                    return $ok;
                }

    }

    public function borrar($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;


          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE beneficios
                          SET B=1,Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                          WHERE Id={$id_item}
                      ");
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE beneficios_detalles_aplicacion
                      SET B=1,Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                      WHERE Id_Beneficio={$id_item}
                  ");
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE beneficios_asignaciones
                      SET B=1,Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                      WHERE Id_Beneficio={$id_item}
                  ");

          $ok='El beneficio ha sido dado de baja con éxito.';
          return $ok;
        }

    public function listado($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();



          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT bn.Id, bn.Nombre, bn.Tipo_Descuento, bn.Descuento, bn.Id_Categoria, bn.Aplica_Total, bn.Estado
                          FROM beneficios bn
                          WHERE bn.B=0
                          ORDER BY bn.Nombre
                      ");


          for ($j=0; $j < count($listado); $j++)
                {
                   $ID_Tipo_Descuento = $listado[$j]->Tipo_Descuento;
                   $ID_Categoria = $listado[$j]->Id_Categoria;
                   $ID_Estado = $listado[$j]->Estado;
                    $ID_Beneficio = $listado[$j]->Id;
                   if($ID_Tipo_Descuento==1)
                        {
                            $Tipo_Descuento='Porcentaje';
                        }
                    if($ID_Tipo_Descuento==2)
                        {
                            $Tipo_Descuento='Monto Fijo';
                        }
                    if($ID_Categoria==1)
                        {
                            $Categoria='Beca';
                        }
                    if($ID_Categoria==2)
                        {
                            $Categoria='Descuento';
                        }
                   $otorgamientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT ba.Id
                          FROM beneficios_asignaciones ba
                          WHERE ba.B=0 and ba.Id_Beneficio={$ID_Beneficio}

                      ");
                    $Cant_Otorgamientos = count($otorgamientos);
                    $Aplica_Tot=$listado[$j]->Aplica_Total;
                    $resultado[$j] = array(
                                              'id' => $ID_Beneficio,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'categoria'=> trim(utf8_decode($Categoria)),
                                              'id_categoria'=>$ID_Categoria,
                                              'tipo_descuento'=> $Tipo_Descuento,
                                              'id_tipo_descuento'=> $ID_Tipo_Descuento,
                                              'descuento'=> $listado[$j]->Descuento,
                                              'aplica_total'=> $Aplica_Tot,
                                              'estado' => $listado[$j]->Estado,
                                              'otorgamientos' => $Cant_Otorgamientos
                                          );
                    if($Aplica_Tot==1)
                        {
                            $resultado[$j]['conceptos_alcanzados'][0] = array();
                        }
                    else
                        {
                            $alcance = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT bda.Id, bda.Id_Concepto
                                                FROM beneficios_detalles_aplicacion bda
                                                WHERE bda.B=0 and bda.Id_Beneficio={$ID_Beneficio}

                                            ");
                            $Cant_Alcances = count($alcance);
                            if(empty($Cant_Alcances))
                                {
                                    $resultado[$j]['conceptos_alcanzados'][0] = array();
                                }
                            else
                                {
                                    for ($k=0; $k < count($alcance); $k++)
                                        {
                                            $resultado[$j]['conceptos_alcanzados'][$k] = array(
                                                'id'=> $alcance[$k]->Id,
                                                'id_concepto'=> $alcance[$k]->Id_Concepto



                                          );
                                        }
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
                            SELECT be.*
                            FROM beneficios be
                            WHERE be.B=0 and be.Id={$id_item}

                      ");

          for ($j=0; $j < count($listado); $j++)
                {
                    $ID_Tipo_Descuento = $listado[$j]->Tipo_Descuento;
                    $ID_Categoria = $listado[$j]->Id_Categoria;
                    $ID_Estado = $listado[$j]->Estado;
                     $ID_Beneficio = $listado[$j]->Id;
                    if($ID_Tipo_Descuento==1)
                         {
                             $Tipo_Descuento='Porcentaje';
                         }
                     if($ID_Tipo_Descuento==2)
                         {
                             $Tipo_Descuento='Monto Fijo';
                         }
                     if($ID_Categoria==1)
                         {
                             $Categoria='Beca';
                         }
                     if($ID_Categoria==2)
                         {
                             $Categoria='Descuento';
                         }
                    $resultado[$j] = array(
                            'id' => $id_item,
                            'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                            'categoria'=> trim(utf8_decode($Categoria)),
                            'id_categoria'=>$ID_Categoria,
                            'tipo_descuento'=> $Tipo_Descuento,
                            'id_tipo_descuento'=> $ID_Tipo_Descuento,
                            'descuento'=> $listado[$j]->Descuento,
                            'aplica_total'=> $listado[$j]->Aplica_Total,
                            'estado' => $listado[$j]->Estado


                        );

                   $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT ba.Id, ba.Id_Alumno, ba.Fecha_Alta, ba.Fecha_Vencimiento, ba.Estado
                            FROM beneficios_asignaciones ba
                            WHERE ba.B=0 and ba.Id_Beneficio={$id_item}
                            ORDER BY ba.Id
                        ");
                    $cant_vinculos=count($detalle);
                    //$ruta_api2='http://apirest.geoeducacion.com.ar/api/responsables/estudiantes_listado/'.$id_institucion;
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
                    if($cant_vinculos>=1)
                        {
                            /*$ruta_api='http://apirest.geoeducacion.com.ar/api/responsables/estudiantes_listado/'.$id_institucion;
                            $headers = [
                                'Content-Type: application/json',
                            ];
                            $curl = curl_init();
                            //$ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno;
                            curl_setopt($curl, CURLOPT_URL,  $ruta_api);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl, CURLOPT_HTTPGET,true);
                            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($curl, CURLOPT_POST, false);
                            //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
                            //usleep(100000);
                            $data = curl_exec($curl);
                            curl_close($curl);
                            $data = json_decode($data, true);
                            $datos_alumno = $data['data'];
                            //$datos_alumno = $data['data'];
                            */
                            $contador=0;
                            for ($k=0; $k < count($detalle); $k++) {

                                    $ID_Alumno = $detalle[$k]->Id_Alumno;
                                    /*
                                    $ruta2_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno;
                                    $ruta_api='https://apirest.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno;

                                    if($data = json_decode( file_get_contents($ruta_api), true))
                                        {

                                        }
                                    else
                                        {
                                            $data = json_decode( file_get_contents($ruta2_api), true);
                                        }
                                  //$data = json_decode($data, true);
                                    //$data = json_decode( file_get_contents($ruta_api), true);
                                    //echo $data['nickname'];
          */
                                    /*
                                    $headers = [
                                        'Content-Type: application/json',
                                    ];
                                    $curl = curl_init();
                                    $ruta_api='http://apidemo.geoeducacion.com.ar/api/facturacion/estudiante/'.$id_institucion.'?id='.$ID_Alumno;
                                    curl_setopt($curl, CURLOPT_URL,  $ruta_api);
                                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($curl, CURLOPT_HTTPGET,true);
                                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($curl, CURLOPT_POST, false);
                                    //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
                                    //usleep(100000);
                                    $data = curl_exec($curl);
                                    curl_close($curl);
                                    $data = json_decode($data, true);
                                    $datos_alumno = $data['data'];
                                    */
                                    $habil=0;
                                    foreach($datos_alumnos as $estudiante) {

                                        $id_estudiante=$estudiante["id"];
                                        if($id_estudiante==$ID_Alumno)
                                            {
                                                $Nombre_A=$estudiante["nombre"];
                                                $Apellido_A=$estudiante["apellido"];
                                                $Curso_A=$estudiante["curso"];
                                              //  $ID_Curso_A=$estudiante["id_curso"];
                                                $ID_Curso_A=1;
                                                //$ID_Nivel_A=$estudiante["id_nivel"];
                                                $ID_Nivel_A=1;
                                                $habil=1;
                                            }


                                    }

                                    $resultado[$j]['otorgamientos'][$k] = array(
                                                                        'id'=> $detalle[$k]->Id,
                                                                        'id_alumno'=> $ID_Alumno,
                                                                        'apellido'=> $Apellido_A,
                                                                        'nombre'=> $Nombre_A,
                                                                        'id_curso' => $ID_Curso_A,
                                                                        'curso' => $Curso_A,
                                                                        'id_nivel' => $ID_Nivel_A,
                                                                        'fecha_otorgamiento' => $detalle[$k]->Fecha_Alta,
                                                                        'fecha_vencimiento' => $detalle[$k]->Fecha_Vencimiento,
                                                                        'estado' => $detalle[$k]->Estado


                                                                  );
                                                     }
                        }
                    else
                        {
                            $resultado[$j]['otorgamientos'] = array();
                        }
                }



          return $resultado;
        }

        public function ver_alumno($id,$id_item)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ba.Id, be.Nombre
                        FROM beneficios_asignaciones ba
                        INNER JOIN beneficios be ON ba.Id_Beneficio=be.Id
                        WHERE ba.B=0 and ba.Id_Alumno={$id_item}

                    ");
          $Control_Beneficio=count($listado);
          if($Control_Beneficio>=1)
            {
                for ($j=0; $j < count($listado); $j++)
                    {
                        $ID_Beneficio = $listado[$j]->Id;
                        $resultado[$j] = array(
                            'id' => $ID_Beneficio,
                            'nombre'=> trim(utf8_decode($listado[$j]->Nombre))
                            

                        );
                    }
            }


          



          return $resultado;
        }


        public function asignar($id,$id_item,$id_alumno,$id_usuario,$vencimiento)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $check_existencia = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT Id
                                    FROM beneficios_asignaciones
                                    WHERE Id_Beneficio='{$id_item}' and Id_Alumno='{$id_alumno}' and B=0
                               ");

           $control_existencia=count($check_existencia);
           if(empty($control_existencia))
            {
                //LO AGREGO
                $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO beneficios_asignaciones
                                    (Id_Beneficio,Id_Alumno,Id_Usuario,Fecha_Vencimiento)
                                    VALUES ('{$id_item}','{$id_alumno}','{$id_usuario}','{$vencimiento}')
                                ");
                $resultado='El beneficio se ha asignado con éxito al estudiante.';
            }
            else
            {
                $resultado='Atención: El Estudiante ya encuentra con el beneficio asignado.';
            }

            return $resultado;

        }

        public function modificar_asignacion($id,$id_item,$vencimiento)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;
            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE beneficios_asignaciones
                                SET Fecha_Vencimiento='{$vencimiento}'
                                WHERE Id={$id_item}
                                ");
            $resultado='La asignación del Beneficio ha sido modificada con éxito.';


            return $resultado;

        }

        public function borrar_asignacion($id,$id_item)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            //LO ACTUALIZO
            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE beneficios_asignaciones
                                SET B=1, Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                                WHERE Id={$id_item}
                                ");
            $resultado='El beneficio se ha desasignado del estudiante con éxito.';

            return $resultado;

        }

        public function suspender_asignacion($id,$id_item)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            //LO ACTUALIZO
            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE beneficios_asignaciones
                                SET Estado=2
                                WHERE Id={$id_item}
                                ");
            $resultado='El beneficio se ha suspendido para el estudiante con éxito.';

            return $resultado;

        }

        public function reactivar_asignacion($id,$id_item)
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $id_institucion=$id;

            //LO ACTUALIZO
            $creo_vinculacion = $this->dataBaseService->selectConexion($id_institucion)->Update("
                                UPDATE beneficios_asignaciones
                                SET Estado=1
                                WHERE Id={$id_item}
                                ");
            $resultado='El beneficio se ha reactivado para el estudiante con éxito.';

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
                   $Clase=$listado[$j]->Clase;
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
                            FROM comprobante co
                            WHERE co.B=0 and co.Id={$ID_Comprobante}
                            ORDER by cc.Id

                                 ");
                            $Identificacion_Comprobante=$comprobante[0]->Identificacion;
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
                                                'id' => $listado[$j]->Id,
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




}
