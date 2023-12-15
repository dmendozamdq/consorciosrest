<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ConsorciosService;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\PasswordBroker;
use Illuminate\Support\Facades\Auth;

class ConsorciosController extends Controller
{

    private $ConsorciosService;

    function __construct(ConsorciosService $ConsorciosService)
    {
        $this->ConsorciosService = $ConsorciosService;
    }

    /**
     * Obtiene todos los Usuarios
     *
     * @return Json Response [success, data, messages]
     */
    public function ver_edificios($id)
    {
        $informe = $this->ConsorciosService->ver_edificios($id);

        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
        

    }

    public function ver_unidades($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->ConsorciosService->ver_unidades($id,  $data['id_edificio']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
        
    }

    public function ver_unidad($id, Request $request)
    {
        $data = $request->all();
        $informe = $this->ConsorciosService->ver_unidad($id,  $data['id_unidad']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]);
        
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Insert de Usuarios
     *
     * @param  Request  $request
     * @return Json Response [success, data, messages]
     */
    public function store(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'nombre'    => 'required|alpha_num|max:50',
            'apellido'  => 'required|alpha_num|max:50',
            'password'  => 'required|alpha_num|min:8',
            'email'     => 'required|email|max:50',
            'imagen'    => 'required|max:250',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'data'    => '',
                'messages' => $validator->messages()
            ]);
        }
        
        $result = $this->UsuariosService->store($data);

        if (is_numeric($result) && $result !== 0)
            $getResult = $this->UsuariosService->show($result);

        return response()->json([
            'success' => 'true',
            'data'    => $getResult,
            'messages' => $validator->messages()
        ]);
    }

    /**
     * Select de Usuarios
     *
     * @param  $id
     * @return Json Response [success, data, messages]
     */
    public function show($id)
    {

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'data'    => '',
                'messages' => $validator->messages()
            ]);
        }

        $result = $this->UsuariosService->show($id);

        return response()->json([
            'success' => 'true',
            'data'    => $result,
            'messages' => $validator->messages()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update de Usuarios
     *
     * @param  Request  $request, $id
     * @return Json Response [success, data, messages]
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'nombre'    => 'required|alpha_num|max:50',
            'apellido'  => 'required|alpha_num|max:50',
            'password'  => 'required|alpha_num|min:8',
            'email'     => 'required|email|max:50',
            'imagen'    => 'required|max:250',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'false',
                'data'    => '',
                'messages' => $validator->messages()
            ]);
        }
        
        $result = $this->UsuariosService->update($data, $id);

        if (is_numeric($result) && $result !== 0)
        {
            $getResult = $this->UsuariosService->show($id);

            return response()->json([
                'success' => 'true',
                'data'    => $getResult,
                'messages' => $validator->messages()
            ]);            
        }

        return response()->json([
            'success' => 'false',
            'data'    => null,
            'messages' => 'Usuario no encontrado'
        ]); 
    

    }

    /**
     * Delete de Usuarios
     *
     * @param  $id
     * @return Json Response [success, data, messages]
     */
    public function destroy($id)
    {
        $result = $this->UsuariosService->destroy($id);

        if (is_numeric($result) && $result !== 0)
        {
            return response()->json([
                'success' => 'true',
                'data'    => $result,
                'messages' => 'Usuario dado de baja con exito'
            ]);
        }
   
        return response()->json([
                'success' => 'true',
                'data'    => $result,
                'messages' => 'Usuario no encontrado'
        ]);
    }

    /**
     * Obtener el usuario y contraseña
     *
     * @param string $email
     * @param string $password     
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) 
    {
        $credentials = request(['Email', 'Contrasenia']);
        $credentials['Contrasenia'] = md5($credentials['Contrasenia']);


        //Update DeviceID
        $DeviceID = request(['DeviceID']);
        if ($DeviceID)
            $this->UsuariosService->updateDeviceID($DeviceID['DeviceID'], $credentials['Email']);


        $token = auth()->attempt($credentials);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);

    }


    /**
     * Obtener token array
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }



}















