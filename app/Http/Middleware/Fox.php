<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Fox
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   

        if(!$request->query('token')){

            return $this->errorResponse('debe ingresar el token', 401);

        }

        $token = $request->query('token');
 
        $user = User::where('token', $token)->first();
        
     
        if($user){
        
            return $next($request);

        }

        return $this->errorResponse('Token no valido', 401);
       
    }
}
