<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\ApiController;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $usuarios = User::with('plan')->get();

  
        return $this->showAll($usuarios);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $reglas = [

            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:users',
            'plan_id' => 'required',
            'password' => 'required:min:6|confirmed'

        ];

    
        $this->validate($request, $reglas);

        
        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $data['verified'] = User::USUARIO_NO_VERIFICADO;
        $data['verification_token'] = User::generarVerificationToken();
        $data['admin'] = User::USUARIO_REGULAR;
        $data['active'] = User::USUARIO_ACTIVO;
        $data['expiration_date'] = Carbon::now()->addYear()->format('Y-m-d');
        
        $usuario = User::create($data);

        return $this->showOne($usuario, 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function login(Request $request)
    {

        $reglas = [

            'email' => 'required|email',
            'password' => 'required'

        ];

        $this->validate( $request, $reglas );

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password ] )){

            $user = User::where('email',$request->email)->first();

            $token = $user->createToken('apifox')->accessToken;

            $data = [
                'user'  => $user,
                'token' => $token
               
            ];

            return $this->showMessage($data,200);

        }else{

            return $this->errorResponse(['login' => ['El usuario o la contraseña no coinciden con ninguna cuenta']], 401);

        }

    }
    
    public function logout(){

        $user = auth()->user();
        
        $user->token()->delete();
        $user->save();
        
        $data = [
            
            'success' => true,
            'message' => 'Bye'
        ];

        return $this->showMessage($data,200);

    }

    public function me(Request $request){

        $user = $request->user();

        return $this->showOne($user);

    }



}
