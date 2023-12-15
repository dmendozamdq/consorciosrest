<?php

namespace App\Repositories;

use App\Models\Alumno;
use App\Services\DataBaseService;

class ReportesRepository
{

    private $Alumno;
    protected $connection = 'mysql2';
    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
    {
        $this->Alumno = $Alumno;
        $this->dataBaseService = $dataBaseService;
    }

    public function general($id, $id_institucion)
    {

        try {

/*****************************************************************************************************/
//Obtengo los periodos

            $periodos = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT bs.ID, bs.Numero, bs.Periodo, bd.Leido, bs.Desde, bs.Hasta
                            FROM alumnos a
                            INNER JOIN boletines_detalle bd ON a.ID=bd.ID_Alumno
                            INNER JOIN boletines_semanales bs ON bd.ID_Boletin=bs.ID
                            WHERE a.ID={$id}
                            GROUP BY bs.ID
                            ORDER BY bs.ID DESC
                        ");


            /* $periodosInasistencias = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT bs.ID, bs.ciclo_lectivo, bs.IPT, bs.FTT
            FROM alumnos a
            INNER JOIN ciclo_lectivo bs ON a.ID_Nivel=bs.ID_Nivel
            WHERE a.ID={$id} and bs.Vigente='SI' and bs.ID_Nivel=a.ID_Nivel
            ORDER BY bs.ID DESC
        "); */
/*****************************************************************************************************/


/*****************************************************************************************************/
//Obtengo las asistencias por periodos

            $resultado = array();
            $detalle = array();
            $total_general = 0.00;
            $total_periodo = 0.00;


            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            for ($i=0; $i < count($periodos); $i++) {

                $ausente   = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (3, 6)
                                AND a.FECHA >= '{$periodos[$i]->Desde}' AND a.FECHA <= '{$periodos[$i]->Hasta}'
                                ");

                $justif   = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (4, 7)
                                AND a.FECHA >= '{$periodos[$i]->Desde}' AND a.FECHA <= '{$periodos[$i]->Hasta}'
                            ");

                $retiro   = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (5, 10)
                                AND a.FECHA >= '{$periodos[$i]->Desde}' AND a.FECHA <= '{$periodos[$i]->Hasta}'
                            ");

                $tarde   = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (9, 2)
                                AND a.FECHA >= '{$periodos[$i]->Desde}' AND a.FECHA <= '{$periodos[$i]->Hasta}'
                            ");

                $otros   = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (11, 8)
                                AND a.FECHA >= '{$periodos[$i]->Desde}' AND a.FECHA <= '{$periodos[$i]->Hasta}'
                            ");

                $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT tas.ID, e.Estado, e.Incidencia, tas.Observaciones, tas.Fecha
                    FROM alumnos a
                    INNER JOIN asistencia tas ON a.ID=tas.ID_Alumnos
                    INNER JOIN estado e ON tas.ID_Estado=e.ID
                    WHERE tas.ID_Alumnos={$id} AND tas.Fecha >= '{$periodos[$i]->Desde}' AND tas.Fecha <= '{$periodos[$i]->Hasta}' and tas.ID_Estado<>1
                    ORDER BY tas.Fecha DESC
                        ");


        $notas = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT m.ID, m.Materia, np.FECHA, np.Calificacion, calif.Tipo, if(np.Promediable = 2, ni.Pub_Cal_NP, np.Promediable) AS Promediable
            FROM alumnos a
            INNER JOIN notas_parciales np ON a.ID=np.ID_Alumno
            INNER JOIN materias m ON np.ID_Materia=m.ID
            INNER JOIN calificaciones calif ON np.ID_Calificacion=calif.ID
            INNER JOIN nivel_parametros ni ON a.ID_Nivel=ni.ID
            WHERE np.ID_Alumno={$id} AND np.FECHA >= '{$periodos[$i]->Desde}' AND np.FECHA <= '{$periodos[$i]->Hasta}'
                ");

        $notas_grupales = $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT mg.ID, mg.Materia, npg.FECHA, npg.Calificacion, calif.Tipo, if(npg.Promediable = 2, ni.Pub_Cal_NP, npg.Promediable) AS Promediable
            FROM alumnos a
            INNER JOIN notas_parciales_grupales npg ON a.ID=npg.ID_Alumno
            INNER JOIN materias_grupales mg ON npg.ID_Materia=mg.ID
            INNER JOIN calificaciones calif ON npg.ID_Calificacion=calif.ID
            INNER JOIN nivel_parametros ni ON a.ID_Nivel=ni.ID
            WHERE npg.ID_Alumno={$id} AND npg.FECHA >= '{$periodos[$i]->Desde}' AND npg.FECHA <= '{$periodos[$i]->Hasta}'
                ");



        $faltas = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT rf.ID, rf.Fecha, f.Falta
                    FROM registro_faltas rf
                    INNER JOIN alumnos a ON rf.ID_Alumno=a.ID
                    INNER JOIN faltas f ON rf.ID_Falta=f.ID
                    WHERE a.ID={$id} AND rf.Fecha >= '{$periodos[$i]->Desde}' AND rf.Fecha <= '{$periodos[$i]->Hasta}'
                ");

                //Armo estructura del JSON en $resultado

                $resultado[$i]['ID']      = json_decode($periodos[$i]->ID, true);
                $resultado[$i]['nro']     = json_decode($periodos[$i]->Numero, true);
                $resultado[$i]['periodo'] = $periodos[$i]->Periodo;
                $resultado[$i]['leido']   = json_decode($periodos[$i]->Leido, true);


                $ausenteTotal  = (isset($ausente[0]->total) ? $ausente[0]->total : 0);
                $justifTotal   = (isset($justif[0]->total)  ? $justif[0]->total  : 0);
                $retiroTotal   = (isset($retiro[0]->total)  ? $retiro[0]->total  : 0);
                $tardeTotal    = (isset($tarde[0]->total)   ? $tarde[0]->total   : 0);
                $otrosTotal    = (isset($otros[0]->total)   ? $otros[0]->total   : 0);

                $retiroValorIncidencia = (isset($ausente[0]->Incidencia) ? $retiro[0]->Incidencia : 0);
                $tardeValorIncidencia  = (isset($tarde[0]->Incidencia)   ? $tarde[0]->Incidencia : 0);

                $retiroValor = $retiroTotal * $retiroValorIncidencia;
                $tardeValor  = $tardeTotal  * $tardeValorIncidencia;

                $total_periodo = $ausenteTotal + $justifTotal + $retiroValor + $tardeValor;

                $total_general = $total_general + $total_periodo;


                $resultado[$i]['inacistencias'] = array(
                                                     'injustificadas' => $ausenteTotal,
                                                     'justificadas'   => $justifTotal,
                                                     'retiros'        => $retiroTotal,
                                                     'llega_tarde'    => $tardeTotal,
                                                     'otras'          => $otrosTotal,
                                                     'total_periodo'  => $total_periodo,
                                                     'total_general'  => 0
                                                    );

                for ($k=0; $k < count($detalle); $k++) {

                        $resultado[$i]['detalle_inasistencias'][$k] = array(                                                                    'id'          => $detalle[$k]->ID,
                        'fecha'       => $detalle[$k]->Fecha,
                        'tipo'        => trim(utf8_decode($detalle[$k]->Estado)),
                        'incidencia'  => $detalle[$k]->Incidencia,
                        'observaciones'  => trim(utf8_decode($detalle[$k]->Observaciones))
                    );
                }


                for ($k=0; $k < count($notas); $k++) {

                    $resultado[$i]['calificaciones'][$k] = array(
                                                         'id'          => $notas[$k]->ID,
                                                         'nombre'      => trim(utf8_decode($notas[$k]->Materia)),
                                                         'fecha'       => $notas[$k]->FECHA,
                                                         'calif'       => $notas[$k]->Calificacion,
                                                         'promediable' => $notas[$k]->Promediable,
                                                         'Tipo'        => trim(utf8_decode($notas[$k]->Tipo))
                                                         );


                }

                for ($k=0; $k < count($notas_grupales); $k++) {

                    $resultado[$i]['calificaciones'][$k] = array(
                                                         'id'          => $notas_grupales[$k]->ID,
                                                         'nombre'      => trim(utf8_decode($notas_grupales[$k]->Materia)),
                                                         'fecha'       => $notas_grupales[$k]->FECHA,
                                                         'calif'       => $notas_grupales[$k]->Calificacion,
                                                         'promediable' => $notas_grupales[$k]->Promediable,
                                                         'Tipo'        => trim(utf8_decode($notas[$k]->Tipo))
                                                         );


                }


                for ($k=0; $k < count($faltas); $k++) {

                    $resultado[$i]['sitdeconflic'][$k] = array(
                                                         'id'    => $faltas[$k]->ID,
                                                         'fecha' => $faltas[$k]->Fecha,
                                                         'desc'  => $faltas[$k]->Falta
                                                        );
                }



            }//Cierro FOR principal


            for ($i=0; $i < count($resultado); $i++) {

                $resultado[$i]['inacistencias']['total_general'] = $total_general;

            }


/*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function lectura_informe($id,$mail, $id_institucion)
      {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $FechaActual=date("Y-m-d");
        $HoraActual=date("H:i:s");
        $alumno_array=array();
        $lectura = $this->dataBaseService->selectConexion($id_institucion)->update("
                            UPDATE boletines_detalle
                            SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                            WHERE ID={$id}
                        ");
        $alumno_array = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT ID_Destinatario
                                        FROM boletines_detalle
                                        WHERE ID={$id}
                                    ");
        $ID_Estudiante = $alumno_array[0]->ID_Destinatario;
        $carpeta = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT i.Carpeta
                            FROM institucion i
                            ORDER BY i.ID
                        ");

        //Obtengo el inicio y fin del ciclo lectivo
        $periodos = $this->dataBaseService->selectConexion($id_institucion)->select("
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
            $informes = array();
            $array= array();
            $datos_boletin= array();


            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            for ($i=0; $i < count($periodos); $i++)
                {
                  $informes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT bd.ID, bd.ID_Boletin, bd.Aleatorio, bd.Tipo_Envio, bd.Leido, bd.Archivo
                                  FROM boletines_detalle bd
                                  INNER JOIN alumnos a ON bd.ID_Destinatario=a.ID
                                  WHERE a.ID={$ID_Estudiante} AND bd.MailD='{$mail}' AND bd.Envio=1
                                  ORDER BY bd.ID desc
                          ");
                          //INNER JOIN boletines_semanales bs ON bd.ID_Boletin=bs.ID

                  if(empty($informes))
                      {
                          $resultado[0]['comunicados_sl'] = array();
                      }
                  else
                      {
                        //ELABORO UN ARRAY PARA LUEGO ORDENARLO
                        $dj=0;
                        for ($k=0; $k < count($informes); $k++)
                          {
                            $ID_Item=$informes[$k]->ID;
                            $ID_Informe=$informes[$k]->ID_Boletin;
                            $Tipo_Envio=$informes[$k]->Tipo_Envio;
                            $Leido_C=$informes[$k]->Leido;
                            $Aleatorio_C=$informes[$k]->Aleatorio;
                            $Adjunto_C=trim(utf8_decode($informes[$k]->Archivo));
                            if($Tipo_Envio==1)
                              {
                                //LE DEBE PEGAR A LA API DE INFORMES COMO YA ESTABA
                                $datos_boletin = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT Fecha, Periodo, Texto_Adicional
                                                FROM boletines_semanales
                                                WHERE ID={$ID_Informe}
                                                ORDER BY Fecha desc
                                        ");
                                $Fecha_C=$datos_boletin[0]->Fecha;
                                $Titulo_C=utf8_decode($datos_boletin[0]->Periodo);
                                $Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($datos_boletin[0]->Texto_Adicional))));
                                $Link_C='';
                              }
                              if($Tipo_Envio==2)
                                {
                                  //PROCESO DE VALORACION PEDAGOGICA
                                }
                                if($Tipo_Envio==3)
                                  {
                                    //EVALUACION CUALITATIVA
                                    $datos_boletin = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                    SELECT Fecha, Titulo
                                                    FROM evaluaciones_cualitativas
                                                    WHERE ID={$ID_Informe}
                                                    ORDER BY Fecha desc
                                            ");
                                    $Fecha_C=$datos_boletin[0]->Fecha;
                                    $Titulo_C=utf8_decode($datos_boletin[0]->Titulo);
                                    $Descripcion_C='';
                                    //$Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($datos_boletin[0]->Texto_Adicional))));
                                    $Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/reportes/api_informe_cualitativo.php?code='.trim($Aleatorio_C);
                                    //$Link_C='';
                                  }
                                  if($Tipo_Envio==4)
                                    {

                                    }
                                    if($Tipo_Envio==5)
                                      {

                                      }
                                      if($Tipo_Envio==6)
                                        {

                                        }
                                        if(($Tipo_Envio==7) or ($Tipo_Envio==8))
                                          {

                                          }
                            //$Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($comunicados[$k]->Adjunto));


                            $array[$dj]['ID']=$ID_Item;
                            $array[$dj]['Fecha']=$Fecha_C;
                            $array[$dj]['titulo']=$Titulo_C;
                            $array[$dj]['descripcion']=$Descripcion_C;
                            $array[$dj]['leido']=$Leido_C;
                            $array[$dj]['link']=$Link_C;


                            $dj++;
                          //CIERRA FOR SECUNDARIO COMUNICADOS
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
                             'titulo' => $array[$pepe]['titulo'],
                             'descripcion' =>  $array[$pepe]['descripcion'],
                             //'descripcion' =>  trim(strip_tags(html_entity_decode(utf8_decode($comunicados[$k]->Descripcion))))),
                             'leido'    => $array[$pepe]['leido'],
                             'link'  => $array[$pepe]['link']

                            );

                          }
                      }



                //CIERRA FOR PRINCIPAL=i
                }
            return $resultado;
      }

  public function lista_informes($id,$mail, $id_institucion)
    {

        try
          {
            //Obtengo el nombre de la carpeta

              $carpeta = $this->dataBaseService->selectConexion($id_institucion)->select("
                              SELECT i.Carpeta
                              FROM institucion i
                              ORDER BY i.ID
                          ");

              //Obtengo el inicio y fin del ciclo lectivo
              $periodos = $this->dataBaseService->selectConexion($id_institucion)->select("
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
              $informes = array();
              $array= array();
              $datos_boletin= array();


              //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

              for ($i=0; $i < count($periodos); $i++)
                  {
                    $informes = $this->dataBaseService->selectConexion($id_institucion)->select("
                                    SELECT bd.ID, bd.ID_Boletin, bd.Aleatorio, bd.Tipo_Envio, bd.Leido, bd.Archivo
                                    FROM boletines_detalle bd
                                    INNER JOIN alumnos a ON bd.ID_Destinatario=a.ID
                                    WHERE a.ID={$id} AND bd.MailD='{$mail}' AND bd.Envio=1
                                    ORDER BY bd.ID desc
                            ");
                            //INNER JOIN boletines_semanales bs ON bd.ID_Boletin=bs.ID

                    if(empty($informes))
                        {
                            $resultado[0]['comunicados_sl'] = array();
                        }
                    else
                        {
                          //ELABORO UN ARRAY PARA LUEGO ORDENARLO
                          $dj=0;
                          for ($k=0; $k < count($informes); $k++)
                            {
                              $ID_Item=$informes[$k]->ID;
                              $ID_Informe=$informes[$k]->ID_Boletin;
                              $Tipo_Envio=$informes[$k]->Tipo_Envio;
                              $Leido_C=$informes[$k]->Leido;
                              $Aleatorio_C=$informes[$k]->Aleatorio;
                              $Adjunto_C=trim(utf8_decode($informes[$k]->Archivo));
                              if($Tipo_Envio==1)
                                {
                                  //LE DEBE PEGAR A LA API DE INFORMES COMO YA ESTABA
                                  $datos_boletin = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                  SELECT Fecha, Periodo, Texto_Adicional
                                                  FROM boletines_semanales
                                                  WHERE ID={$ID_Informe}
                                                  ORDER BY Fecha desc
                                          ");
                                  $Fecha_C=$datos_boletin[0]->Fecha;
                                  $Titulo_C=utf8_decode($datos_boletin[0]->Periodo);
                                  $Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($datos_boletin[0]->Texto_Adicional))));
                                  $Link_C='';
                                }
                                if($Tipo_Envio==2)
                                  {
                                    //PROCESO DE VALORACION PEDAGOGICA
                                  }
                                  if($Tipo_Envio==3)
                                    {
                                      //EVALUACION CUALITATIVA
                                      $datos_boletin = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                      SELECT Fecha, Titulo
                                                      FROM evaluaciones_cualitativas
                                                      WHERE ID={$ID_Informe}
                                                      ORDER BY Fecha desc
                                              ");
                                      $Fecha_C=$datos_boletin[0]->Fecha;
                                      $Titulo_C=utf8_decode($datos_boletin[0]->Titulo);
                                      $Descripcion_C='';
                                      //$Descripcion_C=trim(strip_tags(html_entity_decode(utf8_decode($datos_boletin[0]->Texto_Adicional))));
                                      $Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/reportes/api_informe_cualitativo.php?code='.trim($Aleatorio_C);
                                      //$Link_C='';
                                    }
                                    if($Tipo_Envio==4)
                                      {

                                      }
                                      if($Tipo_Envio==5)
                                        {

                                        }
                                        if($Tipo_Envio==6)
                                          {

                                          }
                                          if(($Tipo_Envio==7) or ($Tipo_Envio==8))
                                            {

                                            }
                              //$Link_C='https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($comunicados[$k]->Adjunto));


                              $array[$dj]['ID']=$ID_Item;
                              $array[$dj]['Fecha']=$Fecha_C;
                              $array[$dj]['titulo']=$Titulo_C;
                              $array[$dj]['descripcion']=$Descripcion_C;
                              $array[$dj]['leido']=$Leido_C;
                              $array[$dj]['link']=$Link_C;


                              $dj++;
                            //CIERRA FOR SECUNDARIO COMUNICADOS
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
                               'titulo' => $array[$pepe]['titulo'],
                               'descripcion' =>  $array[$pepe]['descripcion'],
                               //'descripcion' =>  trim(strip_tags(html_entity_decode(utf8_decode($comunicados[$k]->Descripcion))))),
                               'leido'    => $array[$pepe]['leido'],
                               'link'  => $array[$pepe]['link']

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
