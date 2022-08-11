<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class ComunicadosRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    public function lectura_comunicado_a($id)
    {
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $lectura = \DB::connection('mysql2')->update("
                          UPDATE comunicados_detalle
                          SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                          WHERE ID={$id}
                      ");


    }

  public function lectura_comunicado($id,$tipo,$mail)
    {
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $alumno_array=array();
      if($tipo==1)
        {
          $lectura = \DB::connection('mysql2')->update("
                          UPDATE comunicados_detalle
                          SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                          WHERE ID={$id}
                      ");
          $alumno_array = \DB::connection('mysql2')->select("
                                      SELECT ID_Destinatario
                                      FROM comunicados_detalle
                                      WHERE ID={$id}
                                  ");
          $ID_Estudiante = $alumno_array[0]->ID_Destinatario;
        }
      if($tipo==2)
          {
            $lectura = \DB::connection('mysql2')->update("
                            UPDATE notificaciones_enviadas
                            SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                            WHERE ID={$id}
                        ");
            $alumno_array = \DB::connection('mysql2')->select("
                            SELECT ID_Alumno
                            FROM notificaciones_enviadas
                            WHERE ID={$id}
                        ");
            $ID_Estudiante = $alumno_array[0]->ID_Alumno;
          }
          $carpeta = \DB::connection('mysql2')->select("
                          SELECT i.Carpeta
                          FROM institucion i
                          ORDER BY i.ID
                      ");

          //Obtengo el inicio y fin del ciclo lectivo
          $periodos = \DB::connection('mysql2')->select("
                          SELECT bs.ID, bs.ciclo_lectivo, bs.IPT, bs.FTT
                          FROM alumnos a
                          INNER JOIN ciclo_lectivo bs ON a.ID_Nivel=bs.ID_Nivel
                          WHERE a.ID={$ID_Estudiante} and bs.Vigente='SI' and bs.ID_Nivel=a.ID_Nivel
                          ORDER BY bs.ID DESC
                      ");
            /*****************************************************************************************************/

          /*****************************************************************************************************/
          /*******************************************************************************************************/
          //Obtengo las asistencias por periodos
          //$carpeta = array();
          $resultado = array();
          $detalle = array();
          $comunicados = array();
          $notificaciones = array();
          $array= array();
          $total_general = 0.00;
          $total_periodo = 0.00;

          //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

          for ($i=0; $i < count($periodos); $i++)
              {
                $comunicados = \DB::connection('mysql2')->select("
                                SELECT cd.ID, c.Fecha, c.Hora, c.Titulo, c.Descripcion, c.Adjunto, cd.Leido
                                FROM comunicados_detalle cd
                                INNER JOIN alumnos a ON cd.ID_Destinatario=a.ID
                                INNER JOIN comunicados c ON cd.ID_Comunicado=c.ID
                                WHERE a.ID={$ID_Estudiante} AND cd.MailD='{$mail}' AND cd.Tipo<>'D' AND Envio=1
                                ORDER BY c.Fecha desc
                        ");

                $notificaciones = \DB::connection('mysql2')->select("
                                SELECT ne.ID, ne.Fecha, ne.Titulo, ne.Mensaje, ne.Adjunto, ne.Leido
                                FROM notificaciones_enviadas ne
                                INNER JOIN alumnos a ON ne.ID_Alumno=a.ID
                                WHERE ne.ID_Alumno={$ID_Estudiante} and ne.ID_Tipo_Notificacion<>0 AND ne.Enviada=1
                        ");
                if((empty($comunicados)) and (empty($notificaciones)))
                    {
                        $resultado[0]['comunicados_sl'] = array();
                    }
                else
                    {
                      //ELABORO UN ARRAY PARA LUEGO ORDENARLO
                      $dj=0;
                      for ($kla=0; $kla < count($comunicados); $kla++)
                        {
                          $ID_C=$comunicados[$kla]->ID;
                          $Fecha_C=$comunicados[$kla]->Fecha;
                          $Hora_C=$comunicados[$kla]->Hora;
                          $Titulo_C=utf8_decode($comunicados[$kla]->Titulo);
                          $Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($comunicados[$kla]->Descripcion))));
                          $Leido_C=$comunicados[$kla]->Leido;
                          $Adjunto_C=trim(utf8_decode($comunicados[$kla]->Adjunto));
                          $Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($comunicados[$kla]->Adjunto));
                          $Tipo_C=1;

                          $array[$dj]['ID']=$ID_C;
                          $array[$dj]['Fecha']=$Fecha_C;
                          $array[$dj]['Hora']=$Hora_C;
                          $array[$dj]['titulo']=$Titulo_C;
                          $array[$dj]['descripcion']=$Descripcion_C;
                          $array[$dj]['leido']=$Leido_C;
                          $array[$dj]['adjunto']=$Adjunto_C;
                          $array[$dj]['link']=$Link_C;
                          $array[$dj]['tipo']=$Tipo_C;


                          $dj++;
                        //CIERRA FOR SECUNDARIO COMUNICADOS
                        }

                      for ($k=0; $k < count($notificaciones); $k++)
                            {
                              $Lectura=$notificaciones[$k]->Leido;
                              if($Lectura=='NO')
                                {
                                  $Leido = 0;
                                }
                              else
                                {
                                  if($Lectura=='SI')
                                    {
                                      $Leido = 1;
                                    }
                                  else {
                                    $Leido = $Lectura;
                                  }
                                }
                                $ID_C=$notificaciones[$k]->ID;
                                $Fecha_C=$notificaciones[$k]->Fecha;
                                $Hora_C='00:00:00';
                                $Titulo_C=utf8_decode($notificaciones[$k]->Titulo);
                                $Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($notificaciones[$k]->Mensaje))));
                                $Leido_C=$notificaciones[$k]->Leido;
                                $Adjunto_C=trim(utf8_decode($notificaciones[$k]->Adjunto));
                                $Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($notificaciones[$k]->Adjunto));
                                $Tipo_C=2;

                                $array[$dj]['ID']=$ID_C;
                                $array[$dj]['Fecha']=$Fecha_C;
                                $array[$dj]['Hora']=$Hora_C;
                                $array[$dj]['titulo']=$Titulo_C;
                                $array[$dj]['descripcion']=$Descripcion_C;
                                $array[$dj]['leido']=$Leido_C;
                                $array[$dj]['adjunto']=$Adjunto_C;
                                $array[$dj]['link']=$Link_C;
                                $array[$dj]['tipo']=$Tipo_C;

                              $dj++;
                            //CIERRA FOR TERCIARIO NOTIFICACIONES
                          }

                      foreach ($array as $clave => $fila)
                          {
                            $Com[$clave] = $fila['Fecha'];
                            //$Ap[$clave] = $fila['Alumno'];
                          }
                      array_multisort($Com, SORT_DESC, $array);
                      
                      //Armo estructura del JSON en $resultado
                      for ($pepe=0; $pepe < count($array); $pepe++)
                        {
                          $resultado[$i]['comunicados_sl'][$pepe] = array(
                           'id'    => $array[$pepe]['ID'],
                           'fecha' => $array[$pepe]['Fecha'],
                           'hora'  => $array[$pepe]['Hora'],
                           'titulo' => $array[$pepe]['titulo'],
                           'descripcion' =>  $array[$pepe]['descripcion'],
                           //'descripcion' =>  trim(strip_tags(html_entity_decode(utf8_decode($comunicados[$k]->Descripcion))))),
                           'leido'    => $array[$pepe]['leido'],
                           'adjunto'=> $array[$pepe]['adjunto'],
                           'link'  => $array[$pepe]['link'],
                           'tipo'    => $array[$pepe]['tipo']
                          );

                        }
                    }



              //CIERRA FOR PRINCIPAL=i
              }
              return $resultado;
    }


    public function general($id,$mail)
    {

        try
          {
            //Obtengo el nombre de la carpeta

              $carpeta = \DB::connection('mysql2')->select("
                              SELECT i.Carpeta
                              FROM institucion i
                              ORDER BY i.ID
                          ");

              //Obtengo el inicio y fin del ciclo lectivo
              $periodos = \DB::connection('mysql2')->select("
                              SELECT bs.ID, bs.ciclo_lectivo, bs.IPT, bs.FTT
                              FROM alumnos a
                              INNER JOIN ciclo_lectivo bs ON a.ID_Nivel=bs.ID_Nivel
                              WHERE a.ID={$id} and bs.Vigente='SI' and bs.ID_Nivel=a.ID_Nivel
                              ORDER BY bs.ID DESC
                          ");
                /*****************************************************************************************************/

              /*****************************************************************************************************/
              /*******************************************************************************************************/
              //Obtengo las asistencias por periodos
              //$carpeta = array();
              $resultado = array();
              $detalle = array();
              $comunicados = array();
              $notificaciones = array();
              $array= array();
              $total_general = 0.00;
              $total_periodo = 0.00;
              

              //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

              for ($i=0; $i < count($periodos); $i++)
                  {
                    $comunicados = \DB::connection('mysql2')->select("
                                    SELECT cd.ID, c.Fecha, c.Hora, c.Titulo, c.Descripcion, c.Adjunto, cd.Leido
                                    FROM comunicados_detalle cd
                                    INNER JOIN alumnos a ON cd.ID_Destinatario=a.ID
                                    INNER JOIN comunicados c ON cd.ID_Comunicado=c.ID
                                    WHERE a.ID={$id} AND cd.MailD='{$mail}' AND cd.Tipo<>'D' AND Envio=1
                                    ORDER BY c.Fecha desc
                            ");

                    $notificaciones = \DB::connection('mysql2')->select("
                                    SELECT ne.ID, ne.Fecha, ne.Titulo, ne.Mensaje, ne.Adjunto, ne.Leido
                                    FROM notificaciones_enviadas ne
                                    INNER JOIN alumnos a ON ne.ID_Alumno=a.ID
                                    WHERE ne.ID_Alumno={$id} and ne.ID_Tipo_Notificacion<>0 AND ne.Enviada=1
                            ");
                    if((empty($comunicados)) and (empty($notificaciones)))
                        {
                            $resultado[0]['comunicados_sl'] = array();
                        }
                    else
                        {
                          //ELABORO UN ARRAY PARA LUEGO ORDENARLO
                          $dj=0;
                          for ($k=0; $k < count($comunicados); $k++)
                            {
                              $ID_C=$comunicados[$k]->ID;
                              $Fecha_C=$comunicados[$k]->Fecha;
                              $Hora_C=$comunicados[$k]->Hora;
                              $Titulo_C=utf8_decode($comunicados[$k]->Titulo);
                              $Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($comunicados[$k]->Descripcion))));
                              $Leido_C=$comunicados[$k]->Leido;
                              $Adjunto_C=trim(utf8_decode($comunicados[$k]->Adjunto));
                              $Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($comunicados[$k]->Adjunto));
                              $Tipo_C=1;

                              $array[$dj]['ID']=$ID_C;
                              $array[$dj]['Fecha']=$Fecha_C;
                              $array[$dj]['Hora']=$Hora_C;
                              $array[$dj]['titulo']=$Titulo_C;
                              $array[$dj]['descripcion']=$Descripcion_C;
                              $array[$dj]['leido']=$Leido_C;
                              $array[$dj]['adjunto']=$Adjunto_C;
                              $array[$dj]['link']=$Link_C;
                              $array[$dj]['tipo']=$Tipo_C;
                              $array[$dj]['Orden']=$dj;

                              
                              
                              $dj++;
                            //CIERRA FOR SECUNDARIO COMUNICADOS
                            }

                          for ($k=0; $k < count($notificaciones); $k++)
                                {
                                  $Lectura=$notificaciones[$k]->Leido;
                                  if($Lectura=='NO')
                                    {
                                      $Leido = 0;
                                    }
                                  else
                                    {
                                      if($Lectura=='SI')
                                        {
                                          $Leido = 1;
                                        }
                                      else {
                                        $Leido = $Lectura;
                                      }
                                    }
                                    $ID_C=$notificaciones[$k]->ID;
                                    $Fecha_C=$notificaciones[$k]->Fecha;
                                    $Hora_C='';
                                    $Titulo_C=utf8_decode($notificaciones[$k]->Titulo);
                                    $Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($notificaciones[$k]->Mensaje))));
                                    $Leido_C=$Leido;
                                    $Adjunto_C=trim(utf8_decode($notificaciones[$k]->Adjunto));
                                    $Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($notificaciones[$k]->Adjunto));
                                    $Tipo_C=2;

                                    $array[$dj]['ID']=$ID_C;
                                    $array[$dj]['Fecha']=$Fecha_C;
                                    $array[$dj]['Hora']=$Hora_C;
                                    $array[$dj]['titulo']=$Titulo_C;
                                    $array[$dj]['descripcion']=$Descripcion_C;
                                    $array[$dj]['leido']=$Leido_C;
                                    $array[$dj]['adjunto']=$Adjunto_C;
                                    $array[$dj]['link']=$Link_C;
                                    $array[$dj]['tipo']=$Tipo_C;
                                    $array[$dj]['Orden']=$dj;
                                  
                                  $dj++;
                                //CIERRA FOR TERCIARIO NOTIFICACIONES
                              }

                          foreach ($array as $clave => $fila)
                              {
                                $Com[$clave] = $fila['Fecha'];
                                //$Ap[$clave] = $fila['Alumno'];
                              }
                          array_multisort($Com, SORT_DESC, $array);
                          //Armo estructura del JSON en $resultado
                          for ($pepe=0; $pepe < count($array); $pepe++)
                            {
                              $resultado[$i]['comunicados_sl'][$pepe] = array(
                               
                               'id'    => $array[$pepe]['ID'],
                               'fecha' => $array[$pepe]['Fecha'],
                               'hora'  => $array[$pepe]['Hora'],
                               'titulo' => $array[$pepe]['titulo'],
                               'descripcion' =>  $array[$pepe]['descripcion'],
                               //'descripcion' =>  trim(strip_tags(html_entity_decode(utf8_decode($comunicados[$k]->Descripcion))))),
                               'leido'    => $array[$pepe]['leido'],
                               'adjunto'=> $array[$pepe]['adjunto'],
                               'link'  => $array[$pepe]['link'],
                               'orden'    => $array[$pepe]['Orden'],
                               'tipo'    => $array[$pepe]['tipo']
                              );

                            }
                        }



                  //CIERRA FOR PRINCIPAL=i
                  }
                  return $resultado;
          //CIERRA TRY
          }
        catch (Exception $e)
          {
            return $e;
          }
    //CIERRA FUNCION
    }



}
