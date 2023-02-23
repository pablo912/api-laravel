<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\District;
use App\Models\Empresa;
use App\Models\Province;
use GuzzleHttp\Client;
use App\Services\SunatService;

use App\Traits\ApiResponser;
use DiDom\Document as DiDom;
use Illuminate\Support\Facades\Http;

class RucService
{

    use ApiResponser;

    private $sunat;
   
    public function __construct(SunatService $sunat)
    {
    
        $this->sunat = $sunat;
      
    }

    public function consultar_ruc($ruc,$id=false,$view_address=true){

   
        $company = Company::first();

        $departamento="-";
        $provincia="-";
        $distrito="-";
        $ubigeo="";


        $site = Empresa::where('ruc', $ruc)->first();


        if($site){

            $razon = str_replace(['"',"'"],"",$site->razon);

            $direccion_completa = $this->getAddress($site);


                if(substr($ruc,0,2)=="20"){

                    $ubigeo = $site->ubigeo;

                    $direccion_completa = $this->getAddress($site);

                }else if(substr($ruc,0,2)=="10" || substr($ruc,0,2)=="15" || substr($ruc,0,2)=="17"){

                    $response_ubigeo = $this->sunat->loginInSunat("10448173173","44817317", "Elpoder20", 'direccion',$ruc);  

                    $ubigeo = $response_ubigeo['ubigeo'];
                    $direccion_completa = $response_ubigeo['direccion'];

                    $site->ubigeo = $ubigeo;
                    $site->direccion =$direccion_completa;
                    $site->save();

                }


            if($ubigeo != null || $ubigeo != "" || $ubigeo != "-"){

                $distrito=District::where('id',$ubigeo)->first();

                if($distrito!=null){
                    $provincia=Province::where('id',$distrito->province_id)->first();
                    $departamento=Department::where('id',$provincia->department_id)->first();   
                }

            }

            if(substr($ruc,0,2)=="20"){

                $ubigeos_data = [substr($site->ubigeo,0,2),substr($site->ubigeo,0,4),$site->ubigeo];
                $ubigeo_data = $site->ubigeo;
                
            }else{
                
                 $ubigeos_data = ($distrito!=null) ?  [substr($ubigeo,0,2),substr($ubigeo,0,4),$ubigeo] : [null,null,null];
                 $ubigeo_data = $ubigeo;
          
            } 

            $response = [

                'ruc' => $site->ruc,
                'nombre_o_razon_social' =>$razon,
                'direccion' =>trim($direccion_completa),
                'estado' => $site->estado,
                'condicion' => $site->condicion,
                'departamento' => ($distrito!=null) ? strtoupper(optional($departamento)->description) : "",
                'provincia' => ($distrito!=null) ?  strtoupper($provincia->description) : "",
                'distrito' =>  ($distrito!=null) ?  strtoupper($distrito->description) : "",
                'ubigeos' =>  $ubigeos_data,
                'ubigeo' => $ubigeo_data

            ];

            return [
                'success' => true,
                'agente_retencion' =>( $site->agente_retencion==1) ? 'SI' : 'NO',
                'data' => $response,

            ];

        }


        return $this->errorResponse('El número de RUC no fué encontrado.',404);


    }


