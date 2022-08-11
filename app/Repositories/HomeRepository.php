<?php

namespace App\Repositories;

use App\Models\Alumno;

class HomeRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    /*public function lectura_comunicado($id)
    {
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $lectura = \DB::connection('mysql2')->update("
                      UPDATE comunicados_detalle
                      SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                      WHERE ID={$id}
                  ");
    }
*/
    public function general($id)
    {

        try {
/*****************************************************************************************************/
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
            $resultado2 = array();
            $detalle = array();
            $detalle2 = array();
            $comunicados = array();
            $total_general = 0.00;
            $total_general2 = 0.00;
            $total_periodo = 0.00;
            $total_periodo2 = 0.00;


            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            for ($i=0; $i < count($periodos); $i++) {

                $ausente   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (3, 6)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                                ");
                $ausente2   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia_grupal a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (3, 6)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");


                $justif   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (4, 7)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");

                $justif2   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia_grupal a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (4, 7)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");

                $retiro   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (5, 10)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");
                $retiro2   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia_grupal a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (5, 10)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");

                $tarde   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (9, 2)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");
                $tarde2   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia_grupal a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (9, 2)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");

                $otros   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (11, 8)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");
                $otros2   = \DB::connection('mysql2')->select("
                                SELECT COUNT(e.Estado) AS 'total', e.Incidencia
                                FROM asistencia_grupal a
                                INNER JOIN estado e ON a.ID_Estado=e.ID
                                WHERE a.ID_Alumnos = {$id} AND e.ID IN (11, 8)
                                AND a.FECHA >= '{$periodos[$i]->IPT}' AND a.FECHA <= '{$periodos[$i]->FTT}'
                            ");
                //CONSULTO EL DETALLE DE INASISTENCIAS
                $detalle = \DB::connection('mysql2')->select("
                                SELECT tas.ID, e.Estado, e.Incidencia, tas.Observaciones, tas.Fecha
                                FROM alumnos a
                                INNER JOIN asistencia tas ON a.ID=tas.ID_Alumnos
                                INNER JOIN estado e ON tas.ID_Estado=e.ID
                                WHERE tas.ID_Alumnos={$id} AND tas.Fecha >= '{$periodos[$i]->IPT}' AND tas.Fecha <= '{$periodos[$i]->FTT}' and tas.ID_Estado<>1
                                ORDER BY tas.Fecha DESC
                                    ");

                //CONSULTO EL DETALLE DE INASISTENCIAS CONTRATURNO
                $detalle2 = \DB::connection('mysql2')->select("
                                SELECT tas.ID, e.Estado, e.Incidencia, tas.Observaciones, tas.Fecha
                                FROM alumnos a
                                INNER JOIN asistencia_grupal tas ON a.ID=tas.ID_Alumnos
                                INNER JOIN estado e ON tas.ID_Estado=e.ID
                                WHERE tas.ID_Alumnos={$id} AND tas.Fecha >= '{$periodos[$i]->IPT}' AND tas.Fecha <= '{$periodos[$i]->FTT}' and tas.ID_Estado<>1
                                ORDER BY tas.Fecha DESC
                                    ");

                //Armo estructura del JSON en $resultado

                $resultado[$i]['ID']      = json_decode($periodos[$i]->ID, true);
                $resultado[$i]['ciclo_lectivo']     = json_decode($periodos[$i]->ciclo_lectivo, true);



                $ausenteTotal  = (isset($ausente[0]->total) ? $ausente[0]->total : 0);
                $justifTotal   = (isset($justif[0]->total)  ? $justif[0]->total  : 0);
                $retiroTotal   = (isset($retiro[0]->total)  ? $retiro[0]->total  : 0);
                $tardeTotal    = (isset($tarde[0]->total)   ? $tarde[0]->total   : 0);
                $otrosTotal    = (isset($otros[0]->total)   ? $otros[0]->total   : 0);

                $ausenteTotal2  = (isset($ausente2[0]->total) ? $ausente2[0]->total : 0);
                $justifTotal2   = (isset($justif2[0]->total)  ? $justif2[0]->total  : 0);
                $retiroTotal2   = (isset($retiro2[0]->total)  ? $retiro2[0]->total  : 0);
                $tardeTotal2    = (isset($tarde2[0]->total)   ? $tarde2[0]->total   : 0);
                $otrosTotal2    = (isset($otros2[0]->total)   ? $otros2[0]->total   : 0);



                $retiroValorIncidencia = (isset($ausente[0]->Incidencia) ? $retiro[0]->Incidencia : 0);
                $tardeValorIncidencia  = (isset($tarde[0]->Incidencia)   ? $tarde[0]->Incidencia : 0);

                $retiroValorIncidencia2 = (isset($ausente2[0]->Incidencia) ? $retiro2[0]->Incidencia : 0);
                $tardeValorIncidencia2  = (isset($tarde2[0]->Incidencia)   ? $tarde2[0]->Incidencia : 0);

                $retiroValor = $retiroTotal * $retiroValorIncidencia;
                $tardeValor  = $tardeTotal  * $tardeValorIncidencia;

                $retiroValor2 = $retiroTotal2 * $retiroValorIncidencia2;
                $tardeValor2  = $tardeTotal2  * $tardeValorIncidencia2;

                $total_periodo = $ausenteTotal + $justifTotal + $retiroValor + $tardeValor;
                $total_periodo2 = $ausenteTotal2 + $justifTotal2 + $retiroValor2 + $tardeValor2;

                $total_general = $total_general + $total_periodo;
                $total_general2 = $total_general2 + $total_periodo2;

                $resultado[$i]['inasistencias']['total_periodo'] = $total_periodo;

                $resultado[$i]['inasistencias']['total_periodo2'] = $total_periodo2;

                /*$resultado[$i]['inasistencias'] = array(
                                                     'total_periodo'  => $total_periodo,
                                                    );

                $resultado[$i]['inasistencias'] = array(
                                                        'total_periodo2'  => $total_periodo2,
                                                    );
                                                    */

                for ($k=0; $k < count($detalle); $k++) {

                      $resultado[$i]['detalle_inasistencias'][$k] = array(
                                                              'id'          => $detalle[$k]->ID,
                                                              'fecha'       => $detalle[$k]->Fecha,
                                                              'tipo'        => trim(utf8_decode($detalle[$k]->Estado)),
                                                              'incidencia'  => $detalle[$k]->Incidencia,
                                                              'observaciones'  => trim(utf8_decode($detalle[$k]->Observaciones))
                                                               );
                }

                for ($k=0; $k < count($detalle2); $k++) {

                    $resultado[$i]['detalle_inasistencias2'][$k] = array(
                                                              'id'          => $detalle2[$k]->ID,
                                                              'fecha'       => $detalle2[$k]->Fecha,
                                                              'tipo'        => trim(utf8_decode($detalle2[$k]->Estado)),
                                                              'incidencia'  => $detalle2[$k]->Incidencia,
                                                              'observaciones'  => trim(utf8_decode($detalle2[$k]->Observaciones))
                                                               );
                }

                /*for ($k=0; $k < count($comunicados); $k++) {

                    $resultado[$i]['comunicados_sl'][$k] = array(
                                                         'id'    => $comunicados[$k]->ID,
                                                         'fecha' => $comunicados[$k]->Fecha,
                                                         'hora'  => $comunicados[$k]->Hora,
                                                         'titulo'=> trim(utf8_decode($comunicados[$k]->Titulo)),
                                                         'descripcion'=> trim(utf8_decode($comunicados[$k]->Descripcion)),
                                                         'adjunto'=> trim(utf8_decode($comunicados[$k]->Adjunto)),
                                                         'link'  => 'https://pesge.com.ar/'.$carpeta[0]->Carpeta.'/comunicados/'.trim(utf8_decode($comunicados[$k]->Adjunto))
                                                        );
                }
                */


            }//Cierro FOR principal


            for ($i=0; $i < count($resultado); $i++) {

                $resultado[$i]['inasistencias']['total_general'] = $total_general;

            }

            for ($i=0; $i < count($resultado); $i++) {

                $resultado[$i]['inasistencias']['total_general2'] = $total_general2;

            }


/*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }



}
