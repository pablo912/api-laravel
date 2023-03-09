<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\ApiController;
use App\Mail\Recover;
use App\Mail\UserCreated;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     public function __construct()
     {

         $this->middleware('auth:api')
         ->except(['store', 'verify', 'resend','login','recoverpassword','userForRememberToken','resetPassword']);

     }

    public function index(Request $request)
    {   
        $user = $request->user();


        if(!$user->esAdministrador()){

            $usuario = $user;

            $usuario['plan_id'] = $user->plan;

            unset($usuario['plan_id']);

            return $this->showAll(collect([$usuario]));

        }else{

            $usuarios = User::with('plan')->get();

  
            return $this->showAll($usuarios);

        }



     
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
        $data['token'] = User::generarVerificationToken();
        $data['admin'] = User::USUARIO_REGULAR;
        $data['active'] = User::USUARIO_ACTIVO;
        $data['queries'] = 0;
        $data['limit'] = '200';
        $data['expiration_date'] = Carbon::now()->addMonth()->format('Y-m-d');
        
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
    public function update(Request $request, User $user)
    {
        $reglas = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::USUARIO_ADMINISTRADOR . ',' . User::USUARIO_REGULAR,
        ];

        $this->validate($request, $reglas);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::USUARIO_NO_VERIFICADO;
            $user->verification_token = User::generarVerificationToken();
            $user->email = $request->email;
        }


        if ($request->has('plan_id')) {

            if($user->plan_id == 1 && $request->plan_id == 2){

                $user->plan_id = $request->plan_id;
                $user->expiration_date = Carbon::now()->addYear()->format('Y-m-d');
                $user->limit = 'ilimitado';

            }else{

                $user->plan_id = $request->plan_id;
                $user->expiration_date = Carbon::now()->addMonth()->format('Y-m-d');
                $user->limit = '100';

            }

       

        }


        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')) {
            $this->allowedAdminAction();
        
            if (!$user->esVerificado()) {
                return $this->errorResponse('Unicamente los usuarios verificados pueden cambiar su valor de administrador', 409);
            }

            $user->admin = $request->admin;
        }

        if (!$user->isDirty()) {
            return $this->errorResponse('Se debe especificar al menos un valor diferente para actualizar', 422);
        }

        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
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

            // $token = $user->createToken('apifox')->accessToken;

            $data = [
                'user'  => $user,
                'token' => $user->api_token
               
            ];

            return $this->showMessage($data,200);

        }else{

            return $this->errorResponse(['login' => ['El usuario o la contraseña no coinciden con ninguna cuenta']], 401);

        }

    }
    
    public function logout(Request $request){

        $user = $request->user();
        
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


    public function verify($token){


        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;

        $user->save();


        $response = [

                    'message' =>  'La cuenta ha sido verificado'

        ];

         return $this->showMessage($response);

    }


    public function resend(User $user){


        if($user->esVerificado()){

            return $this->errorResponse('Este usuario ya ha sido verificado', 409);

            
        }

        retry(5, function() use ($user)  {
            Mail::to($user)->send(new UserCreated($user));

       },100);

        return $this->showMessage('El correo de verificacion se ha reenviado');



    }

    public function recoverpassword(Request $request){

        $rules = [

            'email' => 'required|email'

        ];

        $this->validate( $request, $rules );

        $user = User::where('email', $request->email)->first();

        if(!$user){

            return $this->errorResponse('No existe ningun usuario con este email', 409);
        }

        $user['remember_token'] = User::generarRecoverToken();

        $user->save();
        
        retry(5, function() use ($user)  {

            Mail::to($user)->send(new Recover($user));

       },100);

        return $this->showMessage('Recordatorio de contraseña enviado');    


    }

    public function userForRememberToken($token){

        $user = User::where('remember_token', $token)->firstOrFail();


        return $this->showOne($user);


    }


    public function resetPassword(Request $request){


        $rules = [

            'email' => 'required',
            'token' => 'required',
            'password' => 'required|confirmed',
    
        ];


        $this->validate($request, $rules);

        $user = User::where('remember_token', $request->token)->firstOrFail();
        $user->password = Hash::make($request->password);

        $user->remember_token = null;

        $user->save();

        return $this->showMessage('Su contraseña ha sido restablecida',200);

    }

}
