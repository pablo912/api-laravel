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

        $token = $request->header('token-fox');

        $user = User::where('verification_token', $token)->first();
        
     

        if($user){

            return $next($request);

        }

        return $this->errorResponse('Token no valido', 401);
       
    }
}
