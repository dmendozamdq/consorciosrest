<?php

namespace App\Services;

use App\Repositories\ReportesRepository;

class ReportesService
{

    private $ReportesRep;

    function __construct(ReportesRepository $ReportesRep)
    {
        $this->ReportesRep = $ReportesRep;
    }

    public function general($id)
    {
        try {

            return $this->ReportesRep->general($id);

        } catch (Exception $e) {

        }
    }

    public function lectura_informe($id,$mail)
    {
        try {

            return $this->ReportesRep->lectura_informe($id,$mail);

        } catch (Exception $e) {

        }
    }

    public function lista_informes($id,$mail)
    {
        try {

            return $this->ReportesRep->lista_informes($id,$mail);

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
