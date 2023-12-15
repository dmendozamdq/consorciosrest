<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\UsuariosService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'recupero']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) 
    {
       
        $credentials = request(['email', 'password']);
        //$credentials['Contrasenia'] = md5($credentials['Contrasenia']);


        //Update DeviceID
        
            //$this->UsuariosService->updateDeviceID($DeviceID['DeviceID'], $credentials['email']);


        $token = auth()->attempt($credentials);

        if (! $token = auth()->attempt($credentials)) {
            $credentials['password'] = md5($credentials['password']);
            if (! $token = auth()->attempt($credentials)) {
                               
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            else
                {
                    //$user = User::update(['password' => bcrypt($request->password)]);
                    $DeviceID = request(['DeviceID']);
                    if ($DeviceID)
                        {
                            $DID=$DeviceID['DeviceID'];
                            $USM=$credentials['email'];
                            $result = \DB::select("
                                            UPDATE users rf
                                            SET rf.DeviceID = '{$DID}'
                                            WHERE rf.email = '{$USM}'
                                        ");
                         }
                    $USM=$credentials['email'];
                    $NewPass= bcrypt($request->password);
                    $result = \DB::select("
                                            UPDATE users rf
                                            SET rf.password = '{$NewPass}'
                                            WHERE rf.email = '{$USM}'
                                        ");

                                
                }
        }
        else
        {
            $DeviceID = request(['DeviceID']);
            if ($DeviceID)
                {
                    $DID=$DeviceID['DeviceID'];
                    $USM=$credentials['email'];
                    $result = \DB::select("
                                    UPDATE users rf
                                    SET rf.DeviceID = '{$DID}'
                                    WHERE rf.email = '{$USM}'
                                ");
                        }
        }

        
        return $this->respondWithToken($token);
      
       
    }
    /* public function login()
    {
        
        $credentials = request(['email', 'password']);
        //$credentials_o = request(['email', 'password']);
        //$credentials['password'] = md5($credentials['password']);
        
        
        if (! $token = auth()->attempt($credentials)) {
            $credentials['password'] = md5($credentials['password']);
            if (! $token = auth()->attempt($credentials)) {
                               
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            else
                {
                    //$user = User::update(['password' => bcrypt($request->password)]);
                    
                }
           
            

        }
        

        return $this->respondWithToken($token);
    }
*/
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
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

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 * 100,
            'user' => auth()->user(),
        ]);
    }
    /* protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:3',
            'password_confirmation' => 'required|string|min:3|same:password',
            'dni' => 'required|string|min:6|unique:users',
            
        
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => '¡Usuario registrado exitosamente!',
            'user' => $user
        ], 201);
    }

    public function recupero(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'dni' => 'required|string|min:6|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6',
            

        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => '¡Password regenerado con éxito!',
            'user' => $user
        ], 201);
    }
}