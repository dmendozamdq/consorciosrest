<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class PublicacionesRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }

    public function lectura_publicacion($id)
    {
      $FechaActual=date("Y-m-d");
      $HoraActual=date("H:i:s");
      $lectura = \DB::connection('mysql2')->update("
                      UPDATE publicaciones_detalle
                      SET Leido=1,Fecha_Leido='{$FechaActual}',Hora_Leido='{$HoraActual}'
                      WHERE ID={$id}
                  ");

      $userpub = \DB::connection('mysql2')->select("
                      SELECT ID_Destinatario, MailD
                      FROM publicaciones_detalle
                      WHERE ID={$id}
                  ");
      $ID_Estudiante = $userpub[0]->ID_Destinatario;
      $Mail_User = $userpub[0]->MailD;

      $comunicados = \DB::connection('mysql2')->select("
                      SELECT pd.ID, pd.ID_Comunicado, p.Fecha, p.Titulo, p.Descripcion, pd.Leido
                      FROM publicaciones_detalle pd
                      INNER JOIN alumnos a ON pd.ID_Destinatario=a.ID
                      INNER JOIN publicaciones p ON pd.ID_Comunicado=p.ID
                      WHERE a.ID={$ID_Estudiante} AND pd.MailD='{$Mail_User}' AND p.Estado='P' AND p.ID_Nivel=a.ID_Nivel AND p.Desde<='{$FechaActual}' AND  p.Hasta>='{$FechaActual}'
                      ORDER BY p.ID desc

                          ");
      for ($k=0; $k < count($comunicados); $k++) {


          $Fecha_P=$comunicados[$k]->Fecha;
          if($Fecha_P<='2022-05-15')
            {
                $Titulo_P = trim(html_entity_decode(iconv('latin5', 'utf-8',$comunicados[$k]->Titulo)));
                $Descripcion_P = trim(html_entity_decode(iconv('latin5', 'utf-8',$comunicados[$k]->Descripcion)));
            }
          else
            {
                $Titulo_P = trim($comunicados[$k]->Titulo);
                $Descripcion_P = trim(utf8_decode(html_entity_decode(strip_tags($comunicados[$k]->Descripcion))));
            }
          $resultado[$k] = array(
                          'id_publicacion'    => $comunicados[$k]->ID_Comunicado,
                          'fecha' => $Fecha_P,
                          'titulo' =>  trim(utf8_decode($Titulo_P)),
                          'descripcion' =>  trim($Descripcion_P),
                          'leido'    => $comunicados[$k]->Leido,
                          'id_referencia'    => $comunicados[$k]->ID,


                        );

          }
    return $resultado;

    }

    public function general($id,$mail)
    {

        try {
/*****************************************************************************************************/
//Obtengo el nombre de la carpeta
                      $FechaActual=date("Y-m-d");
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
                                      WHERE a.ID={$id} and bs.Vigente='SI' and bs.ID_Nivel=a.ID_Nivel
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

            //Hago la consulta por la cantidad (count($periodos)) de periodos que hay

            //for ($i=0; $i < count($periodos); $i++) {


                //CONSULTO COMUNICADOS

                $comunicados = \DB::connection('mysql2')->select("
                                SELECT pd.ID, pd.ID_Comunicado, p.Fecha, p.Titulo, p.Descripcion, pd.Leido
                                FROM publicaciones_detalle pd
                                INNER JOIN alumnos a ON pd.ID_Destinatario=a.ID
                                INNER JOIN publicaciones p ON pd.ID_Comunicado=p.ID
                                WHERE a.ID={$id} AND pd.MailD='{$mail}' AND p.Estado='P' AND p.ID_Nivel=a.ID_Nivel AND p.Desde<='{$FechaActual}' AND  p.Hasta>='{$FechaActual}'
                                ORDER BY p.ID desc

                        ");

                //Armo estructura del JSON en $resultado

                for ($k=0; $k < count($comunicados); $k++) {


                    $Fecha_P=$comunicados[$k]->Fecha;
                    if($Fecha_P<='2022-05-15')
                      {
                        $Titulo_P = trim(html_entity_decode(iconv('latin5', 'utf-8',$comunicados[$k]->Titulo)));
                        $Descripcion_P = trim(html_entity_decode(iconv('latin5', 'utf-8',$comunicados[$k]->Descripcion)));
                      }
                    else
                      {
                        $Titulo_P = trim($comunicados[$k]->Titulo);
                        $Descripcion_P = trim(utf8_decode(html_entity_decode(strip_tags($comunicados[$k]->Descripcion))));

                      }

                    $resultado[$k] = array(
                                                                 'id_publicacion'    => $comunicados[$k]->ID_Comunicado,
                                                                 'fecha' => $Fecha_P,

                                                                 'titulo' =>  trim(utf8_decode($Titulo_P)),
                                                                 'descripcion' =>  trim($Descripcion_P),
                                                                 //'descripcion' =>  trim(utf8_decode($Descripcion_P)),
                                                                 //'titulo' => utf8_decode($comunicados[$k]->Titulo),
                                                                 //'descripcion' => trim(utf8_decode(html_entity_decode($comunicados[$k]->Descripcion))),
                                                                 //'descripcion' => trim(utf8_decode($comunicados[$k]->Descripcion)),
                                                                 'leido'    => $comunicados[$k]->Leido,
                                                                 'id_referencia'    => $comunicados[$k]->ID,


                    );

                }

            //}//Cierro FOR principal




/*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }
    public function contenido_publicacion($id)
    {

        try {
              /*****************************************************************************************************/
              $carpeta = \DB::connection('mysql2')->select("
                              SELECT i.Carpeta
                              FROM institucion i
                              ORDER BY i.ID
                          ");
                //Obtengo los datos de la publicación
                $publicacion_c = \DB::connection('mysql2')->select("
                                    SELECT p.Titulo, p.Fecha, p.Descripcion, p.Estado
                                    FROM publicaciones p
                                    WHERE p.ID={$id}
                                      ");
                ////$Titulo_P = trim(utf8_decode(html_entity_decode($publicacion_c[0]->Titulo)));
                $Fecha_P = $publicacion_c[0]->Fecha;
                if($Fecha_P<='2022-05-15')
                  {
                    $Titulo_P = trim(html_entity_decode(iconv('latin5', 'utf-8',$publicacion_c[0]->Titulo)));
                    $Descripcion_P = trim(html_entity_decode(iconv('latin5', 'utf-8',$publicacion_c[0]->Descripcion)));
                  }
                else
                  {
                    $Titulo_P = trim($publicacion_c[0]->Titulo);
                    $Descripcion_P = trim($publicacion_c[0]->Descripcion);
                  }



                $Estado_P = trim(utf8_decode(html_entity_decode($publicacion_c[0]->Estado)));


                if($Estado_P=='P')
                  {
                    //Consulto las imagenes que tiene la publicación
                    $imagenes_c = \DB::connection('mysql2')->select("
                                      SELECT pi.Imagen
                                      FROM pubicaciones_imagenes pi
                                      WHERE pi.ID_Publicacion={$id}
                                      ORDER BY pi.ID desc
                                        ");

                    $resultado[0] = array (
                                        'id_publicacion'    => $id,
                                        'fecha' => $Fecha_P,
                                        'titulo' => trim(utf8_decode($Titulo_P)),
                                        'descripcion' => trim(utf8_decode($Descripcion_P)),



                    );
                    for ($k=0; $k < count($imagenes_c); $k++) {
                      //$Imagen_P = trim(utf8_decode(html_entity_decode($imagenes_c[$k]->Imagen)));
                      $Imagen_P = trim($imagenes_c[$k]->Imagen);
                      $resultado[0]['galeria'][$k] = array(
                                                'link'  => 'https://geoeducacion.com.ar/'.$carpeta[0]->Carpeta.'/difusion/'.$Imagen_P,


                                            );

                    }
                  }


    /*****************************************************************************************************/


            return $resultado;


        } catch (Exception $e) {
            return $e;
        }
    }



}
