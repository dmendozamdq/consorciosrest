<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class Medios_PagoRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    
    public function agregar_medio_pago($nombre)
    {
      try {
               $nombre=utf8_encode($nombre);
               $medios_pago = \DB::connection('mysql2')->select("
                                  SELECT mp.Id
                                  FROM medios_pago mp
                                  WHERE mp.Nombre='{$nombre}'
                                    ");
              
              $ctrl_medio_pago=count($medios_pago);
              if(empty($ctrl_medio_pago))
                {
                    $creo_medio_pago = \DB::connection('mysql2')->Insert("
                              INSERT INTO medios_pago
                              (Nombre)
                              VALUES
                              ('{$nombre}')
                          ");
              
                    $ok='agregado';
                    return $ok;
                }
              else
                {
                    $error='existe';
                    return $error;
                }
              
              

          } catch (\Exception $e) {
              return $e;
          }
        }

    
    public function borrar_medio_pago($id)
        {
          date_default_timezone_set('America/Argentina/Buenos_Aires');
          $FechaActual=date("Y-m-d");
          $HoraActual=date("H:i:s");
            
          $borrado = \DB::connection('mysql2')->update("
                          UPDATE medios_pago
                          SET B=1
                          WHERE ID={$id}'
                      ");
          $ok='borrado';
          return $ok;
        }


}