    public function rusplus($ruc){

        try{

            $empresas = Empresa::where('ruc', $ruc)->first();

            $direccion_sunat="";

            $consultar_sunat=false;


            $resultDir = $this->sunat->loginInSunat("10448173173","44817317", "Elpoder20", 'direccion',$ruc);                
        
            if ($resultDir['success']==true){
                    
                    $direccion_sunat = $resultDir['direccion'];
                    $ubigeo_sunat = $resultDir['ubigeo'];

                    $empresas->ubigeo = $ubigeo_sunat;
                    $empresas->direccion =$direccion_sunat;
                    $empresas->save();

                    $consultar_sunat=true;
                     
                 } else {

                     $direccion_sunat="-";
                     $consultar_sunat=false;

            }
          

            $this->sunat->requestHttp("https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc=CHACCCHI","POST");
            $httpRequest=$this->sunat->requestHttp("https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc=CHACCCHI","POST");

          

            if($httpRequest['success']==true){
        
               $document = new DiDom($httpRequest['data']);
            
               $posts = $document->find('form')[0]->find('input');
               $random=explode("=",$posts[3]->html());
               $random=substr($random[3],1,strlen($random[3])-3);

               // BUSCAR CUALQUIER RUC
               $http_Request = $this->sunat->requestHttp("https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRuc&actReturn=1&modo=1&nroRuc=".$ruc."&numRnd=".$random,"POST");
               
               $document = new DiDom($http_Request['data'],false,'ISO-8859-1');

               $result = $document->find('.panel-primary')[0]->find('.list-group-item');

               
               $array_result=[];

               $array_data=[];

               $array_data['numero_ruc'] = $ruc;
                
     
               for ($i=0; $i < count($result) ; $i++) {

                $result1 = preg_replace("/[\t|\n|\r]+/", '',$result[$i]->find('.list-group-item')[0]->find('.row'));
                
                $node = new DiDom($result1[0]);

                $node1 = $node->find('.row')[0];


                if(strpos(trim($node1->find('div')[1]->text()),"Número de RUC:") !== false){

                     $array_data['razon_social']= trim(explode("-",trim($node1->find('div')[2]->text()))[1]);
      
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Tipo Contribuyente") !== FALSE){

                    $array_data['tipo_contribuyente'] = trim($node1->find('div')[2]->text());

            
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Tipo de Documento:") !== FALSE){
                    $document=explode("  ",trim($node1->find('div')[2]->text()));
                    $array_data['tipo_documento']= $document[0];

                }
                if(strpos(trim($node1->find('div')[1]->text()),"Nombre Comercial:") !== FALSE){
                    $array_data['nombre_comercial']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Fecha de Inscripci") !== FALSE){
                    $array_data['fecha_inscripcion']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Fecha de Inicio") !== FALSE){
                   $array_data['fecha_actividades']= trim($node1->find('div')[2]->text());

                }
                if(strpos(trim($node1->find('div')[1]->text()),"Estado del Contribuyente:") !== FALSE){
                    $array_data['estado_contribuyente']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Condición del Contribuyente:") !== FALSE){


                    $array_data['condicion_contribuyente']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Domicilio Fiscal:") !== FALSE){
                    if($direccion_sunat==null){
                         $direccion_sunat="-";
                    }
                    $array_data['domicilio_fiscal']=($consultar_sunat==true) ?  $direccion_sunat : preg_replace(['/\s+/','/^\s|\s$/'],[' ',''],$node1->find('div')[2]->text());

                 
                 
                        $departamento=Department::where('id','=',substr($empresas->ubigeo,0,2))->first();
                       
                       
                        if($departamento){
                            $provincia=Province::where('department_id','=',$departamento->id)->where('id',substr($empresas->ubigeo,0,4))->first();
                            $district=District::where('id','=',$empresas->ubigeo)->first();
                            $array_data['departamento']=strtoupper($departamento->description);
                            $array_data['provincia']=strtoupper($provincia->description);
                            $array_data['distrito']=strtoupper($district->description);
                            $array_data['ubigeo']=strtoupper($empresas->ubigeo);
                            $array_data["dir_tipo_via"] = $empresas->tipo_via;
                            $array_data["nombre_via"] = $empresas->nombre_via;
                            $array_data["dir_cod_zona"] = $empresas->codigo_zona;
                            $array_data["dir_tipo_zona"] = $empresas->tipo_zona;
                            $array_data["dir_num"] =  $empresas->numero;
                            $array_data["dir_interior"] = $empresas->interior;
                            $array_data["dir_lote"] = $empresas->lote;
                            $array_data["dir_dpto"] = $empresas->departamento;
                            $array_data["dir_manzana"] = $empresas->manzana;
                            $array_data["dir_km"] = $empresas->km;
                            $array_data["dir_nomb_via"] = $empresas->nombre_via;
                            $array_data["afectado_rus"] = $empresas->afectado_rus;
                        }

                  
                    
           
                }



                if(strpos(trim($node1->find('div')[1]->text()),"Sistema Emisión de Comprobante:") !== FALSE){
                    $array_data['sistema_emision_comprobante']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Actividad Comercio Exterior:") !== FALSE){
                    $array_result['data']=[
                        "comercio_exterior"=>trim($node1->find('div')[2]->text())
                    ];
                    $array_data['condicion_contribuyente']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Sistema Contabilidiad:") !== FALSE){
                    $array_data['sistema_contabilidad']= trim($node1->find('div')[2]->text());

                }
                if(strpos(trim($node1->find('div')[1]->text()),"Actividad(es) Económica(s)") !== FALSE){
                    $economica=explode("                                                ",trim($node1->find('div')[2]->text()));
                    $data_cpe=[];
                    if(count($economica)>0){
                        for ($ii=0; $ii < count($economica); $ii++) {
                            if(trim($economica[$ii]) !=""){
                                $data_cpe['economica'][]=trim($economica[$ii]);

                            }
                        }
                        $nodos=[];
                        for ($xx=0; $xx <count($data_cpe['economica']) ; $xx++) {
                            $rows=explode("-", trim($data_cpe['economica'][$xx]));
                            $nodos[]=[
                                "tipo" => strtolower(trim($rows[0])),
                                "descripcion"=>trim($rows[2]),
                                "code"=>trim($rows[1])
                            ];
                        }
                        $array_data['actividad_economica']=$nodos;
                    }else{
                        $array_data['actividad_economica']=trim($node1->find('div')[2]->text());

                    }
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Comprobantes de Pago c/aut. de impresin") !== FALSE){
                    $comprobantes_pagos="";
                    $cpe_pagos=explode("                                                ",trim($node1->find('div')[2]->text()));
                    $data_cpe=[];
                    if(count($cpe_pagos)>0){
                        for ($ii=0; $ii < count($cpe_pagos); $ii++) {
                            $data_cpe['cpe'][]= trim($cpe_pagos[$ii]);
                        }
                        $array_data['comprobantes_pagos']=$data_cpe['cpe'];
                    }else{
                        $array_data['comprobantes_pagos']=trim($node1->find('div')[2]->text());

                    }
                    //(count($comprobantes_pagos)>0) ? $comprobantes_pagos: trim($node1->find('div')[2]->text());

                }
                if(strpos(trim($node1->find('div')[1]->text()),"Sistema de Emisión Electrónica") !== FALSE){
                    $array_data['sistema_emision']= preg_replace(['/\s+/','/^\s|\s$/'],[' ',''],trim($node1->find('div')[2]->text())) ;
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Emisor electrónico desde") !== FALSE){
                    $array_data['emisor_electronico']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Comprobantes Electrónicos") !== FALSE){
                    $array_data['comprobantes_electronicos']= trim($node1->find('div')[2]->text());
                }
                if(strpos(trim($node1->find('div')[1]->text()),"Afiliado al PLE desde:") !== FALSE){
                    $array_data['ple']= trim($node1->find('div')[2]->text());
                }

                if(strpos(trim($node1->find('div')[1]->text()),"Padrones:") !== FALSE){
                    $array_data['padrones']= trim($node1->find('div')[2]->text());
                }

               }

            }    

      

          
            return response()->json([
                "success" => true,
                "data" =>$array_data
            ]);

        }catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                return  [
                    "success" => false,
                    "message" => "webservice de Sunat en mantenimiento, intente mas luego",
                    "code" => $e->getHandlerContext()['http_code'],
                ];
    
            }
        }
        

    }


    protected function getAddress($site){

        $tipo_via = ($site->tipo_via && $site->tipo_via != '-') ? $site->tipo_via : '';
        $nombre_via = ($site->nombre_via && $site->nombre_via != '-') ? ' '.$site->nombre_via : '';
        $codigo_zona = ($site->codigo_zona && $site->codigo_zona != '-') ? ' '.$site->codigo_zona : '';
        $tipo_zona = ($site->tipo_zona && $site->tipo_zona != '-') ? ' '.$site->tipo_zona : '';
        $numero = ($site->numero && $site->numero != '-') ? " NRO. {$site->numero}" : '';

        $manzana = ($site->manzana && $site->manzana != '-') ? " MZ. {$site->manzana}" : '';
        $lote = ($site->lote && $site->lote != '-') ? " LT. {$site->lote}" : '';
        $departamento = ($site->departamento && $site->departamento != '-') ? " DPTO. {$site->departamento}" : '';
        $interior = ($site->interior && $site->interior != '-') ? " INT. {$site->interior}" : '';
        $kilometro = ($site->kilometro && $site->kilometro != '-') ? " KM. {$site->kilometro}" : '';

        $address = "{$tipo_via}{$nombre_via}{$numero}{$codigo_zona}{$tipo_zona}{$manzana}{$lote}{$departamento}{$interior}{$kilometro}";

        return $address;
    }


}