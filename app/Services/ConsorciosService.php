<?php

namespace App\Services;

use App\Repositories\ConsorciosRepository;

class ConsorciosService
{

    private $ConsorciosRep;

    function __construct(ConsorciosRepository $ConsorciosRep)
    {
        $this->ConsorciosRep = $ConsorciosRep;
    }

    public function ver_edicicios($id)
    {
        try {

            return $this->ConsorciosRep->ver_edicicios($id);

        } catch (Exception $e) {

        }
    }
    public function ver_unidades($id, $id_edificio)
    {
        try {

            return $this->ConsorciosRep->ver_unidades($id, $id_edificio);

        } catch (Exception $e) {

        }
    }
    public function ver_unidad($id, $id_unidad)
    {
        try {

            return $this->ConsorciosRep->ver_unidad($id, $id_unidad);

        } catch (Exception $e) {

        }
    }

    public function show($id)
    {
        try {

            $getUsuario = $this->UsuariosRep->show($id);

            return $getUsuario;            

        } catch (Exception $e) {
            return $e;
        }
    }

    public function store($data)
    {
        try {

            $postUsuarios = $this->UsuariosRep->insertUsuario($data);

            return $postUsuarios;            

        } catch (Exception $e) {
            return $e;
        }
    }

    public function destroy($id)
    {
        try {
            $delUsuarios = $this->UsuariosRep->deleteUsuario($id);

            return $delUsuarios;            

        } catch (Exception $e) {
            return $e;
        }
    }


    public function update($data, $id)
    {
        try {
            $putUsuarios = $this->UsuariosRep->updateUsuario($data, $id);

            return $putUsuarios;            

        } catch (Exception $e) {
            return $e;
        }
    }


    public function updateDeviceID($DeviceID, $email)
    {
        try {
            $updateDeviceID = $this->UsuariosRep->updateDeviceID($DeviceID, $email);

            return $updateDeviceID;            

        } catch (Exception $e) {
            return $e;
        }
    }


}
