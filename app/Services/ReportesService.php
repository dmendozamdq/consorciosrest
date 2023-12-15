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

    public function general($id, $id_institucion)
    {
        try {

            return $this->ReportesRep->general($id, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lectura_informe($id,$mail, $id_institucion)
    {
        try {

            return $this->ReportesRep->lectura_informe($id,$mail, $id_institucion);

        } catch (Exception $e) {

        }
    }

    public function lista_informes($id,$mail, $id_institucion)
    {
        try {

            return $this->ReportesRep->lista_informes($id,$mail, $id_institucion);

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
