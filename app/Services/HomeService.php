<?php

namespace App\Services;

use App\Repositories\HomeRepository;

class HomeService
{

    private $HomeRep;

    function __construct(HomeRepository $HomeRep)
    {
        $this->HomeRep = $HomeRep;
    }

    public function general($id)
    {
        try {

            return $this->HomeRep->general($id);

        } catch (Exception $e) {

        }
    }

    /*public function lectura_comunicado($id)
    {
        try {

            return $this->HomeRep->lectura_comunicado($id);

        } catch (Exception $e) {

        }
    }
*/
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
