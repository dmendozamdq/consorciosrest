<?php

namespace App\Repositories;

use App\Models\Alumno;

class CuotasRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    public function lectura_cuotas($id)
    {
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $lectura = \DB::connection('mysql2')->update("
                      UPDATE envio_cuotas_detalle
                      SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                      WHERE ID={$id}
                  ");
    }

    public function general($id,$mail)
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
            $detalle = array();
            $cuotas = array();


            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            for ($i=0; $i < count($periodos); $i++) {


                //CONSULTO COMUNICADOS

                $cuotas= \DB::connection('mysql2')->select("
                                SELECT ce.ID, c.Fecha, c.Titulo, c.Mensaje, ce.Cuota, ce.Leido
                                FROM envio_cuotas_detalle ce
                                INNER JOIN alumnos a ON ce.ID_Destinatario=a.ID
                                INNER JOIN envio_cuotas c ON ce.ID_Cuota=c.ID
                                WHERE a.ID={$id} AND ce.MailD='{$mail}' AND c.Visible=1

                        ");

                //Armo estructura del JSON en $resultado

                for ($k=0; $k < count($cuotas); $k++) {

                    $resultado[$i]['cuotas_sl'][$k] = array(
                                                         'id'    => $cuotas[$k]->ID,
                                                         'fecha' => $cuotas[$k]->Fecha,
                                                         'titulo'=> trim(utf8_decode($cuotas[$k]->Titulo)),
                                                         'descripcion' => $cuotas[$k]->Mensaje,
                                                         //'descripcion'=> trim(utf8_decode($cuotas[$k]->Mensaje)),
                                                         'leido'    => $cuotas[$k]->Leido,
                                                         'adjunto'=> trim(utf8_decode($cuotas[$k]->Cuota)),
                                                         'link'  => 'https://escuelaencasa.com.ar/'.$carpeta[0]->Carpeta.'/cuota_protegida/'.trim(utf8_decode($cuotas[$k]->Cuota))
                                                        );

                }

            }//Cierro FOR principal




/*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }



}
