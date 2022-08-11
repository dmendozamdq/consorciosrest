<?php

namespace App\Repositories;

use App\Models\Alumno;

class RiteRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    public function lectura_rite($id)
    {
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $lectura = \DB::connection('mysql2')->update("
                      UPDATE boletines_detalle
                      SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}',Acceso=1,Fecha_Acceso='{$FechaActual}',Hora_Acceso='{$HoraActual}'
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
            $boletines = array();


            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            for ($i=0; $i < count($periodos); $i++) {


                //CONSULTO COMUNICADOS

                $boletines= \DB::connection('mysql2')->select("
                                SELECT bd.ID, rt.Titulo, rt.Descripcion, bd.Leido, bd.ID_Boletin
                                FROM boletines_detalle bd
                                INNER JOIN alumnos a ON bd.ID_Destinatario=a.ID
                                INNER JOIN rite rt ON bd.ID_Boletin=rt.ID
                                WHERE a.ID={$id} AND bd.MailD='{$mail}' AND bd.Tipo_Envio=2 AND rt.Visible=1 and rt.Tipo=1

                        ");

                //Armo estructura del JSON en $resultado

                for ($k=0; $k < count($boletines); $k++) {

                    $resultado[$i]['boletines'][$k] = array(
                                                         'id'    => $boletines[$k]->ID,
                                                         'id_rite'    => $boletines[$k]->ID_Boletin,
                                                         'titulo'=> trim(utf8_decode($boletines[$k]->Titulo)),
                                                         'descripcion' => $boletines[$k]->Descripcion,
                                                         //'descripcion'=> trim(utf8_decode($cuotas[$k]->Mensaje)),
                                                         'leido'    => $boletines[$k]->Leido,
                                                         //'adjunto'=> trim(utf8_decode($boletines[$k]->Archivo)),
                                                         //'link'  => 'https://pesge.com.ar/'.$carpeta[0]->Carpeta.'/reportes/ver_rite_alumno.php?id='.trim(utf8_decode($id)).'&idp='.$boletines[$k]->ID))

                                                         'link'  => 'https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/reportes/ver_rite_alumno.php?id='.$id.'&idp='.$boletines[$k]->ID_Boletin
                                                         //'link'  => 'https://pesge.com.ar/'.$carpeta[0]->Carpeta.'/boletines/'.trim(utf8_decode($boletines[$k]->Archivo))
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
