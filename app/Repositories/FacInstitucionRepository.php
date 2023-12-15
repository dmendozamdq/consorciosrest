<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class FacInstitucionRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

 public function listado_niveles($id)
    {

        try
          {
            $id_institucion = $id;          
            
            $result = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT  n.ID, n.Nivel, n.Numero, n.CUE
                        FROM nivel n
                        ORDER BY n.Nivel
                        ");

            for ($k=0; $k < count($result); $k++) {

            $resultado[0]['niveles'][$k] = array(
                                                        'id'    => $result[$k]->ID,
                                                        'nivel'=> trim(utf8_decode($result[$k]->Nivel)),
                                                        'numero'=> $result[$k]->Numero,
                                                        'cue' => $result[$k]->CUE
                                                        
                                                        
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

    public function listado_cursos($id,$id_institucion)
    {

        try
          {
            
            $result = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT  c.ID, c.Cursos, c.Turno, c.CC, c.Division
                        FROM cursos c
                        WHERE c.ID_Nivel={$id}
                        ORDER BY c.Cursos
                        ");

            for ($k=0; $k < count($result); $k++) {

            $resultado[0]['cursos'][$k] = array(
                                                        'id'    => $result[$k]->ID,
                                                        'curso'=> trim(utf8_decode($result[$k]->Cursos)),
                                                        'turno'=> $result[$k]->Turno,
                                                        'anio' => $result[$k]->CC,
                                                        'division' => trim(utf8_decode($result[$k]->Division))
                                                        
                                                        
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
