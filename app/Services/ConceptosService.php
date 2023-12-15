<?php

namespace App\Services;

use App\Repositories\ConceptosRepository;

class ConceptosService
{

    private $ConceptoRep;

    function __construct(ConceptosRepository $ConceptoRep)
    {
        $this->ConceptoRep = $ConceptoRep;
    }

    

    public function agregar_conceptos($id,$nombre,$importe,$alcance)
    {
        try {

           
            return $this->ConceptoRep->agregar_conceptos($id,$nombre, $importe, $alcance);

        } catch (Exception $e) {

        }
    }

    public function modificar_conceptos($id, $nombre, $importe, $alcance, $id_concepto)
    {
        try {

            return $this->ConceptoRep->modificar_conceptos($id, $nombre, $importe, $alcance, $id_concepto);

        } catch (Exception $e) {

        }
    }

    public function borrar_conceptos($id, $id_concepto)
    {
        try {

            return $this->ConceptoRep->borrar_conceptos($id, $id_concepto);

        } catch (Exception $e) {

        }
    }

    public function mostrar_conceptos($id)
    {
        try {

            return $this->ConceptoRep->mostrar_conceptos($id);

        } catch (Exception $e) {

        }
    }
    public function mostrar_concepto($id, $id_concepto)
    {
        try {

            return $this->ConceptoRep->mostrar_concepto($id, $id_concepto);

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
