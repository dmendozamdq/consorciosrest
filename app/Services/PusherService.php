<?php

namespace App\Services;

use App\Repositories\PusherRepository;

class PusherService
{

    private $PusherRep;

    function __construct(PusherRepository $PusherRep)
    {
        $this->PusherRep = $PusherRep;
    }

    public function sendNotification($id,$token,$title,$body)
    {
        try {

            return $this->PusherRep->sendNotification($id,$token,$title,$body);

        } catch (Exception $e) {

        }
        
    }

    public function sendNotification_a($id,$token,$title,$body)
    {
        try {

            return $this->PusherRep->sendNotification_a($id,$token,$title,$body);

        } catch (Exception $e) {

        }
        
    }

    


    public function show($id)
    {

    }

    public function store($data)
    {

    }

    public function destroy($id)
    {

    }


    public function update($data, $id)
    {

    }


}
