<?php

namespace App\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{

    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    public function render($request, Throwable $exception){

        if($exception instanceof ValidationException){
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if($exception instanceof ModelNotFoundException){
            $modelo = class_basename($exception->getModel());
            return $this->errorResponse("No existe ninguna intancia del ${modelo} con el id especifico", 404);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->errorResponse('No posee permisos para ejecutar esta accion', 403);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('No se encontro la url espeficada', 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('El metodo ' .  $request->method() . ' especificado no es valido', 405);
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if ($exception instanceof QueryException) {

            $codigo = $exception->errorInfo[1];

            if($codigo == 1451){
               return $this->errorResponse('No se puede eliminar de forma permanente el recurso porque esta relacionado con algun otro', 409);     
            }
           
        }

        if(config('app.debug')){

            return parent::render($request, $exception);
        }else{
            return $this->errorResponse('Falla inesperada intente luego', 500);
        }

        

        
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        return $this->errorResponse($errors, 422);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('No auntenticado', 401);
    }


}