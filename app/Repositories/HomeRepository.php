<?php

namespace App\Repositories;

use App\Models\Alumno;
use App\Services\DataBaseService;

class HomeRepository
{

    private $Alumno;
    protected $connection = 'mysql2';
    private $dataBaseService;

    function __construct(Alumno $Alumno, DataBaseService $dataBaseService)
    {
        $this->Alumno = $Alumno;
        $this->dataBaseService = $dataBaseService;
    }

    /*public function lectura_comunicado($id)
    {
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $lectura = $this->dataBaseService->selectConexion($id_institucion)->update("
                      UPDATE comunicados_detalle
                      SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                      WHERE ID={$id}
                  ");
    }
*/
    


    public function total_facturado($id, $filtro)
    {

        try {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado=array();

            function esDiaHabil($fecha) {
                // Obtener el día de la semana (0: domingo, 1: lunes, ..., 6: sábado)
                $dia_semana = date('w', strtotime($fecha));
                
                // Verificar si el día de la semana es lunes a viernes (días hábiles)
                return ($dia_semana >= 1 && $dia_semana <= 5);
            }
            
            function restarDiaHabil($fecha) {
                $fecha_modificada = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
                
                // Si la fecha resultante es un día hábil, retornarla
                if (esDiaHabil($fecha_modificada)) {
                    return $fecha_modificada;
                } else {
                    // Si la fecha es sábado (6), restar dos días para obtener el viernes
                    if (date('w', strtotime($fecha_modificada)) == 6) {
                        return date('Y-m-d', strtotime('-2 days', strtotime($fecha_modificada)));
                    } else {
                        // Si la fecha es domingo (0), restar tres días para obtener el viernes
                        return date('Y-m-d', strtotime('-3 days', strtotime($fecha_modificada)));
                    }
                }
            }

            if($filtro==1)
                {
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha='{$FechaActual}'
                        ");
                    $fecha_anterior= restarDiaHabil($FechaActual);
                    $consulta_facturacion_anterior = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha='{$fecha_anterior}'
                        ");



                }
            if($filtro==2)
                {
                    $FechaInicio = date("Y-m-01", strtotime($FechaActual));

                    // Obtener el último día del mes en curso
                    $FechaFin = date("Y-m-t", strtotime($FechaActual));

                    // Calcular la fecha del primer día del mes anterior
                    $primer_dia_mes_anterior = date("Y-m-01", strtotime("-1 month", strtotime($FechaActual)));

                    // Calcular la fecha del último día del mes anterior
                    $ultimo_dia_mes_anterior = date("Y-m-t", strtotime("-1 month", strtotime($FechaActual)));


                    
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha>='{$FechaInicio}'
                        ");

                    $consulta_facturacion_anterior = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha>='{$primer_dia_mes_anterior}' and cc.Fecha<='{$ultimo_dia_mes_anterior}'
                        ");
                }
            if($filtro==3)
                {
                    $anio_actual = date("Y");
                    // Calcular el año anterior
                    $anio_anterior = $anio_actual - 1;

                    // Calcular la fecha del primer día del año anterior
                    $primer_dia_anio_anterior = date("Y-01-01", strtotime($anio_anterior . "-01-01"));

                    // Calcular la fecha del último día del año anterior
                    $ultimo_dia_anio_anterior = date("Y-12-31", strtotime($anio_anterior . "-12-31"));

                    
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1
                        ");

                    $consulta_facturacion_anterior = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha>='{$primer_dia_anio_anterior}' and cc.Fecha<='{$ultimo_dia_anio_anterior}'
                        ");
                }
            $Ctrl_Nulidad=count($consulta_facturacion);
            if(empty($Ctrl_Nulidad))
                {
                    $Total_Facturado=0;
                }
            else
                {
                    $Total_Facturado=0;
                    for ($i=0; $i < count($consulta_facturacion); $i++) {
                        $Importe=$consulta_facturacion[$i]->Importe;
                        $Total_Facturado=$Total_Facturado+$Importe;
                    }
                }
            $Ctrl_Nulidad2=count($consulta_facturacion_anterior);
                if(empty($Ctrl_Nulidad2))
                    {
                        $Total_Facturado_Anterior=0;
                    }
                else
                    {
                        $Total_Facturado_Anterior=0;
                        for ($i=0; $i < count($consulta_facturacion_anterior); $i++) {
                            $Importe_Anterior=$consulta_facturacion_anterior[$i]->Importe;
                            $Total_Facturado_Anterior=$Total_Facturado_Anterior+$Importe_Anterior;
                        }
                    }
                
            $Total_Facturado=round($Total_Facturado,2);
            $Total_Facturado_Anterior=round($Total_Facturado_Anterior,2);
            
            if($Total_Facturado_Anterior>=1)
                    {
                        $Coef_Diferencia=($Total_Facturado/$Total_Facturado_Anterior)*100;
                        $Coef_Diferencia=round($Coef_Diferencia);
                        if($filtro==1)
                            {
                                if($Coef_Diferencia>1)
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % más que el día anterior';
                                        $Mensaje=' % más que el día anterior';
                                    }
                                else
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % menos que el día anterior';
                                        $Mensaje=' % del día anterior';
                                    }
                                
                                
                            }
                            if($filtro==2)
                            {
                                if($Coef_Diferencia>1)
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % más que el mes anterior';
                                        $Mensaje=' % más que el mes anterior';
                                    }
                                else
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % menos que el mes anterior';
                                        $Mensaje=' % del mes anterior';
                                    }
                            }
                            if($filtro==3)
                            {
                                if($Coef_Diferencia>1)
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % más que el año anterior';
                                        $Mensaje=' % más que el año anterior';
                                    }
                                else
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % menos que el año anterior';
                                        $Mensaje=' % del año anterior';
                                    }
                            }
                    }
            else
                    {
                        if($filtro==1)
                            {
                                $Mensaje='No hay datos de facturación del día anterior';
                                $Coef_Diferencia=0;
                            }
                            if($filtro==2)
                            {
                                $Mensaje='No hay datos de facturación del mes anterior';
                                $Coef_Diferencia=0;
                            }
                            if($filtro==3)
                            {
                                $Mensaje='No hay datos de facturación del año anterior';
                                $Coef_Diferencia=0;
                            }
                    }


            $resultado[0] = array(
                                                              'total_facturado' => $Total_Facturado,
                                                              'filtro'       => $filtro,
                                                              'porcentaje'       => $Coef_Diferencia,
                                                              'mensaje'       => trim($Mensaje)
                                                              //'total_facturado_anterior' => $Total_Facturado_Anterior


                                                              
                                                              
                                 );

            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function total_cobrado($id, $filtro)
    {

        try {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado=array();

            function esDiaHabil($fecha) {
                // Obtener el día de la semana (0: domingo, 1: lunes, ..., 6: sábado)
                $dia_semana = date('w', strtotime($fecha));
                
                // Verificar si el día de la semana es lunes a viernes (días hábiles)
                return ($dia_semana >= 1 && $dia_semana <= 5);
            }
            
            function restarDiaHabil($fecha) {
                $fecha_modificada = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
                
                // Si la fecha resultante es un día hábil, retornarla
                if (esDiaHabil($fecha_modificada)) {
                    return $fecha_modificada;
                } else {
                    // Si la fecha es sábado (6), restar dos días para obtener el viernes
                    if (date('w', strtotime($fecha_modificada)) == 6) {
                        return date('Y-m-d', strtotime('-2 days', strtotime($fecha_modificada)));
                    } else {
                        // Si la fecha es domingo (0), restar tres días para obtener el viernes
                        return date('Y-m-d', strtotime('-3 days', strtotime($fecha_modificada)));
                    }
                }
            }

            if($filtro==1)
                {
                    $consulta_facturacion  = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Importe
                    FROM movimientos_caja mc
                    WHERE mc.B=0 and mc.Fecha='{$FechaActual}'
                        ");
                    $fecha_anterior= restarDiaHabil($FechaActual);
                    $consulta_facturacion_anterior = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Importe
                    FROM movimientos_caja mc
                    WHERE mc.B=0 and mc.Fecha='{$fecha_anterior}'
                        ");



                }
            if($filtro==2)
                {
                    $FechaInicio = date("Y-m-01", strtotime($FechaActual));

                    // Obtener el último día del mes en curso
                    $FechaFin = date("Y-m-t", strtotime($FechaActual));

                    // Calcular la fecha del primer día del mes anterior
                    $primer_dia_mes_anterior = date("Y-m-01", strtotime("-1 month", strtotime($FechaActual)));

                    // Calcular la fecha del último día del mes anterior
                    $ultimo_dia_mes_anterior = date("Y-m-t", strtotime("-1 month", strtotime($FechaActual)));


                    
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Importe
                    FROM movimientos_caja mc
                    WHERE mc.B=0 and mc.Fecha>='{$FechaInicio}'
                        ");

                    $consulta_facturacion_anterior = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Importe
                    FROM movimientos_caja mc
                    WHERE mc.B=0 and mc.Fecha>='{$primer_dia_mes_anterior}' and mc.Fecha<='{$ultimo_dia_mes_anterior}'
                        ");
                }
            if($filtro==3)
                {
                    $anio_actual = date("Y");
                    // Calcular el año anterior
                    $anio_anterior = $anio_actual - 1;

                    // Calcular la fecha del primer día del año anterior
                    $primer_dia_anio_anterior = date("Y-01-01", strtotime($anio_anterior . "-01-01"));

                    // Calcular la fecha del último día del año anterior
                    $ultimo_dia_anio_anterior = date("Y-12-31", strtotime($anio_anterior . "-12-31"));

                    
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Importe
                    FROM movimientos_caja mc
                    WHERE mc.B=0
                        ");

                    $consulta_facturacion_anterior = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT mc.Importe
                    FROM movimientos_caja mc
                    WHERE mc.B=0 and mc.Fecha>='{$primer_dia_anio_anterior}' and mc.Fecha<='{$ultimo_dia_anio_anterior}'
                        ");
                }
            $Ctrl_Nulidad=count($consulta_facturacion);
            if(empty($Ctrl_Nulidad))
                {
                    $Total_Facturado=0;
                }
            else
                {
                    $Total_Facturado=0;
                    for ($i=0; $i < count($consulta_facturacion); $i++) {
                        $Importe=$consulta_facturacion[$i]->Importe;
                        $Total_Facturado=$Total_Facturado+$Importe;
                    }
                }
            $Ctrl_Nulidad2=count($consulta_facturacion_anterior);
                if(empty($Ctrl_Nulidad2))
                    {
                        $Total_Facturado_Anterior=0;
                    }
                else
                    {
                        $Total_Facturado_Anterior=0;
                        for ($i=0; $i < count($consulta_facturacion_anterior); $i++) {
                            $Importe_Anterior=$consulta_facturacion_anterior[$i]->Importe;
                            $Total_Facturado_Anterior=$Total_Facturado_Anterior+$Importe_Anterior;
                        }
                    }
                
            $Total_Facturado=round($Total_Facturado,2);
            $Total_Facturado_Anterior=round($Total_Facturado_Anterior,2);
            
            if($Total_Facturado_Anterior>=1)
                    {
                        $Coef_Diferencia=($Total_Facturado/$Total_Facturado_Anterior)*100;
                        $Coef_Diferencia=round($Coef_Diferencia);
                        if($filtro==1)
                            {
                                if($Coef_Diferencia>1)
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % más que el día anterior';
                                        $Mensaje=' % más que el día anterior';
                                    }
                                else
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % menos que el día anterior';
                                        $Mensaje=' % del día anterior';
                                    }
                                
                                
                            }
                            if($filtro==2)
                            {
                                if($Coef_Diferencia>1)
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % más que el mes anterior';
                                        $Mensaje=' % más que el mes anterior';
                                    }
                                else
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % menos que el mes anterior';
                                        $Mensaje=' % del mes anterior';
                                    }
                            }
                            if($filtro==3)
                            {
                                if($Coef_Diferencia>1)
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % más que el año anterior';
                                        $Mensaje=' % más que el año anterior';
                                    }
                                else
                                    {
                                        //$Mensaje=$Coef_Diferencia. ' % menos que el año anterior';
                                        $Mensaje=' % del año anterior';
                                    }
                            }
                    }
            else
                    {
                        if($filtro==1)
                            {
                                $Mensaje='No hay datos de cobranzas del día anterior';
                                $Coef_Diferencia=0;
                            }
                            if($filtro==2)
                            {
                                $Mensaje='No hay datos de cobranzas del mes anterior';
                                $Coef_Diferencia=0;
                            }
                            if($filtro==3)
                            {
                                $Mensaje='No hay datos de cobranzas del año anterior';
                                $Coef_Diferencia=0;
                            }
                    }


            $resultado[0] = array(
                                                              'total_cobrado' => $Total_Facturado,
                                                              'total_anterior' => $Total_Facturado_Anterior,
                                                              'filtro'       => $filtro,
                                                              'porcentaje'       => $Coef_Diferencia,
                                                              'mensaje'       => trim($Mensaje)
                                                              //'total_facturado_anterior' => $Total_Facturado_Anterior


                                                              
                                                              
                                 );

            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function detalle_cobranza($id, $filtro)
    {

        try {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado=array();

            function esDiaHabil($fecha) {
                // Obtener el día de la semana (0: domingo, 1: lunes, ..., 6: sábado)
                $dia_semana = date('w', strtotime($fecha));
                
                // Verificar si el día de la semana es lunes a viernes (días hábiles)
                return ($dia_semana >= 1 && $dia_semana <= 5);
            }
            
            function restarDiaHabil($fecha) {
                $fecha_modificada = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
                
                // Si la fecsha resultante es un día hábil, retornarla
                if (esDiaHabil($fecha_modificada)) {
                    return $fecha_modificada;
                } else {
                    // Si la fecha es sábado (6), restar dos días para obtener el viernes
                    if (date('w', strtotime($fecha_modificada)) == 6) {
                        return date('Y-m-d', strtotime('-2 days', strtotime($fecha_modificada)));
                    } else {
                        // Si la fecha es domingo (0), restar tres días para obtener el viernes
                        return date('Y-m-d', strtotime('-3 days', strtotime($fecha_modificada)));
                    }
                }
            }

            if($filtro==1)
                {
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe, cc.Id_Tipo_Comprobante
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha='{$FechaActual}' and cct.ID<>8
                        ");
                    
                }
            if($filtro==2)
                {
                    $FechaInicio = date("Y-m-01", strtotime($FechaActual));

                    // Obtener el último día del mes en curso
                    $FechaFin = date("Y-m-t", strtotime($FechaActual));

                    // Calcular la fecha del primer día del mes anterior
                    $primer_dia_mes_anterior = date("Y-m-01", strtotime("-1 month", strtotime($FechaActual)));

                    // Calcular la fecha del último día del mes anterior
                    $ultimo_dia_mes_anterior = date("Y-m-t", strtotime("-1 month", strtotime($FechaActual)));

                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe, cc.Id_Tipo_Comprobante
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cc.Fecha>='{$FechaInicio}' and cct.ID<>8
                        ");

                    
                }
            if($filtro==3)
                {
                    $anio_actual = date("Y");
                    // Calcular el año anterior
                    $anio_anterior = $anio_actual - 1;

                    // Calcular la fecha del primer día del año anterior
                    $primer_dia_anio_anterior = date("Y-01-01", strtotime($anio_anterior . "-01-01"));

                    // Calcular la fecha del último día del año anterior
                    $ultimo_dia_anio_anterior = date("Y-12-31", strtotime($anio_anterior . "-12-31"));

                    
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                    SELECT cc.Importe, cc.Id_Tipo_Comprobante
                    FROM cuenta_corriente cc
                    INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                    WHERE cc.B=0 and cct.Clase=1 and cct.ID<>8
                        ");

                }
            $Ctrl_Nulidad=count($consulta_facturacion);
            if(empty($Ctrl_Nulidad))
                {
                    $Cuota=0;
                    $Recargos=0;
                    $Matricula=0;
                    $Extension=0;
                    $Extraordinarios=0;
                    $Deuda=0;
                }
            else
                {
                    $Cuota=0;
                    $Recargos=0;
                    $Matricula=0;
                    $Extension=0;
                    $Extraordinarios=0;
                    $Deuda=0;
                    for ($i=0; $i < count($consulta_facturacion); $i++) {
                        $Importe=$consulta_facturacion[$i]->Importe;
                        $Tipo=$consulta_facturacion[$i]->Id_Tipo_Comprobante;
                        if(($Tipo==2) or ($Tipo==3))
                            {
                                $Cuota=$Cuota+$Importe;
                            }
                        if($Tipo==7)
                            {
                                $Recargos=$Recargos+$Importe;
                            }
                        if($Tipo==9)
                            {
                                $Extraordinarios=$Extraordinarios+$Importe;
                            }
                        if($Tipo==10)
                            {
                                $Extension=$Extension+$Importe;
                            }
                        if($Tipo==11)
                            {
                                $Matricula=$Matricula+$Importe;
                            }
                    }
                    $Cuota=round($Cuota);
                    $Recargos=round($Recargos);
                    $Extraordinarios=round($Extraordinarios);
                    $Extension=round($Extension);
                    $Matricula=round($Matricula);
                }
            
          

            $resultado[0] = array(
                                                              'cuotas_mensuales' => $Cuota,
                                                              'recargos'       => $Recargos,
                                                              'matricula'       => $Matricula,
                                                              'extension_horaria'      => $Extension,
                                                              'extraordinarios'      => $Extraordinarios,
                                                              'deudas'      => $Deuda,
                                                              'filtro'       => $filtro


                                                              
                                                              
                                 );

            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function total_estudiantes($id, $filtro)
    {

        try {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado=array();

            if($filtro==0)
            {
                $headers = [
                    'Content-Type: application/json',
                    ];
                    $curl = curl_init();
                    $ruta_api2='http://apirest.geofacturacion.com.ar/api/responsables/estudiantes_listado/'.$id_institucion;
                    curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HTTPGET,true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_POST, false);
                    $data = curl_exec($curl);
                    curl_close($curl);
                    $data = json_decode($data, true);
                    $datos_alumnos = $data['data'];

                    $num_registros = count($datos_alumnos);
                    $descripcion='Estudiantes regulares en todos los niveles';
                
            }

            if($filtro==1)
                {
                    $ID_Nivel=$filtro;
                    $headers = [
                        'Content-Type: application/json',
                        ];
                        $curl = curl_init();
                        $ruta_api2='http://apirest.geoeducacion.com.ar/api/facturacion/estudiantes/'.$id_institucion.'?id='.$ID_Nivel;
                        curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_HTTPGET,true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($curl, CURLOPT_POST, false);
                        $data = curl_exec($curl);
                        curl_close($curl);
                        $data = json_decode($data, true);
                        $datos_alumnos = $data['data'];
                        $i=0;
                        foreach($datos_alumnos as $alumnos0)
                            {
                                $ii=0;
                                $Array_Alumnos=$alumnos0["alumnos"];
                                $num_registros = count($Array_Alumnos);
                                $descripcion='Estudiantes regulares en Nivel Primario';

                            }
    
                        
                    
                }
            if($filtro==2)
                {
                    $ID_Nivel=$filtro;
                    $headers = [
                        'Content-Type: application/json',
                        ];
                        $curl = curl_init();
                        $ruta_api2='http://apirest.geoeducacion.com.ar/api/facturacion/estudiantes/'.$id_institucion.'?id='.$ID_Nivel;
                        curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_HTTPGET,true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($curl, CURLOPT_POST, false);
                        $data = curl_exec($curl);
                        curl_close($curl);
                        $data = json_decode($data, true);
                        $datos_alumnos = $data['data'];
                        $i=0;
                        foreach($datos_alumnos as $alumnos0)
                            {
                                $ii=0;
                                $Array_Alumnos=$alumnos0["alumnos"];
                                $num_registros = count($Array_Alumnos);
                                $descripcion='Estudiantes regulares en Nivel Secundario';

                            }
                    
                }
            if($filtro==3)
                {
                    $ID_Nivel=$filtro;
                    $headers = [
                        'Content-Type: application/json',
                        ];
                        $curl = curl_init();
                        $ruta_api2='http://apirest.geoeducacion.com.ar/api/facturacion/estudiantes/'.$id_institucion.'?id='.$ID_Nivel;
                        curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_HTTPGET,true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($curl, CURLOPT_POST, false);
                        $data = curl_exec($curl);
                        curl_close($curl);
                        $data = json_decode($data, true);
                        $datos_alumnos = $data['data'];
                        $i=0;
                        foreach($datos_alumnos as $alumnos0)
                            {
                                $ii=0;
                                $Array_Alumnos=$alumnos0["alumnos"];
                                $num_registros = count($Array_Alumnos);
                                $descripcion='Estudiantes regulares en Nivel Inicial';

                            }
                }
            
            $resultado[0] = array(
                                                              'cantidad_alumnos' => $num_registros,
                                                              'texto'       => $descripcion                  
                                 );

            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function cobranza_evolutiva($id, $filtro)
    {

        try {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado=array();
            $id_periodo=$filtro;

            function esDiaHabil($fecha) {
                // Obtener el día de la semana (0: domingo, 1: lunes, ..., 6: sábado)
                $dia_semana = date('w', strtotime($fecha));
                
                // Verificar si el día de la semana es lunes a viernes (días hábiles)
                return ($dia_semana >= 1 && $dia_semana <= 5);
            }
            
            function restarDiaHabil($fecha) {
                $fecha_modificada = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
                
                // Si la fecsha resultante es un día hábil, retornarla
                if (esDiaHabil($fecha_modificada)) {
                    return $fecha_modificada;
                } else {
                    // Si la fecha es sábado (6), restar dos días para obtener el viernes
                    if (date('w', strtotime($fecha_modificada)) == 6) {
                        return date('Y-m-d', strtotime('-2 days', strtotime($fecha_modificada)));
                    } else {
                        // Si la fecha es domingo (0), restar tres días para obtener el viernes
                        return date('Y-m-d', strtotime('-3 days', strtotime($fecha_modificada)));
                    }
                }
            }
            
            //AÑO
            $consulta_periodos= $this->dataBaseService->selectConexion($id_institucion)->select("
            SELECT pd.Id, pd.Mes, pd.Inicio, pd.Fin, pe.Nombre
            FROM periodos_detalle pd
            INNER JOIN periodos pe ON pd.Id_Periodo=pe.Id
            WHERE pd.B=0 and pd.Id_Periodo={$id_periodo}
                ");

            for ($i=0; $i < count($consulta_periodos); $i++) 
                {
                    
                    $ID_Periodo=$consulta_periodos[$i]->Id;
                    $Mes=$consulta_periodos[$i]->Mes;
                    $Inicio=$consulta_periodos[$i]->Inicio;
                    $Fin=$consulta_periodos[$i]->Fin;
                    $Periodo=trim(utf8_decode($consulta_periodos[$i]->Nombre));
                    if($i==0)
                        {
                            $resultado[0]['titulo'] = $Periodo;
                        }
                    
                    //ANALISIS DE FACTURACION
                    $Facturado=0;
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT cc.Importe, cc.Id_Tipo_Comprobante
                        FROM cuenta_corriente cc
                        INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                        WHERE cc.B=0 and cct.Clase=1 and cc.Fecha>='{$Inicio}' and cct.ID<>8 and cc.Fecha<='{$Fin}'
                            ");
                    for ($k=0; $k < count($consulta_facturacion); $k++)
                        {
                            $Importe=$consulta_facturacion[$k]->Importe;
                            $Facturado=$Facturado+$Importe;
                        }
                    $Facturado=round($Facturado);
                    if($Facturado>=1)
                        {
                            $resultado[0]['meses'][$i] = $Mes;
                            $resultado[0]['emitido'][$i] = $Facturado; 
                            $Cobranza=0;
                            $consulta_cobranza = $this->dataBaseService->selectConexion($id_institucion)->select("
                                SELECT cc.Importe, cc.Id_Tipo_Comprobante
                                FROM cuenta_corriente cc
                                INNER JOIN cuenta_corriente_tipos cct ON cc.Id_Tipo_Comprobante=cct.ID
                                WHERE cc.B=0 and cct.Clase=2 and cc.Fecha>='{$Inicio}' and cct.ID<>8 and cc.Fecha<='{$Fin}' and cc.Id_Tipo_Comprobante=4
                                    ");
                            for ($k=0; $k < count($consulta_cobranza); $k++)
                                {
                                    $Importe=$consulta_cobranza[$k]->Importe;
                                    $Cobranza=$Cobranza+$Importe;
                                }
                            $Cobranza=round($Cobranza);
                            $resultado[0]['cobrado'][$i] = $Cobranza; 


                        }


                }
                    
           

            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function sintesis_medios_pago($id, $filtro)
    {

        try {
            $id_institucion=$id;
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $resultado=array();

            function esDiaHabil($fecha) {
                // Obtener el día de la semana (0: domingo, 1: lunes, ..., 6: sábado)
                $dia_semana = date('w', strtotime($fecha));
                
                // Verificar si el día de la semana es lunes a viernes (días hábiles)
                return ($dia_semana >= 1 && $dia_semana <= 5);
            }
            
            function restarDiaHabil($fecha) {
                $fecha_modificada = date('Y-m-d', strtotime('-1 day', strtotime($fecha)));
                
                // Si la fecsha resultante es un día hábil, retornarla
                if (esDiaHabil($fecha_modificada)) {
                    return $fecha_modificada;
                } else {
                    // Si la fecha es sábado (6), restar dos días para obtener el viernes
                    if (date('w', strtotime($fecha_modificada)) == 6) {
                        return date('Y-m-d', strtotime('-2 days', strtotime($fecha_modificada)));
                    } else {
                        // Si la fecha es domingo (0), restar tres días para obtener el viernes
                        return date('Y-m-d', strtotime('-3 days', strtotime($fecha_modificada)));
                    }
                }
            }

            if($filtro==1)
                {
                    $Fecha_Consulta=$FechaActual.' 00:00:00';
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT mc.Importe, mc.ID_Medio_Pago
                        FROM movimientos_caja mc
                        WHERE mc.B=0 AND mc.Fecha='{$Fecha_Consulta}'
                            ");
                }
            if($filtro==2)
                {
                    $FechaInicio = date("Y-m-01", strtotime($FechaActual));

                    // Obtener el último día del mes en curso
                    $FechaFin = date("Y-m-t", strtotime($FechaActual));

                    // Calcular la fecha del primer día del mes anterior
                    $primer_dia_mes_anterior = date("Y-m-01", strtotime("-1 month", strtotime($FechaActual)));

                    // Calcular la fecha del último día del mes anterior
                    $ultimo_dia_mes_anterior = date("Y-m-t", strtotime("-1 month", strtotime($FechaActual)));

                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT mc.Importe, mc.ID_Medio_Pago
                        FROM movimientos_caja mc
                        WHERE mc.B=0 AND mc.Fecha>='{$FechaInicio}'
                            ");                    
                }
            if($filtro==3)
                {
                    $anio_actual = date("Y");
                    // Calcular el año anterior
                    $anio_anterior = $anio_actual - 1;

                    // Calcular la fecha del primer día del año anterior
                    $primer_dia_anio_anterior = date("Y-01-01", strtotime($anio_anterior . "-01-01"));

                    // Calcular la fecha del último día del año anterior
                    $ultimo_dia_anio_anterior = date("Y-12-31", strtotime($anio_anterior . "-12-31"));

                    
                    $consulta_facturacion = $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT mc.Importe, mc.ID_Medio_Pago
                        FROM movimientos_caja mc
                        WHERE mc.B=0
                            ");   
                }
            $Ctrl_Nulidad=count($consulta_facturacion);
            if(empty($Ctrl_Nulidad))
                {
                    $Efectivo=0;
                    $Transferencia=0;
                    $Cheque=0;
                    $Electronico=0;
                }
            else
                {
                    $Efectivo=0;
                    $Transferencia=0;
                    $Cheque=0;
                    $Electronico=0;
                    for ($i=0; $i < count($consulta_facturacion); $i++) 
                    {
                        $Importe=$consulta_facturacion[$i]->Importe;
                        $ID_Medio_Pago=$consulta_facturacion[$i]->ID_Medio_Pago;
                        if($ID_Medio_Pago==1)
                            {
                                $Efectivo=$Efectivo+$Importe;
                            }
                        if($ID_Medio_Pago==2)
                            {
                                $Transferencia=$Transferencia+$Importe;
                            }
                        
                    }
                    $Efectivo=round($Efectivo);
                    $Transferencia=round($Transferencia);
                    
                }
            
            $resultado[0] = array(
                                                              'efectivo' => $Efectivo,
                                                              'transferencia'       => $Transferencia,
                                                              'cheque'       => $Cheque,
                                                              'electronico'      => $Electronico,
                                                              'filtro'       => $filtro
                       
                                 );

            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

public function cobranzas_recientes($id,$filtro)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $FechaActual=date("Y-m-d");
        $FechaActual='2023-07-10';
        $HoraActual=date("H:i:s");
        $id_institucion=$id;
        $resultado=array();
        $headers = [
          'Content-Type: application/json',
          ];
          $curl = curl_init();
          $ruta_api2='http://apirest.geofacturacion.com.ar/api/responsables/estudiantes_listado/'.$id_institucion;
          curl_setopt($curl, CURLOPT_URL,  $ruta_api2);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLOPT_HTTPGET,true);
          curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($curl, CURLOPT_POST, false);
          //curl_setopt( $curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
          //usleep(100000);
          $data = curl_exec($curl);
          curl_close($curl);
          $data = json_decode($data, true);
          $datos_alumnos = $data['data'];

          $Total=0;
          $Total_Facturado=0;
          $Total_Pendiente=0;
          $Total_NoFacturable=0;
          $fechaFormateada = date("Y-m-d", strtotime($FechaActual));

          $lista_institucion = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT inst.Ruta_Reportes, inst.Ruta_Reportes_Publicos
                                  FROM institucion inst
                                  WHERE inst.Id=1
                                      ");
            $Ruta_Reportes=$lista_institucion[0]->Ruta_Reportes;
            $Ruta_Reportes_Publicos=$lista_institucion[0]->Ruta_Reportes_Publicos;


        $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT mc.ID,mc.Fecha,mc.ID_Caja,mc.ID_Usuario,mc.Id_Responsable,mc.ID_Tipo_Movimiento,mc.Importe,mc.ID_Medio_Pago, mc.Detalle, ctm.Nombre, re.Apellido, mc.Facturado
                                  FROM movimientos_caja mc
                                  INNER JOIN caja_tipo_movimiento ctm ON mc.ID_Tipo_Movimiento=ctm.ID
                                  INNER JOIN responsabes_economicos re ON mc.Id_Responsable=re.ID
                                  WHERE mc.B=0 and mc.Fecha='{$fechaFormateada}'
                                  ORDER BY mc.ID desc
                                  LIMIT 5

                                      ");

         $ctrl_movimientos=count($lista_movientos);
         if(empty($ctrl_movimientos))
          {
              
          }
      else
          {
              
              
              for ($j=0; $j < count($lista_movientos); $j++)
                  {
                      unset($detalle_facturas);
                      unset($detalle_imputaciones);
                      $detalle_facturas=array();
                      $detalle_imputaciones=array();
                      $ID_Operacion=$lista_movientos[$j]->ID;
                      $ID_Responsable=$lista_movientos[$j]->Id_Responsable;
                      $Responsable = $lista_movientos[$j]->Apellido;
                      $Tipo_Movimiento = $lista_movientos[$j]->Nombre;
                      $FechayHora=$lista_movientos[$j]->Fecha;
                      $ID_Caja=$lista_movientos[$j]->ID_Caja;
                      $ID_User=$lista_movientos[$j]->ID_Usuario;
                      $Importe=$lista_movientos[$j]->Importe;
                      $ID_Medio_Pago=$lista_movientos[$j]->ID_Medio_Pago;
                      $Detalle=$lista_movientos[$j]->Detalle;
                      $Facturado=$lista_movientos[$j]->Facturado;
                      $Total=$Total+$Importe;
                      if($Facturado==0)
                          {
                              $Estado_Facturado='Sin Factura';
                              $Estado_Facturable=0;
                                  
                          }
                          if($Facturado==1)
                          {
                              $Estado_Facturado='Pendiente';
                              $Estado_Facturable=1;
                              
                          }
                          if($Facturado==2)
                          {   
                              $Estado_Facturado='Facturado';
                              $Estado_Facturable=1;
                             
                          }
                      //CHEQUEO DE IMPUTACIONES
                      $busqueda_imputaciones = $this->dataBaseService->selectConexion($id_institucion)->select("
                      SELECT ci.Importe,ci.Cancela,cc.Descripcion
                          FROM comprobantes_imputaciones ci
                          INNER JOIN cuenta_corriente cc ON ci.ID_Cta_Cte=cc.Id
                          WHERE ci.ID_Movimiento='{$ID_Operacion}' and ci.B=0
                              ");
                      $ctrl_imputaciones=count($busqueda_imputaciones);
                      if(empty($ctrl_imputaciones))
                          {
                              $detalle_imputaciones[0] = array(
                                  'importe'=> '0.00',
                                  'detalle_imputacion'=> 'No se han registrado Imputaciones'
                                                                     
                                     
                              );
                          }
                      else
                          {
                              for ($m=0; $m < count($busqueda_imputaciones); $m++)
                                  {
                                      $Importe_Imputacion=$busqueda_imputaciones[$m]->Importe;
                                      $Cancela=$busqueda_imputaciones[$m]->Cancela;
                                      $Descripcion=$busqueda_imputaciones[$m]->Descripcion;
                                      
                                      if($Cancela==1)
                                          {
                                              $Imputacion='A cuenta de '.$Descripcion;
                                          }
                                      else
                                          {
                                              if($Cancela==2)
                                              {
                                                  $Imputacion='Completa pago de '.$Descripcion;
                                              }
                                              else
                                              {
                                                  $Imputacion='';
                                              }
                                          }
                                     
                                    $detalle_imputaciones[$m] = array(
                                      'importe'=> $Importe_Imputacion,
                                      'detalle_imputacion'=> $Imputacion                            
                                     
                                      );
                                      
                                      
                                  }
                          }
                      
                      $Enlace_Recibo='http://geofacturacion.com.ar/'.$Ruta_Reportes.'/print_invoicep.php?id='.$ID_Operacion;
                                             
                      $busqueda_responsable= $this->dataBaseService->selectConexion($id_institucion)->select("
                          SELECT re.Nombre, re.Apellido
                          FROM responsabes_economicos re
                          WHERE re.Id='{$ID_Responsable}'
                              ");

                      $Nombre_R=$busqueda_responsable[0]->Nombre;
                      $Apellido_R=$busqueda_responsable[0]->Apellido;
                      $Responsable=$Apellido_R.', '.$Nombre_R;

                    //EXPLORO LECTURA
                      $resultado[0]['movimientos'][$j] = array(
                                                                                   
                          'id'=> $ID_Operacion,
                          'fecha'=> $FechayHora,
                          'tipo_movimiento'=> $Tipo_Movimiento,
                          'responsable'=> trim(utf8_decode($Responsable)),
                          'tipo_movimiento'=> trim(utf8_decode($Tipo_Movimiento)),
                          'caja'=> $ID_Caja,
                          'id_caja'=> $ID_Caja,
                          'usuario'=> $ID_User,
                          'id_usuario'=> $ID_User,
                          'importe'=> $Importe,
                          'id_medio_pago'=> $ID_Medio_Pago,
                          'detalle'=> trim(utf8_decode($Detalle)),
                          'detalle_imputaciones'=> $detalle_imputaciones,
                          'facturado'=> trim(utf8_decode($Estado_Facturado)),
                          'estado_facturado'=> $Estado_Facturable,
                          'enlace_recibo'=> $Enlace_Recibo,
                    );

                    $detalle = $this->dataBaseService->selectConexion($id_institucion)->select("
                                        SELECT av.Id,av.Id_Alumno
                                        FROM alumnos_vinculados av
                                        WHERE av.B=0 and av.Id_Responsable={$ID_Responsable}
                                        ORDER BY av.Id
                                    ");
                      $cant_vinculos=count($detalle);
                      
                      if($cant_vinculos>=1)
                          {
                            for ($k=0; $k < count($detalle); $k++) {
                                  //$resultado[$j]['detalle_periodo'][$k] = 1;
                                  $ID_Alumno = $detalle[$k]->Id_Alumno;
                                  foreach($datos_alumnos as $estudiante) {

                                      $id_estudiante=$estudiante["id"];
                                      if($id_estudiante==$ID_Alumno)
                                          {
                                              $Nombre_A=$estudiante["nombre"];
                                              $Apellido_A=$estudiante["apellido"];
                                              $habil=1;
                                          }
                                      }
                                  $resultado[0]['movimientos'][$j]['detalle_vinculos'][$k] = array(
                                                                                   
                                                                                    'apellido'=> $Apellido_A,
                                                                                    'nombre'=> $Nombre_A
                                                                                    
                                                                              );
                                  }
                          }
                      else
                          {
                              $resultado[$j]['detalle_vinculos'][$k] = array(
                                                                                   
                                  'apellido'=> '',
                                  'nombre'=> ''
                                  
                            );
                          }

                  }
            
          }
        return $resultado;
        
    }

    public function notificaciones($id,$id_usuario)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $FechaActual=date("Y-m-d");
        //$FechaActual='2023-08-10';
        $HoraActual=date("H:i:s");
        $id_institucion=$id;
        $resultado=array();
        
        //CONTROL DE INTERESES
        $lista_intereses= $this->dataBaseService->selectConexion($id_institucion)->select("
        SELECT Id,Vencimiento,Interes,Fin
        FROM periodos_detalle
        WHERE B=0 and Vencimiento>='{$FechaActual}' and Fin<='{$FechaActual}'

            ");
        $cant_movimientos=count($lista_intereses);
        if($cant_movimientos>=1)
            {
                for ($k=0; $k < count($lista_intereses); $k++) 
                    {
                        $ID_Periodo = $lista_intereses[$k]->Id;
                        $Vencimiento = $lista_intereses[$k]->Vencimiento;
                        $Interes = $lista_intereses[$k]->Interes;
                        $Fecha_Fin = $lista_intereses[$k]->Fin;
                        $consulta_lote_intereses= $this->dataBaseService->selectConexion($id_institucion)->select("
                        SELECT ID, Orden
                        FROM intereses_generados
                        WHERE B=0 and ID_Periodo={$ID_Periodo}
                
                            ");
                        $cant_lotes=count($consulta_lote_intereses);
                        $Generado='';
                        if(empty($cant_lotes))
                            {
                                //GENERO EL UNO
                                $Interes=round(($Interes/2),2);
                                $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                    INSERT INTO intereses_generados
                                    (ID_Periodo,Orden,Estado,Interes_Aplicado,Vencimiento,Fecha_Generacion,Hora_Generacion,ID_Generacion)
                                    VALUES ({$ID_Periodo},'1','0','{$Interes}','{$Vencimiento}','{$FechaActual}','{$HoraActual}',{$id_usuario})
                                ");
                                $Generado='Lote 1';
                            }
                        else
                            {
                                if($cant_lotes==1)
                                {
                                    if($FechaActual>$Fecha_Fin)
                                        {
                                            //GENERO EL DOS
                                            $creo_registro = $this->dataBaseService->selectConexion($id_institucion)->Insert("
                                            INSERT INTO intereses_generados
                                            (ID_Periodo,Orden,Estado,Interes_Aplicado,Vencimiento,Fecha_Generacion,Hora_Generacion,ID_Generacion)
                                            VALUES ({$ID_Periodo},'2','0','{$Interes}','{$Vencimiento}','{$FechaActual}','{$HoraActual}',{$id_usuario})
                                        ");
                                        $Generado='Lote 2';
    
                                        }
                                }
                                else
                                    {
                                        $Generado='Ya generados';
                                    }
                            }
                       
                        

                    }
            }
        else
            {
                $Generado='nada para actualizar';
            }




        //$fechaFormateada = date("Y-m-d", strtotime($FechaActual));
        $fechaFormateada=$FechaActual.' 00:00:00';
        //NOTIFICACION FACTURAS DEL DÍA
        $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                                  SELECT mc.ID
                                  FROM movimientos_caja mc
                                  WHERE mc.B=0 and mc.Fecha='{$fechaFormateada}' and mc.Facturado=1
      
                                      ");
         $cant_movimientos_diarios=count($lista_movientos);
         $resultado[0]['facturas_pendientes_diarias'] = $cant_movimientos_diarios;
        //NOTIFICACION FACTURAS ANTERIORES
        $lista_movientos = $this->dataBaseService->selectConexion($id_institucion)->select("
                SELECT mc.ID
                FROM movimientos_caja mc
                WHERE mc.B=0 and mc.Fecha<'{$fechaFormateada}' and mc.Facturado=1

                    ");
        $cant_movimientos=count($lista_movientos);
        $resultado[0]['facturas_pendientes_anteriores'] = $cant_movimientos;

        //MENSAJES SIN LEER
        $resultado[0]['mensajes'] = 0;
        $resultado[0]['mensajes_detalles'] = array();
        //NOTIFICACIONES DE SISTEMA
        $resultado[0]['sistema'] = 0;
        $resultado[0]['sistema_detalles'] = array();
        $resultado[0]['generado'] = $Generado;

        return $resultado;
        
    }

}
