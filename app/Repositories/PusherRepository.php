<?php

namespace App\Repositories;

use App\Models\Alumno;

use function GuzzleHttp\json_decode;

class PusherRepository
{

    private $Alumno;
    protected $connection = 'mysql2';

    function __construct(Alumno $Alumno)
    {
        $this->Alumno = $Alumno;
    }
 public function sendNotification($id,$token,$title,$body)
    {

        try
          {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $ID_Institucion = 11;

            
            
            $firebaseToken = array();
            
            $firebaseToken = array($token);
            
            $SERVER_API_KEY = 'AAAAdb4SUiU:APA91bHAmPTtrTWoeqWdGajucOUiumDBtf8q7K8o8i6AXBPrGkFcDe5Sj9kjbXQeZoHC9C3OlES4Ge6zjDk8By2p-WYuCHp66aVkZb6u1e5ad3G8ECUen8iSa5_NBi0C-axKMcV2Ilex';
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => trim(json_decode($title)),
                    "body" => trim(json_decode($body)),
                    "content_available" => true,
                    "priority" => "high",
                ]
            ];
            $dataString = json_encode($data);

            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
            dd($response);

            return $response;


          //CIERRA TRY
          }
        catch (Exception $e)
          {
            return $e;
          }
    //CIERRA FUNCION
    }

    public function sendNotification_a($id,$token,$title,$body)
    {

        try
          {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $FechaActual=date("Y-m-d");
            $HoraActual=date("H:i:s");
            $ID_Institucion = 11;

            
            
            $firebaseToken = array();
            
            $firebaseToken = array($token);
            
            $SERVER_API_KEY = 'AAAAdb4SUiU:APA91bHAmPTtrTWoeqWdGajucOUiumDBtf8q7K8o8i6AXBPrGkFcDe5Sj9kjbXQeZoHC9C3OlES4Ge6zjDk8By2p-WYuCHp66aVkZb6u1e5ad3G8ECUen8iSa5_NBi0C-axKMcV2Ilex';
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => trim($title),
                    "body" => trim($body),
                    "content_available" => true,
                    "priority" => "high",
                ]
            ];
            $dataString = json_encode($data);

            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
            dd($response);

            return $response;


          //CIERRA TRY
          }
        catch (Exception $e)
          {
            return $e;
          }
    //CIERRA FUNCION
    }


}
