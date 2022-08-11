<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class MensajeriaRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    public function lectura_mensajeria($id)
    {
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $chats = \DB::connection('mysql2')->select("
                          SELECT chcc.Codigo
                          FROM chat_codigo_conversaciones chcc
                          WHERE chcc.ID={$id}
                            ");
      $codigo_chat = trim(utf8_decode($chats[0]->Codigo));

      $lectura = \DB::connection('mysql2')->update("
                      UPDATE chat
                      SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                      WHERE Codigo='{$codigo_chat}' AND Tipo_Destinatario=2 and Leido=0 and B=0
                  ");
    }

    public function general($id,$mail)
    {

        try {
/*****************************************************************************************************/
//obtengo el ID de la familia
                      $id_familia_c =  \DB::select("
                      SELECT  rf.ID
                      FROM reg_familiar rf
                      WHERE rf.Email='{$mail}'
                      ");
                      
                      $id_familia = $id_familia_c[0]->ID;
                      //$id_familia = 6;

//Obtengo el nombre de la carpeta

                      $carpeta = \DB::connection('mysql2')->select("
                                      SELECT i.Carpeta
                                      FROM institucion i
                                      ORDER BY i.ID
                                  ");

//Obtengo el inicio y fin del ciclo lectivo

                      $periodos = \DB::connection('mysql2')->select("
                                      SELECT bs.ID, bs.ciclo_lectivo, bs.IPT, bs.FTT
                                      FROM alumnos a
                                      INNER JOIN ciclo_lectivo bs ON a.ID_Nivel=bs.ID_Nivel
                                      WHERE a.ID={$id} and bs.ID_Nivel=a.ID_Nivel
                                      ORDER BY bs.ID DESC
                                  ");

          /*****************************************************************************************************/

/*****************************************************************************************************/
/*******************************************************************************************************/
//Obtengo las asistencias por periodos
            //$carpeta = array();
            $resultado = array();
            $detalle = array();
            $comunicados = array();
            $chats = array();
            $i = 0;
            
            
            //Consulto los Chats Activos del usuario
            $chats = \DB::connection('mysql2')->select("
                                SELECT chcc.ID, chcc.Codigo, chcc.ID_Familia, chcc.ID_Docente
                                FROM chat_codigo_conversaciones chcc
                                WHERE chcc.ID_Alumno={$id} AND chcc.ID_Familia={$id_familia}
                                  ");

            if(empty($chats))
              {
                $resultado[0]['chats_activos'] = array();
              }
            else
             {
              //$resultado[0]['chats_activos'] = array(); 
              $ref=0;
              for ($k=0; $k < count($chats); $k++) {

                   $ID_Chat = $chats[$k]->ID;
                   $Codigo = trim(utf8_decode($chats[$k]->Codigo));
                   $ID_Fam = $chats[$k]->ID_Familia;
                   $ID_Docente = $chats[$k]->ID_Docente;

                   if($ID_Docente>=10000)
                     {
                       $Docente_Consulta = \DB::connection('mysql2')->select("
                                       SELECT chg.Nombre
                                       FROM chat_grupos chg
                                       WHERE chg.ID={$ID_Docente}
                                         ");
                       $Ctrl_Docente = count($Docente_Consulta);
                       if($Ctrl_Docente>=1)
                        {
                          $Docente = $Docente_Consulta[0]->Nombre;
                        }

                     }
                   else
                     {
                       $Docente_Consulta = \DB::connection('mysql2')->select("
                                       SELECT pd.Nombre, pd.Apellido
                                       FROM personal pd
                                       WHERE pd.ID={$ID_Docente} and pd.Estado='H'
                                         ");
                       $Ctrl_Docente = count($Docente_Consulta);
                       if($Ctrl_Docente>=1)
                        {
                          $Nombre_D = $Docente_Consulta[0]->Nombre;
                          $Apellido_D = $Docente_Consulta[0]->Apellido;
                          $Docente = $Apellido_D.', '.$Nombre_D;
                        }

                     }
                  //$Ctrl_Docente=1;
                  if($Ctrl_Docente>=1)
                    {
                      $Docente= trim(strip_tags(html_entity_decode(utf8_decode($Docente))));
                      $comunicados = \DB::connection('mysql2')->select("
                                        SELECT ch.Fecha, ch.Tipo_Remitente, ch.ID_Remitente, ch.Codigo, ch.Hora, ch.Mensaje, ch.Leido, ch.Fecha_Leido, ch.Hora_Leido
                                        FROM chat ch
                                        WHERE ch.ID_Alumno={$id} AND ch.Codigo='{$Codigo}' and ch.B=0 order by ch.ID desc limit 1
                                          ");
                      //Armo estructura del JSON en $resultado
                      $resultado[$i]['chats_activos'][$ref] = array(
                                                           'id_chat'    => $ID_Chat,
                                                           'fecha' => $comunicados[0]->Fecha,
                                                           'hora'  => $comunicados[0]->Hora,
                                                           //'codigo'=> $Codigo,
                                                           'usuario_destino'  => addslashes(html_entity_decode($Docente)),
                                                           'tipo_remitente'  => $comunicados[0]->Tipo_Remitente,
                                                           //trim(strip_tags(html_entity_decode(utf8_decode($$comunicados[0]->Mensaje)))),
                                                           'ultimo_mensaje'=>  trim(strip_tags(html_entity_decode(utf8_decode($comunicados[0]->Mensaje)))),
                                                           'leido'    => $comunicados[0]->Leido,
                                                           'fecha_leido'    => $comunicados[0]->Fecha_Leido,
                                                           'hora_leido'    => $comunicados[0]->Hora_Leido
                                                          );
                      $ref++;

                    }
                  
                    

               }
            }


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
      }

    public function historial_mensajes($id)
    {

        try {
    /*****************************************************************************************************/
    //Obtengo el Código de Chat y el Destinatario
    $chats = \DB::connection('mysql2')->select("
                        SELECT chcc.Codigo, chcc.ID_Docente, chcc.ID_Alumno
                        FROM chat_codigo_conversaciones chcc
                        WHERE chcc.ID={$id}
                          ");
    $codigo_chat = trim(utf8_decode($chats[0]->Codigo));
    $ID_Docente = $chats[0]->ID_Docente;
    $ID_Alumno = $chats[0]->ID_Alumno;
    if($ID_Docente>=10000)
      {
        $Docente_Consulta = \DB::connection('mysql2')->select("
                        SELECT chg.Nombre
                        FROM chat_grupos chg
                        WHERE chg.ID={$ID_Docente}
                          ");
        $Docente = $Docente_Consulta[0]->Nombre;
      }
    else
      {
        $Docente_Consulta = \DB::connection('mysql2')->select("
                        SELECT pd.Nombre, pd.Apellido
                        FROM personal pd
                        WHERE pd.ID={$ID_Docente}
                          ");
        $Nombre_D = $Docente_Consulta[0]->Nombre;
        $Apellido_D = $Docente_Consulta[0]->Apellido;
        $Docente = $Apellido_D.', '.$Nombre_D;
      }


    /*****************************************************************************************************/

    /*****************************************************************************************************/
    /*******************************************************************************************************/
    //Obtengo las asistencias por periodos

            $resultado = array();
            $historial = array();

            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            //for ($i=0; $i < count($periodos); $i++) {

                //CONSULTO EL LOS EVENTOS PROXIMOS
                $historial = \DB::connection('mysql2')->select("
                                  SELECT ch.id, ch.Fecha, ch.Tipo_Remitente, ch.ID_Remitente, ch.Codigo, ch.Hora, ch.Mensaje, ch.Leido, ch.Fecha_Leido, ch.Hora_Leido
                                  FROM chat ch
                                  WHERE ch.ID_Alumno={$ID_Alumno} AND ch.Codigo='{$codigo_chat}' and ch.B=0 order by ch.ID desc
                                    ");

                for ($k=0; $k < count($historial); $k++) {

                $Tipo_Remitente = $historial[$k]->Tipo_Remitente;
                $ID_Remitente = $historial[$k]->ID_Remitente;
                if($Tipo_Remitente==1)
                  {
                    if($ID_Remitente>=10000)
                      {
                        $Remitente_Consulta = \DB::connection('mysql2')->select("
                                        SELECT chg.Nombre
                                        FROM chat_grupos chg
                                        WHERE chg.ID={$ID_Remitente}
                                          ");
                        $Remitente = trim(utf8_decode($Remitente_Consulta[0]->Nombre));
                      }
                    else
                      {
                        $Remitente_Consulta = \DB::connection('mysql2')->select("
                                        SELECT pd.Nombre, pd.Apellido
                                        FROM personal pd
                                        WHERE pd.ID={$ID_Remitente}
                                          ");
                        $Nombre_D = trim(utf8_decode($Remitente_Consulta[0]->Nombre));
                        $Apellido_D = trim(utf8_decode($Remitente_Consulta[0]->Apellido));
                        $Remitente = $Apellido_D . ', ' . $Nombre_D;
                      }
                  }
                else
                  {
                    $Remitente='Yo';
                  }
                $resultado[$k] = array(
                                                        'id' => $historial[$k]->id,
                                                        'fecha' => $historial[$k]->Fecha,
                                                        'hora'  => $historial[$k]->Hora,
                                                        //'codigo'=> $Codigo,
                                                        'usuario_destino'  => html_entity_decode($Remitente),
                                                        'tipo_remitente'  => $historial[$k]->Tipo_Remitente,
                                                        'ultimo_mensaje'=> trim(strip_tags(html_entity_decode(utf8_decode($historial[$k]->Mensaje)))),
                                                        'leido'    => $historial[$k]->Leido,
                                                        'fecha_leido'    => $historial[$k]->Fecha_Leido,
                                                        'hora_leido'    => $historial[$k]->Hora_Leido,
                                                        );
                }

            //}//Cierro FOR principal





    /*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }

    public function enviar_chat($id,$chat)
    {
      try {

              date_default_timezone_set('America/Argentina/Buenos_Aires');
              $FechaActual=date("Y-m-d");
              $HoraActual=date("H:i:s");
              $chats = \DB::connection('mysql2')->select("
                                  SELECT chcc.Codigo, chcc.ID_Docente, chcc.ID_Alumno, chcc.ID_Familia
                                  FROM chat_codigo_conversaciones chcc
                                  WHERE chcc.ID={$id}
                                    ");
              $codigo_chat = trim(utf8_decode($chats[0]->Codigo));
              $ID_Docente = $chats[0]->ID_Docente;
              $ID_Alumno = $chats[0]->ID_Alumno;
              $ID_Familia = $chats[0]->ID_Familia;

              $consulta_nivel = \DB::connection('mysql2')->select("
                              SELECT a.ID_Nivel
                              FROM alumnos a
                              WHERE a.ID={$ID_Alumno}
                          ");

              $ID_Nivel = $consulta_nivel[0]->ID_Nivel;
              $chat=utf8_encode($chat);
              $envio_chat = \DB::connection('mysql2')->Insert("
                              INSERT INTO chat
                              (Fecha,Hora,ID_Remitente,Tipo_Remitente,ID_Destinatario,Tipo_Destinatario,Mensaje,Codigo,ID_Alumno,ID_Nivel,P)
                              VALUES
                              ('{$FechaActual}','{$HoraActual}',{$ID_Familia},2,{$ID_Docente},1,'{$chat}','{$codigo_chat}',{$ID_Alumno},{$ID_Nivel},1)
                          ");
              return $id;

          } catch (\Exception $e) {
              return $e;
          }
        }

          public function nuevo_chat($id,$id_alumno,$mail,$chat)
          {
            try {

                    date_default_timezone_set('America/Argentina/Buenos_Aires');
                    $FechaActual=date("Y-m-d");
                    $HoraActual=date("H:i:s");
                    //obtengo el ID de la familia
                    $id_familia_c =  \DB::select("
                                      SELECT  rf.ID
                                      FROM reg_familiar rf
                                      WHERE rf.Email='{$mail}'
                                      ");
                    $id_familia = $id_familia_c[0]->ID;
                    //VERIFICO QUE NO HAYA CHAT EXISTENTE PREVIAMENTE
                    $conversacion_previa=  \DB::connection('mysql2')->select("
                                      SELECT chcc.ID, chcc.Codigo
                                      FROM chat_codigo_conversaciones chcc
                                      WHERE chcc.ID_Docente={$id} AND chcc.ID_Familia={$id_familia} AND ID_Alumno={$id_alumno}
                                      ");

                    if(empty($conversacion_previa))
                      {

                        //NO EXISTE CHAT y GENERO CODIGO Y CONVERSACION
                        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //posibles caracteres a usar
                        $numerodeletras=10;
                        $Cadena_Aleatoria = ""; //variable para almacenar la cadena generada
                        for($i=0;$i<$numerodeletras;$i++)
                           {
                             $Cadena_Aleatoria .= substr($caracteres,rand(0,strlen($caracteres)),1);
                           }
                        $codigo_chat=$Cadena_Aleatoria;
                        $generacion_conversacion = \DB::connection('mysql2')->insert("
                                        INSERT INTO chat_codigo_conversaciones
                                        (ID_Docente,ID_Familia,ID_Alumno,Codigo,Fecha,Hora)
                                        VALUES
                                        ({$id},{$id_familia},{$id_alumno},'{$codigo_chat}','{$FechaActual}','{$HoraActual}')
                                    ");

                        $conversacion_previa =  \DB::connection('mysql2')->select("
                                                  SELECT chcc.ID, chcc.Codigo
                                                  FROM chat_codigo_conversaciones chcc
                                                  WHERE chcc.ID_Docente={$id} AND chcc.ID_Familia={$id_familia} AND ID_Alumno={$id_alumno}
                                                  ");
                        $codigo_chat = trim(utf8_decode($conversacion_previa[0]->Codigo));
                        $id_chat = $conversacion_previa[0]->ID;
                      }else{
                        $codigo_chat = trim(utf8_decode($conversacion_previa[0]->Codigo));
                        $id_chat = $conversacion_previa[0]->ID;
                      }
                    $consulta_nivel = \DB::connection('mysql2')->select("
                                    SELECT a.ID_Nivel
                                    FROM alumnos a
                                    WHERE a.ID={$id_alumno}
                                ");

                    $ID_Nivel = $consulta_nivel[0]->ID_Nivel;
                    $envio_chat = \DB::connection('mysql2')->Insert("
                                    INSERT INTO chat
                                    (Fecha,Hora,ID_Remitente,Tipo_Remitente,ID_Destinatario,Tipo_Destinatario,Mensaje,Codigo,ID_Alumno,ID_Nivel,P)
                                    VALUES
                                    ('{$FechaActual}','{$HoraActual}',{$id_familia},2,{$id},1,'{$chat}','{$codigo_chat}',{$id_alumno},{$ID_Nivel},1)
                                ");

                    return $id_chat;

                } catch (\Exception $e) {
                    return $e;
                }



    }
    public function destinatarios_chats($id)
    {

        try {
    /*****************************************************************************************************/
    //Obtengo el Nivel y Curso del Estudiante
    $consulta_nivel = \DB::connection('mysql2')->select("
                    SELECT a.ID_Nivel, a.ID_Curso
                    FROM alumnos a
                    WHERE a.ID={$id}
                ");

    $ID_Nivel = $consulta_nivel[0]->ID_Nivel;
    $ID_Curso = $consulta_nivel[0]->ID_Curso;

    //Obtengo el Ciclo Lectivo Vigente
    $id_ciclo_c =  \DB::connection('mysql2')->select("
                        SELECT  cl.ID
                        FROM ciclo_lectivo cl
                        WHERE cl.ID_Nivel='{$ID_Nivel}' AND cl.Vigente='SI'
                ");
    $id_ciclo = $id_ciclo_c[0]->ID;


    $grupos = \DB::connection('mysql2')->select("
                        SELECT chg.ID, chg.Nombre, chg.Referencia
                        FROM chat_grupos chg
                        WHERE chg.ID_Nivel={$ID_Nivel} ORDER BY Nombre
                          ");
    $count_array = 0;
    $resultado = array();
    $profes = array();

    for ($k=0; $k < count($grupos); $k++) {

      $ID_Grupo=$grupos[$k]->ID;
      $Grupo=trim(utf8_decode($grupos[$k]->Nombre));
      $Grupo=addslashes(html_entity_decode($Grupo));
      $Ref_Grupo=trim(utf8_decode($grupos[$k]->Referencia));
      if($Ref_Grupo=='PF')
        {
          $profesores_c = \DB::connection('mysql2')->select("
                              SELECT mg.ID_Personal, mg.ID, per.Nombre, per.Apellido
                              FROM materias_grupales mg
                              INNER JOIN personal per ON mg.ID_Personal=per.ID
                              INNER JOIN grupos gr ON mg.ID=gr.ID_Materia_Grupal
                              WHERE mg.ID_Nivel={$ID_Nivel} AND mg.ID_Ciclo_Lectivo={$id_ciclo} AND gr.ID_Alumno={$id} AND gr.ID_Ciclo_Lectivo={$id_ciclo}  ORDER BY per.Apellido
                                ");
          for ($j=0; $j < count($profesores_c); $j++)
            {
              $ID_Grupo=$profesores_c[$j]->ID_Personal;
              $Apellido_D=trim(utf8_decode($profesores_c[$j]->Apellido));
              $Nombre_D=trim(utf8_decode($profesores_c[$j]->Nombre));
              $Grupo = $Apellido_D.', '.$Nombre_D;
              $resultado[$count_array] = array(
                                                      'id_grupo' => $ID_Grupo,
                                                      'nombre'=> $Grupo,
                                                      'referencia'=> $Ref_Grupo,
                                                  );
              $count_array = $count_array + 1;



            }


        }
      else
        {
          if(($Ref_Grupo=='MG') or ($Ref_Grupo=='MI') or ($Ref_Grupo=='PR'))
            {
              $maestros_c = \DB::connection('mysql2')->select("
                                  SELECT cur.ID_Preceptor, cur.ID_Pareja, cur.ID_Pareja2, cur.ID_Pareja3
                                  FROM cursos cur
                                  WHERE cur.ID={$ID_Curso}
                                    ");

                  $ID_MG1=$maestros_c[0]->ID_Preceptor;
                  $ID_MG2=$maestros_c[0]->ID_Pareja;
                  $ID_MG3=$maestros_c[0]->ID_Pareja2;
                  $ID_MG4=$maestros_c[0]->ID_Pareja3;
                  $maestro_c = \DB::connection('mysql2')->select("
                                      SELECT per.Nombre, per.Apellido
                                      FROM personal per
                                      WHERE per.ID={$ID_MG1}
                                        ");
                  $Apellido_D=$maestro_c[0]->Apellido;
                  $Nombre_D=$maestro_c[0]->Nombre;
                  $Grupo= $Apellido_D.', '.$Nombre_D;
                  $resultado[$count_array] = array(
                                                          'id_grupo' => $ID_MG1,
                                                          'nombre'=> $Grupo,
                                                          'referencia'=> $Ref_Grupo,
                                                      );
                  $count_array = $count_array + 1;

                  if($ID_MG2>=1)
                    {
                      $maestro_c = \DB::connection('mysql2')->select("
                                          SELECT per.Nombre, per.Apellido
                                          FROM personal per
                                          WHERE per.ID={$ID_MG2}
                                            ");
                      $Apellido_D=$maestro_c[0]->Apellido;
                      $Nombre_D=$maestro_c[0]->Nombre;
                      $Grupo= $Apellido_D.', '.$Nombre_D;
                      $resultado[$count_array] = array(
                                                              'id_grupo' => $ID_MG2,
                                                              'nombre'=> $Grupo,
                                                              'referencia'=> $Ref_Grupo,
                                                          );
                      $count_array = $count_array + 1;
                    }
                  if($ID_MG3>=1)
                      {
                        $maestro_c = \DB::connection('mysql2')->select("
                                            SELECT per.Nombre, per.Apellido
                                            FROM personal per
                                            WHERE per.ID={$ID_MG3}
                                              ");
                        $Apellido_D=$maestro_c[0]->Apellido;
                        $Nombre_D=$maestro_c[0]->Nombre;
                        $Grupo= $Apellido_D.', '.$Nombre_D;
                        $resultado[$count_array] = array(
                                                                'id_grupo' => $ID_MG3,
                                                                'nombre'=> $Grupo,
                                                                'referencia'=> $Ref_Grupo,
                                                            );
                        $count_array = $count_array + 1;
                      }
                  if($ID_MG4>=1)
                          {
                            $maestro_c = \DB::connection('mysql2')->select("
                                                SELECT per.Nombre, per.Apellido
                                                FROM personal per
                                                WHERE per.ID={$ID_MG4}
                                                  ");
                            $Apellido_D=$maestro_c[0]->Apellido;
                            $Nombre_D=$maestro_c[0]->Nombre;
                            $Grupo= $Apellido_D.', '.$Nombre_D;
                            $resultado[$count_array] = array(
                                                                    'id_grupo' => $ID_MG4,
                                                                    'nombre'=> $Grupo,
                                                                    'referencia'=> $Ref_Grupo,
                                                                );
                            $count_array = $count_array + 1;
                          }


            }
          else
            {
              $resultado[$count_array] = array(
                                                      'id_grupo' => $ID_Grupo,
                                                      'nombre'=> $Grupo,
                                                      'referencia'=> $Ref_Grupo,
                                                  );
              $count_array = $count_array + 1;
            }
        }




    }





            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }


}
