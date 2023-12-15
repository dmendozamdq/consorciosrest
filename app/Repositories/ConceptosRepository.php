<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class ConceptosRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
    {
        $this->Alumno = $Alumno;
        $this->dataBaseService = $dataBaseService;
    }

    // FALTA GUARDAR EN LA TABLA conceptos_alcance
    public function agregar_conceptos($id,$nombre, $importe, $alcance)
    {
      $habilitado=0;
      try {
               $nombre=utf8_encode($nombre);
               $importe=($importe);
               $alcance=($alcance);
               $id_institucion = $id;
               $conceptos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT c.Id
                                  FROM conceptos c
                                  WHERE c.Nombre='{$nombre}' and c.B=0

                                    ");
              
              $ctrl_conceptos=count($conceptos);
              if(empty($ctrl_conceptos))
                {
                  $habilitado=0;
                }
              else
                {
                  for ($k=0; $k < $ctrl_conceptos; $k++)
                    {
                      $id_concepto=$conceptos[$k]->Id;
                      $conceptos_alcance = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT ca.Id
                                  FROM conceptos_alcance ca
                                  WHERE ca.Id_Conceptos={$id_concepto} and ca.Id_Niveles='{$alcance}' AND ca.B=0

                                    ");
              
                      $ctrl_alcance_conceptos=count($conceptos_alcance);
                      if($ctrl_alcance_conceptos>=1)
                        {
                          $habilitado++;
                        }

                    }
                }

              if(empty($habilitado))
                {
                    $creo_conceptos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO conceptos
                              (Nombre,Importe)
                              VALUES
                              ('{$nombre}','{$importe}')
                          ");
                    $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT c.Id
                          FROM conceptos c
                          WHERE c.Nombre='{$nombre}' AND c.Importe='{$importe}' AND c.B=0
                          ORDER BY c.ID desc
                          ");
                    
                    $id_concepto = $verifico_insercion[0]->Id;
                    $creo_alcence_conceptos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO conceptos_alcance
                              (Id_Conceptos , Id_Niveles)
                              VALUES
                              ('{$id_concepto}','{$alcance}')
                          ");
                    
                    
                          
                          
                    $ok='El concepto ha sido agregado con éxito';
                    $ok=utf8_encode($ok);
                    return $ok;
                }
              else
                {
                    $error='Atención: El concepto se encuentra repetido en el sistema';
                    $error=utf8_encode($ok);
                    return $error;
                }
              
              

          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar_conceptos($id, $nombre, $importe, $alcance, $id_concepto)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $id=$id_concepto;
          
          $nombre=utf8_encode($nombre);
          $importe=($importe);
          $alcance=($alcance);

          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE conceptos
                          SET Nombre='{$nombre}',Importe='{$importe}'
                          WHERE ID={$id}
                      ");
          
          $modificar_alcance = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE conceptos_alcance
                      SET Id_Niveles={$alcance}
                      WHERE ID_Conceptos={$id}
                  ");


          $ok='El Concepto ha sido Modificado con éxito';
          return $ok;
    }   
        
    public function borrar_conceptos($id,$id_concepto)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
          $id=$id_concepto;
            
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE conceptos
                          SET B=1
                          WHERE ID={$id}
                      ");
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE conceptos_alcance
                      SET B=1
                      WHERE Id_Conceptos={$id}
                  ");
          $ok='El concepto ha sido eliminado con éxito.';
          return $ok;
        }
        
    public function mostrar_conceptos($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT c.Id,c.Nombre,c.Importe,ca.Id_Niveles
                          FROM conceptos c
                          INNER JOIN conceptos_alcance ca ON c.Id=ca.Id_Conceptos
                          WHERE c.B=0
                          ORDER BY c.Nombre
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                   $resultado[$j] = array(
                                              'id' => $listado[$j]->Id,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'importe'=> $listado[$j]->Importe,
                                              'alcance'=> $listado[$j]->Id_Niveles
                                          );
                }
          return $resultado;
        }
      
      public function mostrar_concepto($id_institucion,$id_concepto)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          //$id_institucion=$id;
          //$id=$id_concepto;
          
          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT c.Id,c.Nombre,c.Importe,ca.Id_Niveles
                          FROM conceptos c
                          INNER JOIN conceptos_alcance ca ON c.Id=ca.Id_Conceptos
                          WHERE c.B=0 and c.Id={$id_concepto}
                          ORDER BY c.Nombre
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                   $resultado[$j] = array(
                                              'id' => $listado[$j]->Id,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'importe'=> $listado[$j]->Importe,
                                              'alcance'=> $listado[$j]->Id_Niveles
                                          );
                }
          return $resultado;
        }

}
