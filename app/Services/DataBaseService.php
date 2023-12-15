<?php

namespace App\Services;


class DataBaseService
{

    function __construct()
    {

    }

    public function selectConexion($ID_Institucion = false)
    {
        try {

            //DAZEO
            if ($ID_Institucion == 3){
              return $connection = \DB::connection('mysql2');
          }
          
            //DORMAR
          elseif ($ID_Institucion == 1){
              return $connection = \DB::connection('mysql3');
          }
          //DEMO
          elseif ($ID_Institucion == 2){
              return $connection = \DB::connection('mysql4');
          }
          /*
          //ESQUIU
          elseif ($ID_Institucion == 6){
              return $connection = \DB::connection('mysql5');
          }
          //SAN MIGUEL
          elseif ($ID_Institucion == 7){
              return $connection = \DB::connection('mysql6');
          }
          //QUILMES
          elseif ($ID_Institucion == 13){
            return $connection = \DB::connection('mysql7');
            
        }

            */

            //return $connection = \DB::connection('mysql2');

        } catch (\Exception $e) {

        }
    }



}
