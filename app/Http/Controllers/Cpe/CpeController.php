<?php

namespace App\Http\Controllers\Cpe;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CpeController extends Controller
{   
    protected $client;

    protected $document_state = [
        '-' => '-',
        '0' => 'NO EXISTE',
        '1' => 'ACEPTADO',
        '2' => 'ANULADO',
        '3' => 'AUTORIZADO',
        '4' => 'NO AUTORIZADO'
    ];

    protected $document = [
        '01' => 'FACTURA ELECTRONICA',
        '03' => 'BOLETA DE VENTA ELECTRONICA',
        '07' => 'NOTA DE CREDITO',
        '08' => 'NOTA DE DEBITO',

    ];

    protected $company_state = [
        '-' => '-',
        '00' => 'ACTIVO',
        '01' => 'BAJA PROVISIONAL',
        '02' => 'BAJA PROV. POR OFICIO',
        '03' => 'SUSPENSION TEMPORAL',
        '10' => 'BAJA DEFINITIVA',
        '11' => 'BAJA DE OFICIO',
        '12' => 'BAJA MULT.INSCR. Y OTROS ',
        '20' => 'NUM. INTERNO IDENTIF.',
        '21' => 'OTROS OBLIGADOS',
        '22' => 'INHABILITADO-VENT.UNICA',
        '30' => 'ANULACION - ERROR SUNAT   '
    ];

    protected $meses=[
        '01'=>'Enero',
        '02'=>'Febrero',
        '03'=>'Marzo',
        '04'=>'Abril',
        '05'=>'Mayo',
        '06'=>'Junio',
        '07'=>'Julio',
        '08'=>'Agosto',
        '09'=>'Setiembre',
        '10'=>'Octubre',
        '11'=>'Noviembre',
        '12'=>'Diciembre',
    ];

    protected $company_condition = [
        '-' => '-',
        '00' => 'HABIDO',
        '01' => 'NO HALLADO SE MUDO DE DOMICILIO',
        '02' => 'NO HALLADO FALLECIO',
        '03' => 'NO HALLADO NO EXISTE DOMICILIO',
        '04' => 'NO HALLADO CERRADO',
        '05' => 'NO HALLADO NRO.PUERTA NO EXISTE',
        '06' => 'NO HALLADO DESTINATARIO DESCONOCIDO',
        '07' => 'NO HALLADO RECHAZADO',
        '08' => 'NO HALLADO OTROS MOTIVOS',
        '09' => 'PENDIENTE',
        '10' => 'NO APLICABLE',
        '11' => 'POR VERIFICAR',
        '12' => 'NO HABIDO',
        '20' => 'NO HALLADO',
        '21' => 'NO EXISTE LA DIRECCION DECLARADA',
        '22' => 'DOMICILIO CERRADO',
        '23' => 'NEGATIVA RECEPCION X PERSONA CAPAZ',
        '24' => 'AUSENCIA DE PERSONA CAPAZ',
        '25' => 'NO APLICABLE X TRAMITE DE REVERSION',
        '40' => 'DEVUELTO'
    ];

    
    public function apivalidarcpe(Request $request){
        try {
            if (session()->has('access_token')==true) {
                $access_token=session('access_token');

              }else{
                $token=$this->access_token();

                if($token['success']==true){
                    $access_token=$token['access_token'];
                    session(['access_token' => $token['access_token']]);
                }
            }

           if(session()->has('access_token')!=false){

            reValidates:
            $user=User::where('id',auth()->user()->id)->first();
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes/20600471326/validarcomprobante',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "numRuc":"'.$request->ruc.'",
                "codComp":"'.$request->document_type.'",
                "numeroSerie":"'.$request->serie.'",
                "numero":"'.$request->numero.'",
                "fechaEmision" : "'.$request->fecha.'",
                "monto" : "'.$request->monto.'"
            }',
                CURLOPT_HTTPHEADER => array(
                    "authorization: Bearer ".$access_token,
                    'Content-Type: application/json'
                ),
            ));

            $html = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($html, true);
            if ($response['success']==false) {
                return [
                    "success" =>false,
                    "message" =>$response['message']
                ];
            }
            if (Arr::has($response, 'status')==true) {
                if ($response['status']==401 && $response['message']=="Unauthorized"){
                    $token=$this->access_token();
                    $access_token=$token['access_token'];
                    session(['access_token' => $access_token]);
                    goto reValidates;
                }
            }
            if (Arr::has($response, 'success')==true) {
                    if ($response['success']==true && count($response['data'])>0){
                        if (array_key_exists('observaciones', $response['data'])) {
                            $observaciones=$response['data']['observaciones'][0];
                        }else{
                            $observaciones="-";
                        }
                        if($response['data']['estadoCp']=="1"){
                            return [
                                'success' => true,
                                'data' => [
                                    'comprobante_estado_codigo' => $response['data']['estadoCp'],
                                    'comprobante' => $this->document[$request->document_type],
                                    'numero_comprobante' =>$request->serie."-".$request->numero,
                                    'comprobante_estado_descripcion' => $this->document_state[$response['data']['estadoCp']],
                                    'empresa_estado_codigo' => $response['data']['estadoRuc'],
                                    'empresa_estado_description' => $this->company_state[$response['data']['estadoRuc']],
                                    'empresa_condicion_codigo' => $response['data']['condDomiRuc'],
                                    'empresa_condicion_descripcion' => $this->company_condition[$response['data']['condDomiRuc']],
                                    'observaciones' =>$observaciones,
                                ]
                            ];
                        }else{
                            return [
                                'success' => true,
                                'data' => [
                                    'comprobante_estado_codigo' => $response['data']['estadoCp'],
                                    'comprobante' => $this->document[$request->document_type],
                                    'numero_comprobante' =>$request->serie."-".$request->numero,
                                    'comprobante_estado_descripcion' => $this->document_state[$response['data']['estadoCp']],
                                ]
                            ];
                        }
                }
          
            }else{
                goto reValidates;
            }
            }else{
                if(session()->has('access_token')==false){
                    $token=$this->access_token();
                    $access_token=$token['access_token'];
                    session(['access_token' => $access_token]);
                    goto reValidates;
                }
            }
         } catch (Exception $e) {
            return [
                "success" => false,
                "message" => $e->getMessage(),
                "line"    =>$e->getLine(),
                "code"=>$e->getCode()
            ];
        }
    }

    public function access_token(){
        try {
            $company = Company::first();
            $this->client = new Client(['verify' => false, 'http_errors' => false]);
           $curl = [
            CURLOPT_URL => "https://api-seguridad.sunat.gob.pe/v1/clientesextranet/".$company->client_id."/oauth2/token/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "grant_type=client_credentials&scope=https://api.sunat.gob.pe/v1/contribuyente/contribuyentes&client_id=".$company->client_id."&client_secret=".$company->client_secret,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
            ),
           ];
          $responses = $this->client->request(strtoupper("POST"),"https://api-seguridad.sunat.gob.pe/v1/clientesextranet/".$company->client_id."/oauth2/token/", [
               'curl' => $curl,
           ]);
           $token= json_decode($responses->getBody()->getContents());
               return [
              "success"=>true,
              "access_token"=>$token->access_token
          ];

      } catch (RequestException $exception) {
         return $exception->getResponse()->getBody();
      }
        //generarToken:
    }


}
