<?php


namespace App\Services;

use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\Signature;

use ZipArchive;

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
                CURLOPT_HTTPHEADER => [
                    'User-Agent: gzip',
                    'Accept-Encoding: gzip'
                   
                ],
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


    public function processRuc10($userRuc, $userSunat, $userPassword, $ruc){



        
       
        $this->loginInSunat($userRuc, $userSunat, $userPassword, 'view_address');
        
        $responses = $this->requestHttp("https://ww1.sunat.gob.pe/ol-ti-itemisionfactura/emitir.do?action=obtenerDomicilioFiscal&tipoDocumento=6&numeroDocumento=".$ruc);
        
        dd($responses);

        $result=json_decode($responses['data']);

         return [

           "success" => ($result->codeError==0) ? true : false,
            "data" =>  ($result->codeError==0) ? $result->data : "Ocurrio un error"
        ];
    }

    public function enviar_invoice($emisor, $nombreXML)
    {   

        $carpeta = base_path().'/public/cdr/';

        if(!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }



        //PASO 1. FIRNAR DIGITALMENTE EL XML        
 
        $objFirma = new Signature();
        $flgFirma = 0; //posicion de la firma digital en el XML

        $path_certi = base_path().'/public/CERTIFICADO-DEMO.pfx';
        $path_xml   = base_path().'/public/xml/'.$nombreXML.'.XML';

        $ruta_certificado = $path_certi;
        $pass_certificado = 'ceti';
        $ruta_xml = $path_xml;
        $objFirma->signature_xml($flgFirma, $ruta_xml, $ruta_certificado, $pass_certificado);

        echo '</br> - PASO 01: FIRMARDO DIGITALMENTE EL XML';


        //PASO 2. COMPRIMIR EL XML FIRMADO EN .ZIP
        $zip = new ZipArchive();
        
        $path = base_path().'/public/xml/';

        $ruta_zip = $path . $nombreXML . '.ZIP';

    
        if ($zip->open($ruta_zip, ZipArchive::CREATE) == true) {

            $zip->addFile($ruta_xml, $nombreXML . '.XML');
            $zip->close();
        }

        echo '</br> - PASO 02: XML COMPRIMIDO EN FORMATO .ZIP';


         //PASO 3. CODIFICAR EN BASE 64 EL ZIP

         $zip_codificado = base64_encode(file_get_contents($ruta_zip));

        //  echo '</br> - PASO 03: ZIP CODIFICADO EN BASE64 : ' . $zip_codificado;


        //PASO 4. CONSUMIR EL WEB SERVICE DE SUNAT
            //RUTA O EL URL DEL WS DE SUNAT
            //XML ENVELOPE: USUARIO Y CLAVE SECUNDARIO, NOMBRE ZIP Y EL CONTENIDO DEL ZIP CODIFICADO
            //EJECUTAR EL CONSUMO CON CURL-PHP

        $url_ws   = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'; //ruta de beta - prueba sunat
        //$url_ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'; //ruta productiva - sunat    


        $file_name_zip = $nombreXML . '.ZIP';

        $xml_envelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                    <wsse:Password>' . $emisor['clave_usuario_secundario'] . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
         </soapenv:Header>
         <soapenv:Body>
            <ser:sendBill>
                <fileName>' . $file_name_zip . '</fileName>
                <contentFile>' . $zip_codificado . '</contentFile>
            </ser:sendBill>
         </soapenv:Body>
        </soapenv:Envelope>';

        // echo '</br> XML ENVELOPE: ' . $xml_envelope;

        // create curl resource


    
   
        $curl = Http::send('POST', $url_ws , [
            'body' => $xml_envelope,
        ]);

        $output = $curl->body();
        $httpcode = $curl->status();

        
        echo '</br> - PASO 04: CONSUMO DEL WEBSERVICE DE SUNAT';


        //PASO 5. INICIO GESTIONAR LA RPTA DE SUNAT
        $estado_fe = 0; //0:XML AUN NO SE ENVIA, 1: OK TENGO EL CDR, 2: RECHAZO DE SUNAT, 3:PROBLEMA DE CONEXION


        if ($httpcode == 200) { //OK OBTUVE RPTA

            $doc = new DOMDocument();
            $doc->loadXML($output);

            if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)){

                $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
                echo '</br> - PASO 05: SE OBTUVO RPTA DE SUNAT';

                $cdr = base64_decode($cdr);

                echo '</br> - PASO 06: CRD DECODIFICADO: OBTENEMOS EL .ZIP';

                $path_crd = base_path().'/public/cdr/';

       

                file_put_contents($path_crd . 'R-' . $file_name_zip, $cdr);

                $zip = new ZipArchive();

                if ($zip->open($path_crd . 'R-' . $file_name_zip) == true) {

                    $zip->extractTo($path_crd);
                    $zip->close();
                    echo '</br> - PASO 07: ZIP CODIADO A DISCO Y DESCOMPRIMIDO, OBTENEMOS EL CDR . XML';

                    $estado_fe = 1;

                    $xml_cdr = $path_crd . 'R-' . $nombreXML . '.XML';
                    $doc_cdr = new DOMDocument();
                    $doc_cdr->loadXML(file_get_contents($xml_cdr));
                    $msje1 = '';
                    $msje2 = '';

                    if (isset($doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue)) {
                        $msje1 = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
                    }

                    if (isset($doc_cdr->getElementsByTagName('Note')->item(0)->nodeValue)) {
                        $msje2 = $doc_cdr->getElementsByTagName('Note')->item(0)->nodeValue;
                    }

                    echo '</br> ' . $msje1;
                    echo '</br> ' . $msje2;

                    echo '</br> PROCESO TERMINADO';

                }else{

                    $estado_fe = 2;
                    $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                    $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                    echo '</br> ERROR: ' . $mensaje . ' </br> CODIGO DE ERROR: ' . $codigo;

                }

            }else{

                $estado_fe = 3;
                
           

                echo '</br> PROBLEMAS DE CONEXION: ' . $output;

            }

          

        }
    }



}


