<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Sire;
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


    public function dni($dni)
    {
        if (strlen($dni) != 8 || !is_numeric($dni)) {

            return $this->errorResponse(['dni' => ['Debe Ingresar 8 digitos numericos.']], 401);

        }

        $result=$this->dniService->processDni($dni,false)->original;

        return $result;
       
    }

    public function dniplus($dni)
    {
        if (strlen($dni) != 8 || !is_numeric($dni)) {
            return $this->errorResponse(['dni' => ['Debe Ingresar 8 digitos numericos.']], 401);
        }

        $result=$this->dniService->processDni($dni,true)->original;

          
        return $result;
       
    }

 

    public function ruc($ruc){

        if( strlen($ruc)!=11 || !is_numeric($ruc)){
            
            return $this->errorResponse('Formato RUC no valido.', 400);
        }


        return $this->rucService->consultar_ruc($ruc);

    }

    public function rusplus($ruc){

        if( strlen($ruc)!=11 || !is_numeric($ruc)){
            
            return $this->errorResponse('Formato RUC no valido.', 400);
            
        }
    
        // dd($this->rucService->rusplus($ruc));

        return $this->rucService->rusplus($ruc);


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
