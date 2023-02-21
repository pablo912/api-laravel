<?php


namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SunatService{

    private $client;
    
    public function __construct()
    {
    
        $this->client = new Client([
            'base_uri' => 'https://www.sunat.gob.pe/ol-ti-itconsultaunificadalibre/consultaUnificadaLibre/',
            'stream' => false,
            'headers' => [
                'User-Agent' => 'Testing 1.0'
            ]
        ]);

    }

    public function loginInSunat($userRuc, $userSunat, $userPassword, $method = 'default',$ruc=null)
    {

        $result = $this->requestHttp(
            'https://api-seguridad.sunat.gob.pe/v1/clientessol/4f3b88b3-d9d6-402a-b85d-6a0bc857746a/oauth2/j_security_check',
            'POST',
            [
                'tipo' => '2',
                'dni' => '',
                'custom_ruc' => $userRuc,
                'j_username' => $userSunat,
                'j_password' => $userPassword,
                'captcha' => '',
                'originalUrl' => 'https://e-menu.sunat.gob.pe/cl-ti-itmenu/AutenticaMenuInternet.htm',
                'state' => 'rO0ABXNyABFqYXZhLnV0aWwuSGFzaE1hcAUH2sHDFmDRAwACRgAKbG9hZEZhY3RvckkACXRocmVzaG9sZHhwP0AAAAAAAAx3CAAAABAAAAADdAAEZXhlY3B0AAZwYXJhbXN0AEsqJiomL2NsLXRpLWl0bWVudS9NZW51SW50ZXJuZXQuaHRtJmI2NGQyNmE4YjVhZjA5MTkyM2IyM2I2NDA3YTFjMWRiNDFlNzMzYTZ0AANleGVweA',
            ]
        );
        

        $internalUrl = [
            'default'       => 'https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=11.19.1.1.1&s=ww1',
            'xml'           => 'https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=11.9.5.1.1&s=ww1',
            'empleador'     => 'https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=10.5.3.1.1&s=ww1',
            'verificar'     => 'https://ww1.sunat.gob.pe/ol-ti-itconscpegem/consultar.do',
            'direccion'     => 'https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=11.5.1.1.2&s=ww1',
            'view_address'  => 'https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=11.5.3.1.1&s=ww1',
        ];

         $results = $this->requestHttp($internalUrl[$method]);


         if($internalUrl[$method]=="https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=11.5.1.1.2&s=ww1"){
            $response = $this->requestHttp(
                'https://ww1.sunat.gob.pe/ol-ti-itreciboelectronico/cpelec001Alias',
                'POST',
                [
                    'accion' => 'VALIDATIPODOC',
                    'formaPago' => 'CREDITO',
                    'tipdoc' => '6',
                    'numdoc' => $ruc,
                    'ubigeoUsuario' => '',
                    'direccionUsuario' => '',
                    'txtUbi_Codigo' => '',
                    'txtUbi_departamento' => '',
                    'txtUbi_provincia' => '',
                    'txtUbi_distrito' => ''
                 ]);

           return $response;
         }



    }

    public function requestHttp($url, $method = 'get', $data = [], $headers = [], $photo=false){

        $directorio = dirname(__FILE__) . '..//..//..//public//tempo';
        $cookieFileLocation = $directorio . '//' . uniqid();
        
        try {
           
            $curl = [
                CURLOPT_USERAGENT =>  "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0",
                CURLOPT_HTTPHEADER => [],
                CURLOPT_ENCODING => "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_COOKIESESSION=>true,
                CURLOPT_HTTP_VERSION=> CURL_HTTP_VERSION_1_1,
                CURLOPT_VERBOSE => true,
                CURLOPT_POST => strtoupper($method) == 'POST',
                CURLOPT_FAILONERROR => true,
                CURLOPT_COOKIEFILE => $cookieFileLocation,
                CURLOPT_COOKIEJAR => $cookieFileLocation,
            ];  
            
            if ($method == 'get') {
                $curl[CURLOPT_MAXREDIRS] = 2;
                $curl[CURLOPT_TIMEOUT] = 0;
                $curl[CURLOPT_CONNECTTIMEOUT] = 0;
                $curl[CURLOPT_FOLLOWLOCATION] = true;
            }

            if (!empty($data)) {
                $curl[CURLOPT_POSTFIELDS] = is_array($data) ? http_build_query($data) : $data;
            }
            
            if (!empty($headers)) {
                $curl[CURLOPT_CUSTOMREQUEST]= "POST";
                $curl[CURLOPT_HTTPHEADER] = $headers;
            }

     
            $response = $this->client->request(strtoupper($method), $url, [
                'curl' => $curl,
                'http_errors' => false,
            ]);

        
         


            if($url=="https://ww1.sunat.gob.pe/ol-ti-itreciboelectronico/cpelec001Alias"){

                $data = str_replace(["\n", "\r", "\t"], '',$response->getBody()->getContents());

             
                $buscar_ubigeo = 'document.getElementById("txtUbi_Codigo").value = "';
                $buscar_fin='onchange="this.value=this.value.toUpperCase()" disabled>';
                $pos   = strripos($data, $buscar_ubigeo);
           
                $buscar_direccion = 'id="direccionUsuarioTemp" value="';

         

                if ($pos === false) {

                    $ubigeo_data="";

                } else {

                    $ubigeo_data=substr($data,$pos+50,6);
                }

                $pos_direccion   = strripos($data, $buscar_direccion);

                if ($pos_direccion === false) {
                    $direccion="";
                } else {

                    $direccion=substr(trim(substr($data,$pos_direccion+33,(strripos($data, $buscar_fin))-($pos_direccion+33)-1)),0,strlen(trim(substr($data,$pos_direccion+33,(strripos($data, $buscar_fin))-($pos_direccion+33)-1)))-1);

                }

                return  [
                    "success" => true,
                    "ubigeo" => $ubigeo_data,
                    "direccion" => $direccion,
                ];

            }else{

                return  [
                    "success" => true,
                    "data" => $response->getBody()->getContents(),
                ];

            }


        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                return  [
                    "success" => false,
                    "message" => "webservice de Sunat en mantenimiento, intente mas luego",
                    "code" => $e->getHandlerContext()['http_code'],
                ];
            }
        }

    }


    public function processRuc10($userRuc, $userSunat, $userPassword, $ruc)
    {
        $this->loginInSunat($userRuc, $userSunat, $userPassword, 'view_address');
        $responses = $this->requestHttp("https://ww1.sunat.gob.pe/ol-ti-itemisionfactura/emitir.do?action=obtenerDomicilioFiscal&tipoDocumento=6&numeroDocumento=".$ruc);
        $result=json_decode($responses['data']);

         return [
           "success" => ($result->codeError==0) ? true : false,
            "data" =>  ($result->codeError==0) ? $result->data : "Ocurrio un error"
        ];
     }

}


