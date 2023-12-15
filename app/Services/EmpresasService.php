<?php

namespace App\Services;

use App\Repositories\EmpresasRepository;

class EmpresasService
{

    private $EmpresasRep;

    function __construct(EmpresasRepository $EmpresasRep)
    {
        $this->EmpresasRep = $EmpresasRep;
    }

    public function agregar_empresa($id,$nombre,$tipo_documento,$documento,$telefono,$email,$usuario,$password,$cuit,$iibb,$inicio,$pto_vta)
    {
        try {

           
            return $this->EmpresasRep->agregar_empresa($id,$nombre,$tipo_documento,$documento,$telefono,$email,$usuario,$password,$cuit,$iibb,$inicio,$pto_vta);

        } catch (Exception $e) {

        }
    }

    public function modificar_empresa($id,$id_empresa,$nombre,$tipo_documento,$documento,$telefono,$email,$usuario,$password,$cuit,$iibb,$inicio,$pto_vta)
    {
        try {

            return $this->EmpresasRep->modificar_empresa($id,$id_empresa,$nombre,$tipo_documento,$documento,$telefono,$email,$usuario,$password,$cuit,$iibb,$inicio,$pto_vta);

        } catch (Exception $e) {

        }
    }

    public function borrar_empresa($id, $id_empresa)
    {
        try {

            return $this->EmpresasRep->borrar_empresa($id, $id_empresa);

        } catch (Exception $e) {

        }
    }

    public function listado_empresas($id)
    {
        try {

            return $this->EmpresasRep->listado_empresas($id);

        } catch (Exception $e) {

        }
    }
    
    public function ver_empresa($id, $id_empresa)
    {
        try {

            return $this->EmpresasRep->ver_empresa($id, $id_empresa);

        } catch (Exception $e) {

        }
    }

    

}
