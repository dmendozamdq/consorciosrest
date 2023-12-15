<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class CampanasRepository
{

    private $Alumno;
    protected $connection = 'mysql';

    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
    {
        $this->Alumno = $Alumno;
        $this->dataBaseService = $dataBaseService;
    }

    
    public function agregar_campana($id, $nombre, $importe, $alcance, $conceptos, $cursos, $id_usuario)
    {
      $habilitado=0;
      try {
              date_default_timezone_set('America/Argentina/Buenos_Aires');
              $FechaActual=date("Y-m-d");
              $HoraActual=date("H:i:s");

               $nombre = utf8_encode($nombre);
               $importe = $importe;
               $alcance = $alcance;
               $conceptos = $conceptos;
               $id_usuario = $id_usuario;
               $id_institucion = $id;

               $campanas = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT cf.Id
                                  FROM campanas_facturacion cf
                                  WHERE cf.Nombre='{$nombre}' and cf.B=0  
                                    ");
              
              $ctrl_campanas=count($campanas);
              if(empty($ctrl_campanas))
                {
                  $habilitado=0;
                 
                }
              else
                {
                  for ($k=0; $k < $ctrl_campanas  ; $k++)
                    {
                      $id_campanas=$campanas[$k]->Id;
                      $campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                            SELECT cc.Id
                                            FROM campanas_conceptos cc
                                            WHERE cc.Id_Campanas={$id_campanas} and cc.Id_Conceptos='{$conceptos}' and cc.B=0

                                              ");
  
                      $ctrl_campanas_conceptos=count($campanas_conceptos);
                      if($ctrl_campanas_conceptos>=1)
                        {
                          $habilitado++;
                        }
                      
                      $campanas_alcance = $this->dataBaseService->selectConexion($id_institucion)->select("
                                              SELECT ca.Id
                                              FROM campanas_alcance ca
                                              WHERE ca.Id_Campana={$id_campanas} and ca.Id_Nivel='{$alcance}' and ca.B=0

                                              ");

                      $ctrl_campanas_alcance=count($campanas_alcance);
                      if($ctrl_campanas_alcance>=1)
                        {
                          $habilitado++;
                        }

                    }
                }
              //$habilitado = 1;
              if(empty($habilitado))
                {           
                    $creo_campanas = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                      INSERT INTO campanas_facturacion
                                      (Nombre,Importe,Fecha_Alta)
                                      VALUES
                                      ('{$nombre}','{$importe}','{$FechaActual}')
                                  ");
                    /*$verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cf.Id
                                FROM campanas_facturacion cf
                                WHERE cf.Nombre='{$nombre}' and cf.Importe='{$importe}' AND cf.Fecha_Alta='{$FechaActual}' AND cf.B=0
                                
                              ");
                              */
                      $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                              SELECT cf.Id
                              FROM campanas_facturacion cf
                              WHERE cf.B=0
                              ORDER BY cf.ID desc LIMIT 1
                              
                            ");          

                    $id_campanas = $verifico_insercion[0]->Id;
                    if($id_campanas>=1)
                      {
                        $creo_campanas_alcances = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO campanas_alcance
                              (Id_Campana, Id_Nivel)
                              VALUES
                              ('{$id_campanas}','{$alcance}')
                        ");
                      }

                    


                    
                    foreach($conceptos as $Linea)
                    {
                      $id_concepto=$Linea['id_concepto'];
                      $creo_campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                      INSERT INTO campanas_conceptos
                      (Id_Campanas , Id_Conceptos , Fecha_Alta , Id_Usuario)
                      VALUES
                      ('{$id_campanas}','{$id_concepto}' ,'{$FechaActual}' ,'{$id_usuario}')
                      ");
                      
                    }

                    foreach($cursos as $Linea)
                    {
                      $id_curso=$Linea['id_curso'];
                      $creo_campanas_cursos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                      INSERT INTO campanas_alcance_cursos
                      (Id_Campana , Id_Curso)
                      VALUES
                      ('{$id_campanas}','{$id_curso}')
                      ");
                      
                    }
                    
                   
                    $ok='La campaña ha sido creada con Éxito';
                    //$ok = utf8_encode($ok);
                    return $ok;
                }
              else
                {
                    $error='Atención: La campaña ya existe en el sistema';
                    //$error= utf8_encode($error);
                    return $error;
                }
              
              

          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar_campana($id,$nombre, $importe, $alcance, $conceptos, $cursos, $id_usuario, $id_campana)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          
          $id_institucion = $id;
          $nombre = utf8_encode($nombre);
          $importe = $importe;
          $alcance = $alcance;
          $conceptos = $conceptos;
          $id_usuario = $id_usuario;


          $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE campanas_facturacion
                          SET Nombre = '{$nombre}', Importe = '{$importe}'
                          WHERE Id= {$id_campana}

                      ");
          $modificar_campanas_alcance = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE  campanas_alcance
                          SET Id_Nivel={$alcance}
                          WHERE Id_Campana={$id_campana}

                    ");

          $borrado_campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE campanas_conceptos
                          SET B=1
                          WHERE Id_Campanas ={$id_campana}
                      ");
          
          foreach($conceptos as $Linea)
            {
                $id_concepto=$Linea['id_concepto'];
                $exploro_campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cc.Id
                          FROM campanas_conceptos cc
                          WHERE cc.B=1 and cc.Id_Campanas={$id_campana} and cc.Id_Conceptos={$id_concepto}
                          
                ");
                $Control_Concepto=count($exploro_campanas_conceptos);
                if(empty($Control_Concepto))
                  {
                    //INSERTO CONCEPTO
                    $creo_campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                        INSERT INTO campanas_conceptos
                                        (Id_Campanas , Id_Conceptos , Fecha_Alta , Id_Usuario)
                                        VALUES
                                        ('{$id_campana}','{$id_concepto}' ,'{$FechaActual}' ,'{$id_usuario}')
                                    ");
                  }
                else
                  {
                    $id_item =  $exploro_campanas_conceptos[0]->Id;
                    $modificar_campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->update("
                                          UPDATE campanas_conceptos
                                          SET b=0
                                          WHERE Id ={$id_item}
                                      ");
                  }

            }
          $borrado_campanas_cursos = $this->dataBaseService->selectConexion($id_institucion)->update("
                UPDATE campanas_alcance_cursos
                SET B=1
                WHERE Id_Campana = {$id_campana}
           ");
          foreach($cursos as $Linea)
            {
              $id_curso=$Linea['id_curso'];
              $exploro_campanas_cursos = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cc.Id
                          FROM campanas_alcance_cursos cc
                          WHERE cc.B=1 and cc.ID_Campana={$id_campana} and cc.Id_Curso={$id_curso}
                          
                ");
                $Control_Curso=count($exploro_campanas_cursos);
                if(empty($Control_Curso))
                  {
                    //INSERTO CONCEPTO
                    $creo_campanas_cursos = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                        INSERT INTO campanas_alcance_cursos
                                        (Id_Campana , Id_Curso)
                                        VALUES
                                        ('{$id_campana}','{$id_curso}')
                                    ");
                  }
                else
                  {
                    $id_item =  $exploro_campanas_cursos[0]->Id;
                    $modificar_campanas_cursos = $this->dataBaseService->selectConexion($id_institucion)->update("
                                          UPDATE campanas_alcance_cursos
                                          SET b=0
                                          WHERE Id ={$id_item}
                                      ");
                  }

            }
                      
          
            $ok='La campaña ha sido modificada con éxito';
            //$ok = utf8_encode($ok);
            return $ok;
    }   
        
    public function borrar_campana($id, $id_campana)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
            
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE campanas_facturacion
                          SET B=1
                          WHERE Id= {$id_campana}

                      ");

          $borrado_campanas_conceptos = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE campanas_conceptos
                      SET B=1
                      WHERE Id_Campanas ={$id_campana}
              
                  ");

          $borrado_campanas_alcance = $this->dataBaseService->selectConexion($id_institucion)->update("
                    UPDATE  campanas_alcance
                    SET B=1
                    WHERE Id_Campana={$id_campana}

              ");

              $borrado_campanas_cursos = $this->dataBaseService->selectConexion($id_institucion)->update("
                    UPDATE campanas_alcance_cursos
                    SET B=1
                    WHERE Id_Campana={$id_campana}

              ");

          $ok='La campaña ha sido eliminada con éxito';
          return $ok;
        }

        public function listado_campanas($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion = $id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cf.Id, cf.Nombre, cf.Fecha_Alta, cf.Importe, ca.Id_Nivel
                          FROM campanas_facturacion cf
                          INNER JOIN campanas_alcance ca ON cf.Id=ca.Id_Campana
                          WHERE cf.B=0 
                          ORDER BY cf.Nombre
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   

                  $id_campana =  $listado[$j]->Id;
                  $resultado[$j] = array(
                                              'id' => $listado[$j]->Id,
                                              'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                              'fecha_alta_cf'=> $listado[$j]->Fecha_Alta,  
                                              'importe_cf'=> $listado[$j]->Importe,                                    
                                              'id_nivel'=> $listado[$j]->Id_Nivel
                                          );
                    
                  $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                              SELECT cc.Id,cc.Id_Conceptos,con.Importe
                                              FROM campanas_conceptos cc
                                              INNER JOIN conceptos con ON cc.Id_Conceptos=con.Id
                                              WHERE cc.B=0 and cc.Id_Campanas={$id_campana}
                                              ORDER BY cc.Id_Conceptos
                                      ");
                    $cant_conceptos=count($detalle);
                    if($cant_conceptos>=1)
                             {
                                            for ($k=0; $k < count($detalle); $k++) {
                  
                                              //$resultado[$j]['detalle_periodo'][$k] = 1;
                                              $resultado[$j]['detalle_conceptos'][$k] = array(                                                                    
                                                                                'id'=> $detalle[$k]->Id,
                                                                                'id_concepto'=> $detalle[$k]->Id_Conceptos,
                                                                                'importe'=> $detalle[$k]->Importe
                                                              );
                                                  }
                              }
                  $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                              SELECT cc.Id,cc.Id_Curso
                                              FROM campanas_alcance_cursos cc
                                              WHERE cc.B=0 and cc.Id_Campana={$id_campana}
                                              ORDER BY cc.Id
                      ");
    $cant_cursos=count($detalle);
    if($cant_cursos>=1)
             {
                            for ($k=0; $k < count($detalle); $k++) {
  
                              //$resultado[$j]['detalle_periodo'][$k] = 1;
                              $resultado[$j]['detalle_cursos'][$k] = array(                                                                    
                                                                'id'=> $detalle[$k]->Id,
                                                                'id_curso'=> $detalle[$k]->Id_Curso
                                              );
                                  }
              }
                                      
                                      
              }
          return $resultado;
        }
      
      public function mostrar_campana($id,$id_campana)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT cf.Id, cf.Nombre, cf.Fecha_Alta, cf.Importe, ca.Id_Nivel
                          FROM campanas_facturacion cf
                          INNER JOIN campanas_alcance ca ON cf.Id=ca.Id_Campana
                          WHERE cf.Id={$id_campana}
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                   $resultado[$j] = array(
                                      'id' => $listado[$j]->Id,
                                      'nombre'=> trim(utf8_decode($listado[$j]->Nombre)),
                                      'fecha_alta_cf'=> $listado[$j]->Fecha_Alta,
                                      'importe'=> $listado[$j]->Importe,
                                      'id_nivel'=> $listado[$j]->Id_Nivel
                                          );
                    $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                          SELECT cc.Id,cc.Id_Conceptos,con.Importe
                                          FROM campanas_conceptos cc
                                          INNER JOIN conceptos con ON cc.Id_Conceptos=con.Id
                                          WHERE cc.B=0 and cc.Id_Campanas={$id_campana}
                                          ORDER BY cc.Id_Conceptos
                                  ");
                    $cant_conceptos=count($detalle);
                    if($cant_conceptos>=1)
                         {
                                        for ($k=0; $k < count($detalle); $k++) {
              
                                          //$resultado[$j]['detalle_periodo'][$k] = 1;
                                          $resultado[$j]['detalle_conceptos'][$k] = array(                                                                    
                                                                            'id'=> $detalle[$k]->Id,
                                                                            'id_concepto'=> $detalle[$k]->Id_Conceptos,
                                                                            'importe'=> $detalle[$k]->Importe
                                                          );
                                              }
                          }
                    $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cc.Id,cc.Id_Curso
                                FROM campanas_alcance_cursos cc
                                WHERE cc.B=0 and cc.Id_Campana={$id_campana}
                                ORDER BY cc.Id
                          ");
                    $cant_cursos=count($detalle);
                    if($cant_cursos>=1)
                            {
                                            for ($k=0; $k < count($detalle); $k++) {
                  
                                              //$resultado[$j]['detalle_periodo'][$k] = 1;
                                              $resultado[$j]['detalle_cursos'][$k] = array(                                                                    
                                                                                'id'=> $detalle[$k]->Id,
                                                                                'id_curso'=> $detalle[$k]->Id_Curso
                                                              );
                                                  }
                              }
    


                }
           
                 
             
          return $resultado;
        }


}
