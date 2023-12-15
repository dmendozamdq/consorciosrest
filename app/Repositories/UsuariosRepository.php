<?php

namespace App\Repositories;

use App\Models\Usuario;
use App\Services\DataBaseService;


class UsuariosRepository
{

    private $Usuario;
    private $ID_Institucion;
    private $dataBaseService;

    function __construct(Usuario $Usuario, DataBaseService $dataBaseService)
    {
        $this->Usuario = $Usuario;
        $this->dataBaseService = $dataBaseService;
    }

    public function getAll()
    {

        try {

            $result = \DB::select("
                        SELECT  rf.*
                        FROM users rf
                        LEFT JOIN asociaciones a ON rf.id=a.ID_Familia
                        GROUP BY rf.ID
                        ");

            return $result;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function show($id)
    {

        try {
            $FechaActual=date("Y-m-d");

            /*$ID_Institucion=11;
            $result = \DB::select("
                SELECT  rf.*, a.ID_Alumno
                FROM reg_familiar rf
                LEFT JOIN asociaciones a ON rf.id=a.ID_Familia
                WHERE rf.ID={$id} and a.ID_Institucion={$ID_Institucion}
            ");
            */
            //$ID_Institucion=11;
            $result = \DB::select("
                SELECT  rf.*, a.ID_Alumno, a.ID_Institucion
                FROM users rf
                LEFT JOIN asociaciones a ON rf.id=a.ID_Familia
                WHERE rf.ID={$id}
                ");

            $return = array();
            $mail_r = $result[0]->email;





            if (isset($result[0]->ID_Alumno)){

                $hijos = array();
                for ($i=0; $i < count($result); $i++) {

                  $ID_Institucion = $result[$i]->ID_Institucion;
                  $institucion = \DB::select("
                      SELECT  inst.Institucion, inst.Carpeta, inst.URL, inst.Logo
                      FROM instituciones inst
                      WHERE inst.ID={$ID_Institucion}
                      ");

                  $Institucion = $institucion[0]->Institucion;
                  $Carpeta = $institucion[0]->Carpeta;
                  $URL = $institucion[0]->URL;
                  $Logo = $institucion[0]->Logo;

                  $Logo_Ubicacion=$URL.'/'.$Carpeta.'/imagenes/'.$Logo;

                  $resultHijos = $this->dataBaseService->selectConexion($ID_Institucion)->select("
                                                                        SELECT a.ID, a.Nombre, a.Apellido, a.DNI
                                                                        FROM alumnos a
                                                                        WHERE id = {$result[$i]->ID_Alumno}
                                                                    ");
                    $ID_Alumno_Vinculado= $resultHijos[0]->ID;
                    $Novedades=0;
                    //REVISO COMUNICADOS SIN LECTURA
                    $comunicados = $this->dataBaseService->selectConexion($ID_Institucion)->select("
                                    SELECT cd.ID
                                    FROM comunicados_detalle cd
                                    INNER JOIN alumnos a ON cd.ID_Destinatario=a.ID
                                    INNER JOIN comunicados c ON cd.ID_Comunicado=c.ID
                                    WHERE a.ID={$ID_Alumno_Vinculado} AND cd.MailD='{$mail_r}' AND cd.Tipo<>'D' AND Envio=1 AND Leido=0

                            ");
                    $Cant_Comunicados=count($comunicados);
                    $Novedades = $Novedades + $Cant_Comunicados;
                    //REVISO NOTIFICACIONES SIN LECTURA
                    $comunicados = $this->dataBaseService->selectConexion($ID_Institucion)->select("
                                    SELECT ne.ID
                                    FROM notificaciones_enviadas ne
                                    INNER JOIN alumnos a ON ne.ID_Alumno=a.ID
                                    WHERE ne.ID_Alumno={$ID_Alumno_Vinculado} AND ne.ID_Tipo_Notificacion>0 AND ne.ID_Lectura=0 and ne.Enviada=1

                            ");
                    $Cant_Comunicados=count($comunicados);
                    $Novedades = $Novedades + $Cant_Comunicados;

                    //REVISO CHATS SIN lectura
                    $chats = $this->dataBaseService->selectConexion($ID_Institucion)->select("
                                        SELECT chcc.ID, chcc.Codigo, chcc.ID_Familia
                                        FROM chat_codigo_conversaciones chcc
                                        WHERE chcc.ID_Alumno={$ID_Alumno_Vinculado} AND chcc.ID_Familia={$id}
                                          ");
                    if(empty($chats))
                        {
                          $Cantidad_Chats = 0;
                        }
                    else
                      {
                        for ($k=0; $k < count($chats); $k++) {
                          $ID_Chat = $chats[$k]->ID;
                          $Codigo = trim(utf8_decode($chats[$k]->Codigo));
                          $comunicados = $this->dataBaseService->selectConexion($ID_Institucion)->select("
                                            SELECT ch.ID
                                            FROM chat ch
                                            WHERE ch.ID_Alumno={$ID_Alumno_Vinculado} AND ch.Codigo='{$Codigo}' and ch.ID_Destinatario='{$id}' AND ch.Tipo_Destinatario=2 and ch.B=0 and ch.Leido=0 and ch.P=1
                                              ");
                        }
                      $Cantidad_Chats=count($comunicados);
                      }
                    $Novedades = $Novedades + $Cantidad_Chats;
                    //REVISO PUBLICACIONES SIN LECTURA
                    $comunicados = $this->dataBaseService->selectConexion($ID_Institucion)->select("
                                    SELECT pd.ID
                                    FROM publicaciones_detalle pd
                                    INNER JOIN alumnos a ON pd.ID_Destinatario=a.ID
                                    INNER JOIN publicaciones p ON pd.ID_Comunicado=p.ID
                                    WHERE a.ID={$ID_Alumno_Vinculado} AND pd.MailD='{$mail_r}' AND pd.Leido=0 AND p.Estado='P' AND p.ID_Nivel=a.ID_Nivel AND p.Desde<='{$FechaActual}' AND  p.Hasta>='{$FechaActual}'
                                    ORDER BY p.ID desc

                            ");
                    $Cant_Publicaciones=count($comunicados);
                    $Novedades = $Novedades + $Cant_Publicaciones;

                    //REVISO CUOTAS SIN LECTURA




                    if ($resultHijos){
                        $hijos[$i] = array(
                            'ID'        => $ID_Alumno_Vinculado,
                            'Nombre'    => trim(utf8_decode($resultHijos[0]->Nombre)),
                            'Apellido'  => trim(utf8_decode($resultHijos[0]->Apellido)),
                            'DNI'       => $resultHijos[0]->DNI,
                            'Novedades' => $Novedades,
                            'id_institucion'=> $ID_Institucion,
                            'institucion'=> $Institucion,
                            'logo'=> $Logo_Ubicacion,

                        );
                    }


                }

            }



            $return['padre'] = $result[0];

            (isset($result[0]->ID_Alumno) ? $return['padre']->ID_Alumno = $hijos : "");


            return $return;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function insertUsuario($data)
    {

        try {

            $id = $this->Usuario->insertGetId([
                                                'nombre'         => $data["nombre"],
                                                'apellido'       => $data["apellido"],
                                                'email'          => $data["email"],
                                                'estado'         => "A",
                                                'password'       => \Hash::make($data["password"]),
                                                'verificado'     => "N",
                                                'imagen'         => $data["imagen"],
                                                'fecha_registro' => date('Y-m-d H:i:s'),
                                                'fecha_mod'      => date('Y-m-d H:i:s')
                                                ]);

            return $id;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function deleteUsuario($id)
    {

        try {

            $id = $this->Usuario->where('id', $id)
                                ->update([
                                  'estado'    =>  'B',
                                  'fecha_mod' =>  date('Y-m-d H:i:s')
                                ]);

            return $id;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function updateUsuario($data, $id)
    {

        try {

            /*$id = $this->Usuario->where('id', $id)
                                ->update([
                                          'estado'    => "A",
                                          'nombre'    => $data["nombre"],
                                          'apellido'  => $data["apellido"],
                                          'password'  => \Hash::make($data["password"]),
                                          'email'     => $data["email"],
                                          'fecha_mod' => date('Y-m-d H:i:s')
                                        ]);
*/
                                        $id = $this->Usuario->where('id', $id)
                                        ->update([
                                                  'estado'    => "A",
                                                  'password'  => \Hash::make($data["password"]),
                                                  'email'     => $data["email"]
                                                  
                                                ]);

            return $id;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateDeviceID($DeviceID, $email)
    {

        try {

            $result = \DB::select("
                        UPDATE users rf
                        SET rf.DeviceID = '{$DeviceID}'
                        WHERE rf.email = '{$email}'
                    ");

            return $result;

        } catch (\Exception $e) {
            return $e;
        }
    }

}
