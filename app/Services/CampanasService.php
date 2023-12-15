<?php

namespace App\Services;

use App\Repositories\CampanasRepository;

class CampanasService
{

    private $CampanasRep;

    function __construct(CampanasRepository $CampanasRep)
    {
        $this->CampanasRep = $CampanasRep;
    }

    

    public function agregar_campana($id, $nombre, $importe, $alcance, $conceptos, $cursos, $id_usuario)
    {
        try {

            return $this->CampanasRep->agregar_campana($id, $nombre, $importe, $alcance, $conceptos, $cursos, $id_usuario);

        } catch (Exception $e) {

        }
    }

    public function modificar_campana($id, $nombre, $importe, $alcance, $conceptos, $cursos, $id_usuario, $id_campana)
    {
        try {

            return $this->CampanasRep->modificar_campana($id,$nombre, $importe, $alcance, $conceptos, $cursos, $id_usuario, $id_campana);

        } catch (Exception $e) {

        }
    }

    public function borrar_campana($id, $id_campana)
    {
        try {

            return $this->CampanasRep->borrar_campana($id, $id_campana);

        } catch (Exception $e) {

        }
    }


    public function listado_campanas($id)
    {
        try {

            return $this->CampanasRep->listado_campanas($id);

        } catch (Exception $e) {

        }
    }

    public function mostrar_campana($id, $id_campana)
    {
        try {

            return $this->CampanasRep->mostrar_campana($id, $id_campana);

        } catch (Exception $e) {

        }
    }
    

}
