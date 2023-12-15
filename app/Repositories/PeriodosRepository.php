<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class PeriodosRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
    {
        $this->Alumno = $Alumno;
        $this->dataBaseService = $dataBaseService;
    }

    public function agregar_periodo($id, $nombre, $ciclo, $subperiodo)
    {
      try {
              date_default_timezone_set('America/Argentina/Buenos_Aires');
              $FechaActual=date("Y-m-d");
              $HoraActual=date("H:i:s");
              $id_institucion = $id;

               $nombre=utf8_encode($nombre);
               $ciclo=$ciclo;

               $periodos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT p.Id
                                  FROM periodos p
                                  WHERE p.Nombre='{$nombre}' and B=0
                                    ");

              $ctrl_periodos=count($periodos);
              if(empty($ctrl_periodos))
                {
                  $habilitado=0;
                }
              else
                {
                  for ($k=0; $k < $ctrl_periodos; $k++)
                    {
                      $id_periodo=$periodos[$k]->Id;
                      /*$periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT pd.Id
                                            FROM periodo_detalle pd
                                            WHERE pd.Id_Periodo={$id_periodo} and pd.Id_Mes='{$mes}'and pd.Vencimiento ='{$fecha_vencimiento}' AND pd.B=0

                                              ");

                      $ctrl_periodo_detalle=count($periodo_detalle);
                      if($ctrl_periodo_detalle>=1)
                        {
                          $habilitado++;
                        }
                      */

                    }
                }
              if(empty($habilitado))
                {
                    $creo_periodos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO periodos
                              (Nombre , Id_Ciclo_Lectivo)
                              VALUES
                              ('{$nombre}','{$ciclo}')
                          ");
                    $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT p.Id
                                FROM periodos p
                                WHERE p.Nombre='{$nombre}' AND p.Id_Ciclo_Lectivo='{$ciclo}'
                                ORDER BY p.ID desc
                              ");

                    $id_periodo = $verifico_insercion[0]->Id;
                    //$json = json_decode($subperiodo, true);
                    foreach($subperiodo as $Linea)
                      {
                        $mes=$Linea['mes'];
                        $interes=$Linea['interes'];
                        $fecha_vencimiento=$Linea['vencimiento'];
                        $creo_periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                        INSERT INTO periodos_detalle
                        (Id_Periodo , Id_Mes , Interes , Vencimiento , Fecha_Alta)
                        VALUES
                        ('{$id_periodo}','{$mes}' ,'{$interes}' ,'{$fecha_vencimiento}' ,'{$FechaActual}')
                    ");

                      }




                    $ok='El Período se ha agregado con éxito.';
                    return $id_periodo;
                }
              else
                {
                    $error='Atención: El período ya existe en el sistema.';
                    return $error;
                }



          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar_periodo($id,$nombre, $ciclo, $id_periodo)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion = $id;
          $id = $id_periodo;

          $id = $id;
          $nombre=utf8_encode($nombre);
          $ciclo=$ciclo;
          
          
          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE periodos
                          SET Nombre = '{$nombre}',  Id_Ciclo_Lectivo='{$ciclo}'
                          WHERE Id= {$id}
                      ");

          /*$modificar_periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE periodos_detalle
                      SET Id_Mes={$mes} , Interes={$interes} , Vencimiento={$fecha_vencimiento} , Fecha_Alta={$FechaActual}
                      WHERE Id_Periodo ={$id}
                  ");
          */

          $ok='El período ha sido modificado con éxito';
          return $ok;
    }

    public function agregar_subperiodo($id, $mes, $interes, $vencimiento, $id_periodo)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");

          $id_institucion = $id;
          $id = $id_periodo;
          $mes=$mes;
          $interes=$interes;
          $fecha_vencimiento=$vencimiento;
          $creo_periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                        INSERT INTO periodos_detalle
                        (Id_Periodo , Id_Mes , Interes , Vencimiento , Fecha_Alta)
                        VALUES
                        ('{$id}','{$mes}' ,'{$interes}' ,'{$fecha_vencimiento}' ,'{$FechaActual}')
                    ");

          $ok='El Subperíodo se ha agregado con éxito';
          return $ok;
    }

    public function modificar_subperiodo($id, $mes, $interes, $vencimiento, $id_subperiodo)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");


          $id_institucion = $id;
          $id = $id_subperiodo;
          $mes=$mes;
          $interes=$interes;
          $fecha_vencimiento=$vencimiento;

          $modificar_periodo_detalle = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE periodos_detalle
                      SET Id_Mes={$mes} , Interes={$interes} , Vencimiento='{$fecha_vencimiento}' , Fecha_Alta='{$FechaActual}'
                      WHERE Id ={$id}
                  ");

          $ok='El Subperíodo se ha modificado con éxito';
          return $ok;
    }

    public function borrar_subperiodo($id, $id_subperiodo)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s"); 
          $id_institucion = $id;
          $id = $id_subperiodo;

          $borrado2 = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE periodos_detalle
                      SET B=1
                      WHERE Id={$id}
                  ");
          $ok='El SubPeríodo ha sido eliminado con éxito';
          return $ok;
    }


    public function borrar_periodo($id,$id_periodo)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion = $id;
          $id = $id_periodo;

          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE periodos
                          SET B=1
                          WHERE ID={$id}
                      ");
          $borrado2 = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE periodos_detalle
                      SET B=1
                      WHERE Id_Periodo={$id}
                  ");
          $ok='El Período ha sido eliminado con éxito';
          return $ok;
        }

      public function listado_periodos($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion = $id;
          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT p.Id, p.Nombre, p.Id_Ciclo_Lectivo
                          FROM periodos p
                          WHERE p.B=0
                          ORDER BY p.Nombre
                      ");

          for ($j=0; $j < count($listado); $j++)
                {

                  $id_periodo =  $listado[$j]->Id;
                  $resultado[$j] = array(
                                              'id' => $id_periodo,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'id_ciclo_lectivo'=> $listado[$j]->Id_Ciclo_Lectivo

                                          );
                    $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                          SELECT pd.Id,pd.Id_Mes, pd.Interes, pd.Vencimiento, pd.Fecha_Alta
                                          FROM periodos_detalle pd
                                          WHERE pd.B=0 and pd.Id_Periodo={$id_periodo}
                                          ORDER BY pd.Id_Mes
                                      ");
                    $cant_subperiodos=count($detalle);
                    if($cant_subperiodos>=1)
                        {
                          for ($k=0; $k < count($detalle); $k++) {

                            //$resultado[$j]['detalle_periodo'][$k] = 1;
                            $resultado[$j]['detalle_periodo'][$k] = array(
                                                              'id'=> $detalle[$k]->Id,
                                                              'mes'=> $detalle[$k]->Id_Mes,
                                                              'interes'=> $detalle[$k]->Interes,
                                                              'vencimiento'=> $detalle[$k]->Vencimiento,
                                                              'fecha_alta'=> $detalle[$k]->Fecha_Alta


                                            );
                                }
                        }


                }
          return $resultado;

        }

      public function mostrar_periodo($id,$id_periodo)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion = $id;
          $id = $id_periodo;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT p.Id, p.Nombre, p.Id_Ciclo_Lectivo
                          FROM periodos p
                          WHERE p.B=0 and p.Id={$id}
                          ORDER BY p.Nombre
                      ");

          for ($j=0; $j < count($listado); $j++)
                      {

                        $id_periodo =  $listado[$j]->Id;
                        $resultado[$j] = array(
                                                    'id' => $id_periodo,
                                                    'cuenta' => count($listado),
                                                    'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                                    'id_ciclo_lectivo'=> $listado[$j]->Id_Ciclo_Lectivo

                                                );
                          $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                                SELECT pd.Id,pd.Id_Mes, pd.Interes, pd.Vencimiento, pd.Fecha_Alta
                                                FROM periodos_detalle pd
                                                WHERE pd.B=0 and pd.Id_Periodo={$id_periodo}
                                                ORDER BY pd.Id_Mes
                                            ");
                          $cant_subperiodos=count($detalle);
                          if($cant_subperiodos>=1)
                              {
                                for ($k=0; $k < count($detalle); $k++) {

                                  //$resultado[$j]['detalle_periodo'][$k] = 1;
                                  $resultado[$j]['detalle_periodo'][$k] = array(
                                                                    'id'=> $detalle[$k]->Id,
                                                                    'mes'=> $detalle[$k]->Id_Mes,
                                                                    'interes'=> $detalle[$k]->Interes,
                                                                    'vencimiento'=> $detalle[$k]->Vencimiento,
                                                                    'fecha_alta'=> $detalle[$k]->Fecha_Alta


                                                  );
                                      }
                              }
                          

                      }
                return $resultado;
        }


}
