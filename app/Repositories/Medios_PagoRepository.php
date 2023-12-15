<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class Medios_PagoRepository
{

    private $Alumno;
    protected $connection = 'mysql';
    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
    {
        $this->Alumno = $Alumno;
        $this->dataBaseService = $dataBaseService;
    }

    
    public function agregar_medio_pago($id,$nombre)
    {
      try {
               $nombre=utf8_encode($nombre);
               $id_institucion = $id;
               $medios_pago = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT mp.Id
                                  FROM medios_pago mp
                                  WHERE mp.Nombre='{$nombre}' and mp.B=0
                                    ");
              
              $ctrl_medio_pago=count($medios_pago);
              if(empty($ctrl_medio_pago))
                {
                    $creo_medio_pago = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO medios_pago
                              (Nombre)
                              VALUES
                              ('{$nombre}')
                          ");
              
                    $ok='El Medio de Pago fue agregado con éxito';
                    return $ok;
                }
              else
                {
                    $error='Atención: El Medio de Pago ya existe en el sistema';
                    return $error;
                }
              
              

          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar_medio_pago($id, $nombre,$estado,$id_medio_pago)
      {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion = $id;
          $id = $id_medio_pago;
            
          $nombre=utf8_encode($nombre);
          $modificado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE medios_pago
                          SET Nombre = '{$nombre}' , Estado = '{$estado}'
                          WHERE ID= {$id}
                      ");
          $ok='El Medio de Pago fue modificado con éxito';
          return $ok;
      }
    
      public function activar_medio_pago($id,$id_medio_pago)
      {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion = $id;
          $id = $id_medio_pago;
          
          $modificado = $this->dataBaseService->selectConexion($id_institucion)->update("
                              UPDATE medios_pago
                              SET Estado = 1
                              WHERE Id= '{$id}'
                              ");
      
          
          $ok='El Medio de Pago fue Activado con éxito';
          return $ok;
      }

      public function desactivar_medio_pago($id,$id_medio_pago)
      {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion = $id;
          $id = $id_medio_pago;
          
          $modificado = $this->dataBaseService->selectConexion($id_institucion)->update("
                              UPDATE medios_pago
                              SET Estado = 0
                              WHERE Id= '{$id}'
                              ");
      
          
          $ok='El Medio de Pago fue desactivado con éxito';
          return $ok;
      }


    public function borrar_medio_pago($id,$id_medio_pago)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion = $id;
          $id = $id_medio_pago;
            
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE medios_pago
                          SET B=1
                          WHERE ID= '{$id}'
                      ");
          $ok='El Medio de Pago fue eliminado con éxito';
          return $ok;
        }


    public function mostrar_medio_pago($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion = $id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT mp.Id,mp.Nombre, mp.Estado
                          FROM medios_pago mp
                          WHERE mp.B=0
                          ORDER BY mp.Nombre
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                   $resultado[$j] = array(
                                              'id' => $listado[$j]->Id,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'estado' => $listado[$j]->Estado
                                          );
                }
          return $resultado;

          
          
        }
      public function ver_medio_pago($id,$id_medio_pago)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion = $id;
          $id = $id_medio_pago;
            
          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT Id,Nombre
                          FROM medios_pago
                          WHERE Id={$id}
                          ORDER BY Id
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                  $ID_MP = $listado[$j]->Id;
                  $MP = trim(utf8_decode($listado[$j]->Nombre));
                  $resultado[$j]['id']      = $ID_MP;
                  $resultado[$j]['nombre']     = $MP;

                  $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT Id,Parametro
                          FROM medios_pago_detalle
                          WHERE Id_Medio_Pago={$ID_MP} and B=0
                          ORDER BY ID
                      ");
                  
                    for ($i=0; $i < count($detalle); $i++)
                      {
                        $resultado[$j]['detalle'][$i] = array(
                                                      'id_parametro'=> $detalle[$i]->Id,
                                                      'parametro'=> trim(utf8_decode($detalle[$i]->Parametro))
                                                      

                          
                         );
                      }
                  }
                return $resultado;

          
          //return $listado;
        }
}
