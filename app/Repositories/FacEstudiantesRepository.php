<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class FacEstudiantesRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

 public function listado_estudiantes($id)
    {

        try
          {
            $result = \DB::connection('mysql2')->select("
                        SELECT  a.ID, a.Nombre, a.Apellido, a.DNI, a.ID_Situacion, s.Situacion, c.Cursos, n.Nivel
                        FROM alumnos a
                        INNER JOIN cursos c ON a.ID_Curso=c.ID
                        INNER JOIN nivel n ON c.ID_Nivel=n.ID
                        INNER JOIN situacion s ON a.ID_Situacion=s.ID
                        WHERE a.ID_Nivel={$id}
                        GROUP BY c.ID
                        ORDER BY a.Orden
                        ");

            for ($k=0; $k < count($result); $k++) {

            $resultado[0]['estudiantes'][$k] = array(
                                                        'id'    => $result[$k]->ID,
                                                        'nombre'=> trim(utf8_decode($result[$k]->Nombre)),
                                                        'apellido'=> trim(utf8_decode($result[$k]->Apellido)),
                                                        'dni' => $result[$k]->DNI,
                                                        'curso'=> trim(utf8_decode($result[$k]->Cursos)),
                                                        'nivel'=> trim(utf8_decode($result[$k]->Nivel)),
                                                        'id_situacion'    => $result[$k]->ID_Situacion,
                                                        'situacion'=> trim(utf8_decode($result[$k]->Situacion))
                                                        
                                                    );
        
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
