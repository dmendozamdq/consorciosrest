<?php

namespace App\Services;

use App\Repositories\RiteRepository;

class RiteService
{

    private $RiteRep;

    function __construct(RiteRepository $RiteRep)
    {
        $this->RiteRep = $RiteRep;
    }

    public function general($id,$mail)
    {
        try {

            return $this->RiteRep->general($id,$mail);

        } catch (Exception $e) {

        }
    }

    public function lectura_rite($id)
    {
        try {

            return $this->RiteRep->lectura_rite($id);

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
