<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Sire;
use App\Models\User;
use App\Services\DniService;
use App\Services\RucService;
use App\Traits\ApiResponser;
use App\Traits\ApiResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{   
    use ApiResult, ApiResponser;

    private $rucService;
    private $dniService;

    public function __construct( RucService $rucService, DniService $dniService  )
    {
        
  
        $this->rucService = $rucService;
        $this->dniService = $dniService;
    }


    public function dni(Request $request, $dni)
    {   

        $user = $request->user();

     
        if (strlen($dni) != 8 || !is_numeric($dni)) {

            return $this->errorResponse(['dni' => ['Debe Ingresar 8 digitos numericos.']], 409);

        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }


        $result = $this->dniService->processDni($dni,false)->original;

        $user->queries += 1;
        $user->save(); 

        return $result;
       
    }

    public function dniplus(Request $request,$dni)
    {   

        $user = $request->user();

 

        if (strlen($dni) != 8 || !is_numeric($dni)) {
            return $this->errorResponse(['dni' => ['Debe Ingresar 8 digitos numericos.']], 409);
        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }


        $result=$this->dniService->processDni($dni,true)->original;

        $user->queries += 1;
        $user->save(); 
        
        
        return $result;
       
    }


    public function ruc(Request $request,$ruc){

        $user = $request->user();


        if( strlen($ruc)!=11 || !is_numeric($ruc)){
            
            return $this->errorResponse('Formato RUC no valido.', 409);
        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }

        $user->queries += 1;
        $user->save(); 
        return $this->rucService->consultar_ruc($ruc);

    }

    public function rusplus(Request $request,$ruc){

        $user = $request->user();

      

        if( strlen($ruc)!=11 || !is_numeric($ruc)){
            
            return $this->errorResponse('Formato RUC no valido.', 409);
            
        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }
 
        $user->queries += 1;
        $user->save(); 
        return $this->rucService->rusplus($ruc);


    }



    // VISUAL FOX PRO

    public function dnivfp(Request $request, $dni)
    {   

        $user = $request->user();

     
        if (strlen($dni) != 8 || !is_numeric($dni)) {

            return $this->errorResponse(['dni' => ['Debe Ingresar 8 digitos numericos.']], 409);

        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }


        $result = $this->dniService->processDni($dni,false)->original;

        $user->queries += 1;
        $user->save(); 

        return $result;
       
    }

    public function dniplusvfp(Request $request,$dni)
    {   

        $user = $request->user();

 

        if (strlen($dni) != 8 || !is_numeric($dni)) {
            return $this->errorResponse(['dni' => ['Debe Ingresar 8 digitos numericos.']], 409);
        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }


        $result=$this->dniService->processDni($dni,true)->original;

        $user->queries += 1;
        $user->save(); 
        
        
        return $result;
       
    }

    public function rucvfp(Request $request,$ruc){

        $user = $request->user();


        if( strlen($ruc)!=11 || !is_numeric($ruc)){
            
            return $this->errorResponse('Formato RUC no valido.', 409);
        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }

        $user->queries += 1;
        $user->save(); 
        return $this->rucService->consultar_ruc($ruc);

    }

    public function rusplusvfp(Request $request,$ruc){

        $user = $request->user();

      

        if( strlen($ruc)!=11 || !is_numeric($ruc)){
            
            return $this->errorResponse('Formato RUC no valido.', 409);
            
        }

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }
 
        $user->queries += 1;
        $user->save(); 
        return $this->rucService->rusplus($ruc);


    }


    public function tipocambio($desde,$hasta)
    {
        
        $user_auth= User::where('id',auth()->user()->id)->first();

        return $this->rucService->consultartipocambio($desde,$hasta);
       
    }

    public function tipocambiovfp($desde,$hasta)
    {
        
        $user_auth= User::where('id',auth()->user()->id)->first();

        return $this->rucService->consultartipocambio($desde,$hasta);
       
    }



    protected function accessToken($request){

        $rules = [

            'ruc' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'usuario' => 'required',
            'clave_sol' => 'required' 

        ];

        $this->validate($request, $rules);

        $campos = $request->all(); 
        
        $campos['usuario'] = $request->ruc.$request->usuario;
        
        $data = [

            'grant_type' => 'password',
            'scope' => 'https://api-eeff.sunat.gob.pe',
            'client_id' => $campos['client_id'],
            'client_secret' => $campos['client_secret'],
            'username' => $campos['usuario'],
            'password' => $campos['clave_sol']
            
        ];


        $curl = Http::asForm()->post('https://api-seguridad.sunat.gob.pe/v1/clientessol/'. $campos['client_id'] .'/oauth2/token', $data );
        
        $response = $curl->json();

        return $response;



    }


    public function sire(Request $request){


        $periodo  = str_replace("-","",$request->periodo);

        $sireDB = Sire::where('numRuc', $request->ruc)->where('perPeriodoTributario', $periodo);

        if($sireDB->count() > 0){

            $sireDB->delete();

        }
   
        $access_token = $this->accessToken($request);

        if($access_token){
               

               $curl = Http::withHeaders([

                       'Authorization' => 'Bearer '.$access_token['access_token'],
            
               ])->get('https://api-cpe.sunat.gob.pe/v1/contribuyente/migeigv/libros/ventas/propuesta/' . $periodo . '/comprobantes?codTipoOpe=1&page=1&perPage=500');

                
               $sire = $curl->json();


               if(empty($sire['registros'])){


                return $this->errorResponse('No se encontro datos con este periodo', 400);


               }

              
               $collection = collect($sire['registros']);

               $multiplied = $collection->map(function ($item) {


                return [

                    "id" => $item['id'],
                    "numRuc" => $item['numRuc'],
                    "nomRazonSocial" => $item['nomRazonSocial'],
                    "perPeriodoTributario" => $item['perPeriodoTributario'],
                    "codCar" => $item['codCar'],
                    "codTipoCDP" => $item['codTipoCDP'],
                    "numSerieCDP" => $item['numSerieCDP'],
                    "numCDP" => $item['numCDP'],
                    "codTipoCarga" => $item['codTipoCarga'],
                    "codSituacion" => $item['codSituacion'], 
                    "fecEmision" => $item['fecEmision'],
                    "fecVencPag" =>  isset($item['fecVencPag']) ? $item['fecVencPag'] : null,
                    "codTipoDocIdentidad" => isset($item['codTipoDocIdentidad']) ? $item['codTipoDocIdentidad'] : null,
                    "numDocIdentidad" => isset($item['numDocIdentidad']) ? $item['numDocIdentidad'] : null,
                    "nomRazonSocialCliente" => $item['nomRazonSocialCliente'],
                    "mtoValFactExpo" => $item['mtoValFactExpo'],
                    "mtoBIGravada" => $item['mtoBIGravada'],
                    "mtoDsctoBI" => $item['mtoDsctoBI'],
                    "mtoIGV" => $item['mtoIGV'],
                    "mtoDsctoIGV" => $item['mtoDsctoIGV'],
                    "mtoExonerado" => $item['mtoExonerado'],
                    "mtoInafecto" => $item['mtoInafecto'],
                    "mtoISC" => $item['mtoISC'],
                    "mtoBIIvap" => $item['mtoBIIvap'],
                    "mtoIvap" => $item['mtoIvap'],
                    "mtoIcbp" => $item['mtoIcbp'],
                    "mtoOtrosTrib" => $item['mtoOtrosTrib'],
                    "mtoTotalCP" => $item['mtoTotalCP'],
                    "codMoneda" => $item['codMoneda'],
                    "mtoTipoCambio" => $item['mtoTipoCambio'],
                    "codEstadoComprobante" => $item['codEstadoComprobante'],
                    "mtoValorOpGratuitas" => $item['mtoValorOpGratuitas'],
                    "mtoValorFob" => $item['mtoValorFob'],
                    "indTipoOperacion" => isset($item['indTipoOperacion']) ? $item['indTipoOperacion'] : null,
                    "numInconsistencias" => $item['numInconsistencias'],
                    "indEditable" => $item['indEditable'],
                    "documentoMod" => implode($item['documentoMod']),
                    "semaforo" => $item['semaforo'],
                                  

                ];   

              
               });


            
               DB::table('sires')->insert($multiplied->toArray());
               
               $response = [

                    'message' =>  'Sire actualizado'

               ];

               

               return $this->showMessage($response);
      
        }


    }


}
