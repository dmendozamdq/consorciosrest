<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;
use App\Services\DataBaseService;

class EmpresasRepository
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
    public function agregar_empresa($id,$nombre,$tipo_documento,$documento,$telefono,$email,$usuario,$password,$cuit,$iibb,$inicio,$pto_vta)
    {
      $habilitado=0;
      try {
               $id_institucion = $id;
               $nombre=utf8_encode($nombre);
               $empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT emp.Id
                                  FROM empresas emp
                                  WHERE emp.Empresa='{$nombre}' AND emp.CUIT='{$cuit}' and emp.B=0

                                    ");
              
              $ctrl_empresa=count($empresa);
              if(empty($ctrl_empresa))
                {
                  $habilitado=0;
                }
              else
                {
                  $habilitado++;
                }

              if(empty($habilitado))
                {
                    $creo_empresa = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                              INSERT INTO empresas
                              (Empresa,Tipo_Documento,Numero_Documento,Telefono,Email,User,Password,CUIT,IIBB,Inicio_Actividades,Pto_Vta)
                              VALUES
                              ('{$nombre}','{$tipo_documento}','{$documento}','{$telefono}','{$email}','{$usuario}','{$password}','{$cuit}','{$iibb}','{$inicio}','{$pto_vta}')
                          ");
                    $verifico_insercion = $this->dataBaseService->selectConexion($id_institucion)->select("
                              SELECT emp.Id
                              FROM empresas emp
                              WHERE emp.CUIT='{$cuit}' and emp.B=0
                          ");
                    
                    $id_empresa = $verifico_insercion[0]->Id;
                          
                    $ok='La empresa ha sido agregada con éxito';
                    $ok=utf8_encode($ok);
                    return $ok;
                }
              else
                {
                    $error='Atención: La empresa ya se encuentra cargada en el sistema';
                    $error=utf8_encode($ok);
                    return $error;
                }
              
              

          } catch (\Exception $e) {
              return $e;
          }
        }

    public function modificar_empresa($id,$id_empresa,$nombre,$tipo_documento,$documento,$telefono,$email,$usuario,$password,$cuit,$iibb,$inicio,$pto_vta)
    {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
                    
          $nombre=utf8_encode($nombre);

          $empresa = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT emp.Id
                                  FROM empresas emp
                                  WHERE emp.CUIT='{$cuit}' and emp.ID<>$id_empresa and emp.B=0

                                    ");
              
            $ctrl_empresa=count($empresa);
            if(empty($ctrl_empresa))
                {
                        $modificar = $this->dataBaseService->selectConexion($id_institucion)->update("
                        UPDATE empresas
                        SET Empresa='{$nombre}',Tipo_Documento='{$tipo_documento}',Numero_Documento='{$documento}',Telefono='{$telefono}',Email='{$email}',User='{$usuario}',Password='{$password}',CUIT='{$cuit}',IIBB='{$iibb}',Inicio_Actividades='{$inicio}',Pto_Vta='{$pto_vta}'
                        WHERE ID={$id_empresa}
                            ");
    
                        $ok='La empresa ha sido modificada con éxito';
                        return $ok;
                }
              else
                {
                    $error='Atención: El CUIT consignado en la modificación, ya se encuentra activo en el sistema';
                    $error=utf8_encode($error);
                    return $error;
                }
          
          
    }   
        
    public function borrar_empresa($id,$id_empresa)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $id_institucion=$id;
                      
          $borrado = $this->dataBaseService->selectConexion($id_institucion)->update("
                          UPDATE empresas
                          SET B=1,Fecha_B='{$FechaActual}',Hora_B='{$HoraActual}'
                          WHERE ID={$id_empresa}
                      ");
          
          $ok='La empresa se ha eliminada con éxito.';
          return $ok;
        }
        
    public function listado_empresas($id)
        {
          $id_institucion=$id;
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();

          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT emp.ID,emp.Empresa,emp.Tipo_Documento,emp.Numero_Documento,emp.Telefono,emp.Email,emp.User,emp.Password,emp.CUIT,emp.IIBB,emp.Inicio_Actividades,emp.Pto_Vta
                          FROM empresas emp
                          WHERE emp.B=0
                          ORDER BY emp.Empresa
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                   $resultado[$j] = array(
                                              'id' => $listado[$j]->ID,
                                              'empresa'=> trim(utf8_decode($listado[$j]->Empresa)),
                                              'Tipo_Documento'=> $listado[$j]->Tipo_Documento,
                                              'Documento'=> $listado[$j]->Numero_Documento,
                                              'Telefono'=> $listado[$j]->Telefono,
                                              'Email'=> $listado[$j]->Email,
                                              'User'=> $listado[$j]->User,
                                              'Password'=> $listado[$j]->Password,
                                              'CUIT'=> $listado[$j]->CUIT,
                                              'IIBB'=> $listado[$j]->IIBB,
                                              'inicio_actividades'=> $listado[$j]->Inicio_Actividades,
                                              'pto_vta'=> $listado[$j]->Pto_Vta

                                          );
                }
          return $resultado;
        }
      
      public function ver_empresa($id,$id_empresa)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
          $resultado = array();
          $id_institucion=$id;
          
          $listado = $this->dataBaseService->selectConexion($id_institucion)->select("
                            SELECT emp.ID,emp.Empresa,emp.Tipo_Documento,emp.Numero_Documento,emp.Telefono,emp.Email,emp.User,emp.Password,emp.CUIT,emp.IIBB,emp.Inicio_Actividades,emp.Pto_Vta
                            FROM empresas emp
                            WHERE emp.B=0 and emp.ID={$id_empresa}
                            
                      ");
          
          for ($j=0; $j < count($listado); $j++)
                {
                   
                   $resultado[$j] = array(
                                                'id' => $listado[$j]->ID,
                                                'empresa'=> trim(utf8_decode($listado[$j]->Empresa)),
                                                'Tipo_Documento'=> $listado[$j]->Tipo_Documento,
                                                'Documento'=> $listado[$j]->Numero_Documento,
                                                'Telefono'=> $listado[$j]->Telefono,
                                                'Email'=> $listado[$j]->Email,
                                                'User'=> $listado[$j]->User,
                                                'Password'=> $listado[$j]->Password,
                                                'CUIT'=> $listado[$j]->CUIT,
                                                'IIBB'=> $listado[$j]->IIBB,
                                                'inicio_actividades'=> $listado[$j]->Inicio_Actividades,
                                                'pto_vta'=> $listado[$j]->Pto_Vta
                                          );
                }
          return $resultado;
        }

}
