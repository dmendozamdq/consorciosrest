<?php

namespace App\Services;

use App\Repositories\EstudiantesRepository;

class EstudiantesService
{

    private $EstudiantesRep;

    function __construct(EstudiantesRepository $EstudiantesRep)
    {
        $this->EstudiantesRep = $EstudiantesRep;
    }

    public function index()
    {
        try {

            return $this->EstudiantesRep->index();

        } catch (Exception $e) {

        }
        /*try {

            $getEstudiantes = $this->EstudiantesRep->getAll();

            return $getEstudiantes;            

        } catch (\Exception $e) {
            return $e;
        }
        */
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
