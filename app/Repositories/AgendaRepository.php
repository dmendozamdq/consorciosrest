<?php

namespace App\Repositories;

use App\Models\Alumno;

class AgendaRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    public function general($id)
    {

        try {
/*****************************************************************************************************/
//Obtengo la fecha Actual
                      $FechaActual=date("Y-m-d");

//Obtengo el inicio y fin del ciclo lectivo

                      $periodos = \DB::connection('mysql2')->select("
                                      SELECT bs.ID, bs.ciclo_lectivo, bs.IPT, bs.FPT
                                      FROM alumnos a
                                      INNER JOIN ciclo_lectivo bs ON a.ID_Nivel=bs.ID_Nivel
                                      WHERE a.ID={$id} and bs.Vigente='SI' and bs.ID_Nivel=a.ID_Nivel
                                      ORDER BY bs.ID DESC
                                  ");
          /*****************************************************************************************************/

/*****************************************************************************************************/
/*******************************************************************************************************/
//Obtengo las asistencias por periodos

            $resultado = array();
            $eventos = array();

            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            //for ($i=0; $i < count($periodos); $i++) {

                //CONSULTO EL LOS EVENTOS PROXIMOS
                $eventos = \DB::connection('mysql2')->select("
                                SELECT ac.ID, ac.Fecha_R, ac.Hora_Inicio, ac.Campo_1, ac.Campo_2, ac.Campo_3, m.Materia, cur.Cursos, pf.Apellido, accp.Aleatorio
                                FROM agenda_comun ac
                                INNER JOIN cursos cur ON ac.ID_Curso=cur.ID
                                INNER JOIN alumnos a ON cur.ID=a.ID_Curso
                                INNER JOIN materias m ON ac.ID_Materia=m.ID
                                INNER JOIN personal pf ON m.ID_Personal=pf.ID
                                INNER JOIN agenda_comun_cp accp ON ac.ID=accp.ID_Evento and a.ID=accp.ID_Alumno
                                WHERE a.ID={$id} AND ac.ID_Curso=cur.ID and ac.Fecha_R>='$FechaActual' and ac.B=0
                                ORDER BY ac.Fecha_R
                                    ");

                for ($k=0; $k < count($eventos); $k++) {

                    $resultado[$k] = array(
                                                         'id'    => $eventos[$k]->ID,
                                                         'fecha' => $eventos[$k]->Fecha_R,
                                                         'hora'  => $eventos[$k]->Hora_Inicio,
                                                         'plataforma'=> trim(utf8_decode($eventos[$k]->Campo_1)),
                                                         'datos'=> trim(utf8_decode($eventos[$k]->Campo_2)),
                                                         'link'=> trim(utf8_decode($eventos[$k]->Campo_3)),
                                                         'materia'=> trim(utf8_decode($eventos[$k]->Materia)),
                                                         'curso'=> trim(utf8_decode($eventos[$k]->Cursos)),
                                                         'profesor'=> trim(utf8_decode($eventos[$k]->Apellido)),
                                                         'codigo_personal'=> trim(utf8_decode($eventos[$k]->Aleatorio))


                                                        );
                }

            //}//Cierro FOR principal





/*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }



}
