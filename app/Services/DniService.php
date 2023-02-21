<?php

namespace App\Services;

use App\Contracts\Services\SunatContractService;
use App\Http\Controllers\ApiController;
use App\Models\Company;
use App\Models\Department;
use App\Models\District;
use App\Models\Dni;
use App\Models\Province;
use App\Models\Ubigeo;
use App\Traits\ApiResult;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class DniService  {

    use ApiResult;

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

    public function processDni($numero, $plus=false){

        try {
           
            $company = Company::first();
            $dni_query=Dni::where('numero',$numero)->first();
          
            if($dni_query===null){

                return $this->buscarDniSunat($company->ruc,$company->usuario_sol,$company->clave_sol,$numero,$plus);

            }else{

                $ubigeo=Ubigeo::where('ubigeo',$dni_query->ubigeo2)->first();
                
                if($ubigeo==null){

                    $array_ubigeo_sunat=[null,null,null];
                    $array_ubigeo_reniec="";

                }else{

                    $dep=Department::where('description',$ubigeo->dpto)->first();
                    $prov=Province::where('department_id',$dep->id)->where('description','=',$ubigeo->prov)->first();
                    $dist=District::where('description',"=",$ubigeo->distrito)->where('province_id',$prov->id)->first();
                    $array_ubigeo_sunat=[str_pad($dep->id, 2, "0", STR_PAD_LEFT),str_pad($prov->id, 4, "0", STR_PAD_LEFT),str_pad($dist->id, 6, "0", STR_PAD_LEFT)];
                    $array_ubigeo_reniec=$ubigeo->ubigeo;

                }

                if($plus==true){
                
                    return response()->json([
                        'success' => true,
                        "data"=>[
                            "numero"=>$numero,
                            "nombre_completo"=>$dni_query->nombre_completo,
                            "nombres"=>$dni_query->nombres,
                            "apellido_paterno"=>$dni_query->apellido_paterno,
                            "apellido_materno"=>$dni_query->apellido_materno,
                            "fecha_nacimiento"=>$dni_query->fecha_nacimiento,
                            "estado_civil"=>$dni_query->estado_civil,
                            "sexo"=>$dni_query->sexo,
                            "edad"=>$dni_query->edad,
                            "direccion"=>(env('VIEW_ADRESS_DNI')==true) ? $dni_query->domicilio : '-',
                            "direccion_completa"=>$dni_query->domicilio." ".$dni_query->departamento." ".$dni_query->provincia." ".$dni_query->distrito,
                            "departamento"=>($ubigeo==null) ? '' : strtoupper($ubigeo->dpto),
                            "provincia"=>($ubigeo==null) ? '' : strtoupper($ubigeo->prov),
                            "distrito"=>($ubigeo==null) ? '' : strtoupper($ubigeo->distrito),
                            "codigo_verificacion" =>$this->digit_control($numero),
                            "ubigeo_reniec"=>$array_ubigeo_reniec,
                            "location_id"=>(env('VIEW_ADRESS_DNI')==true) ? $array_ubigeo_sunat : [ null,null,null],
                            "ubigeo"=>(env('VIEW_ADRESS_DNI')==true) ?  $array_ubigeo_sunat : [ null,null,null],
                            "nombre_o_razon_social"=>$dni_query->nombre_completo,
                            "photo"=>$dni_query->photo,
                        ]
                    ]);

                }else{
              
                    return response()->json([
                        'success' => true,
                        "data"=>[
                            "numero"=>$numero,
                            "nombre_completo"=>$dni_query->nombre_completo,
                            "nombres"=>$dni_query->nombres,
                            "apellido_paterno"=>$dni_query->apellido_paterno,
                            "apellido_materno"=>$dni_query->apellido_materno,
                            "direccion"=> (env('VIEW_ADRESS_DNI')==true) ? $dni_query->domicilio : '',
                            "direccion_completa"=>$dni_query->domicilio." ".$dni_query->departamento." ".$dni_query->provincia." ".$dni_query->distrito,
                            "departamento"=>($ubigeo==null) ? '' : strtoupper($ubigeo->dpto),
                            "provincia"=>($ubigeo==null) ? '' : strtoupper($ubigeo->prov),
                            "distrito"=>($ubigeo==null) ? '' : strtoupper($ubigeo->distrito),
                            "nombre_o_razon_social"=>$dni_query->nombre_completo,
                            "codigo_verificacion" =>$this->digit_control($numero),
                            "ubigeo_reniec"=>$array_ubigeo_reniec,
                            "location_id"=>(env('VIEW_ADRESS_DNI')==true) ?  $array_ubigeo_sunat : [ null,null,null],
                            "ubigeo"=>(env('VIEW_ADRESS_DNI')==true) ?
                             $array_ubigeo_sunat : [ null,null,null],
                        ]
                       ]);

                }

            }

        } catch (ClientException $e) {
            return [
             "line"  => $e->getLine(),
             "message" => $e->getMessage()
            ];
        }

    }


    public function buscarDniSunat($userRuc, $userSunat, $userPassword,$number_dni,$plus){


        try {
            
            $result = $this->login($userRuc, $userSunat, $userPassword);

          
                        
            $headers=[
                "Host"=> "ww1.sunat.gob.pe",
                "User-Agent"=> "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0",
                "Accept"=> " */*",
                "Accept-Language"=> "es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3",
                "Accept-Encoding"=> "gzip, deflate, br",
                "Content-Type"=> " application/x-www-form-urlencoded",
                "X-Requested-With"=> "XMLHttpRequest",
                "Content-Length"=> "2846",
                "Origin"=> "https://ww1.sunat.gob.pe",
                "Connection"=> "keep-alive",
                "Referer"=> "https://ww1.sunat.gob.pe/ol-ti-itrtpspresta/prestadores.htm?accion=buscarPersona",
                "Cookie"=> "f5avraaaaaaaaaaaaaaaa_session_=DDIAPOGIOIEMEJBNKABOOPBAJGJLOFMJHLNBFKGPFOIGLFOPHDBKNDCBKIFFCMJGLFCDLCMJHEFOLKPAALNAODOIGCEBOCBDCJDHEJGJIPPDEOCGEDPBHBDBKIGNFAMD; f5_cspm=1234; srv_id=9cbb57a1c95bad1e0338ed8bbe502151; TS44788fc0027=08fe7428c8ab2000efdc8ed80ec7a859035d439d8fe9171703b9a2cd34425007a43398ff87b573f408cab5dd2211300009b40cb3514c5e266540265bc5b782b2d4e6782b2b6192a76ee2b7955553da1df129dab8b804a9e4113b003d2c06a946; dtCookie=v_4_srv_5_sn_954B7249EDF9C29A4E8C18780BC2F3C5_perc_100000_ol_0_mul_1_app-3A7455…81KCO0D6; dtPC=5$396535974_648h-vFPWCSGAGRIFSNMGKRUFGHBOVHMHRAJGR-0e6; rxvt=1627598345108|1627594604545; dtSa=-; dtLatC=6; 20573198108HIOULDES=1; -1500673858=1; ITRTPSPRESTA=dK0ThDmCtr1KhL32mGLlVyr7CwkvLmBLF712DhWQ9R6pp3Whn9r5kHd0LhJ0SbJDBNp1jnYylGsxGnpHLjZkrv4129jsb4JvrKGXfvJg5h55NN21yS2xt0LJ20dhPJ2XqC51LLhcPRTwJgKH1zfn1Ppgf1JhGZkzzNzmq2yLhWFr8KLpM83JZlgBJQJhLtTwMpvJNZJgyv4kLh3XdQnnm3lYDTfvGNGKd7jnv8nZnGlrdhFgLykk5CHySrW840jv!1124222886!1754349623; site24x7rumID=197645476774520.1627596481599.1627596481599",
                "Sec-Fetch-Dest"=> "empty",
                "Sec-Fetch-Mode"=> "cors",
                "Sec-Fetch-Site"=> "same-origin",
            ];

             $data_fec_nac= "accion=&accionCompPersona=buscarCompPersona&personaNueva=&prestador=%7B%22actualizoIdentificacion%22%3Afalse%2C%22persona%22%3A%7B%22numCorPer%22%3A0%2C%22indOriCodPer%22%3A%22%22%2C%22codTipDocIde%22%3A%2201%22%2C%22numDocIde%22%3A%22".$number_dni."%22%2C%22codPais%22%3A%22604%22%2C%22nombres%22%3A%22%22%2C%22apePat%22%3A%22%22%2C%22apeMat%22%3A%22%22%2C%22strFechaNac%22%3A%2225%2F10%2F1980%22%2C%22indSexo%22%3A%22%22%2C%22indEstCivil%22%3A%22%22%2C%22edad%22%3A0%2C%22codUbigeo%22%3A%22%22%2C%22desDireccion%22%3A%22%22%2C%22desFecNacReniec%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.persona.model.Persona%22%7D%2C%22vinlab%22%3A%7B%22numCorVlab%22%3A0%2C%22numCorPer%22%3A0%2C%22codTipDocIde%22%3A%22%22%2C%22numDocIde%22%3A%22%22%2C%22indOriCodPer%22%3A%22%22%2C%22desCorrElect%22%3A%22%22%2C%22codLdn%22%3A%22%22%2C%22numTelf%22%3A%22%22%2C%22codPaisNac%22%3A%22%22%2C%22cussp%22%3A%22%22%2C%22numRucCas%22%3A%22%22%2C%22indCatTra%22%3A%226%22%2C%22indCatPen%22%3A%226%22%2C%22indCatTer%22%3A%226%22%2C%22indCatPfl%22%3A%226%22%2C%22indSituVlab%22%3A%22%22%2C%22numCorDom1%22%3A0%2C%22numCorDom2%22%3A0%2C%22codFtramTra%22%3A%22%22%2C%22codFtramPen%22%3A%22%22%2C%22codFtramPfl%22%3A%22%22%2C%22codFtramTer%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.prestador.model.T4476Bean%22%7D%2C%22direccion1%22%3A%7B%22numCorDom%22%3A0%2C%22desDom%22%3A%22%22%2C%22desUbigeo%22%3A%22%22%2C%22codUbigeo%22%3A%22%22%2C%22numBlock%22%3A%22%22%2C%22numDpto%22%3A%22%22%2C%22numEtapa%22%3A%22%22%2C%22numInterior%22%3A%22%22%2C%22numKm%22%3A%22%22%2C%22numLote%22%3A%22%22%2C%22desMzn%22%3A%22%22%2C%22desNomVia%22%3A%22%22%2C%22numVia%22%3A%22%22%2C%22codTipVia%22%3A%22%22%2C%22desTipVia%22%3A%22%22%2C%22desNomZona%22%3A%22%22%2C%22codTipZona%22%3A%22%22%2C%22desTipZona%22%3A%22%22%2C%22desRef%22%3A%22%22%2C%22indOrigDom%22%3A%22%22%2C%22indDomiAct%22%3A%22%22%2C%22indSituDom%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.persona.model.Domicilio%22%7D%2C%22direccion2%22%3A%7B%22numCorDom%22%3A0%2C%22desDom%22%3A%22%22%2C%22desUbigeo%22%3A%22%22%2C%22codUbigeo%22%3A%22%22%2C%22numBlock%22%3A%22%22%2C%22numDpto%22%3A%22%22%2C%22numEtapa%22%3A%22%22%2C%22numInterior%22%3A%22%22%2C%22numKm%22%3A%22%22%2C%22numLote%22%3A%22%22%2C%22desMzn%22%3A%22%22%2C%22desNomVia%22%3A%22%22%2C%22numVia%22%3A%22%22%2C%22codTipVia%22%3A%22%22%2C%22desTipVia%22%3A%22%22%2C%22desNomZona%22%3A%22%22%2C%22codTipZona%22%3A%22%22%2C%22desTipZona%22%3A%22%22%2C%22desRef%22%3A%22%22%2C%22indOrigDom%22%3A%22%22%2C%22indDomiAct%22%3A%22%22%2C%22indSituDom%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.persona.model.Domicilio%22%7D%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.prestador.model.PrestadorBean%22%7D&filtrarPersona=&tipDocIden=01&numDocIden=".$number_dni."&fecNac=1980-10-25&dojo.preventCache=1627653402761";
          
             $data_fecha_nacimiento=  $this->requestHttp2("https://ww1.sunat.gob.pe/ol-ti-itrtpspresta/prestadores.htm?accion=buscarPersona&isActualiza=0&codPais=604",'POST',$data_fec_nac,false);

             $fecNac=$data_fecha_nacimiento['persona']['fecNaci'];

             if($data_fecha_nacimiento['persona']['fecNaci']!=="25/10/1980"){

                $dia= substr($fecNac,0,2);
                $mes= substr($fecNac,3,2);
                $anio= substr($fecNac,6,4);
                $data="accion=&accionCompPersona=buscarCompPersona&personaNueva=&prestador=%7B%22actualizoIdentificacion%22%3Afalse%2C%22persona%22%3A%7B%22numCorPer%22%3A0%2C%22indOriCodPer%22%3A%22%22%2C%22codTipDocIde%22%3A%2201%22%2C%22numDocIde%22%3A%22".$number_dni."%22%2C%22codPais%22%3A%22604%22%2C%22nombres%22%3A%22%22%2C%22apePat%22%3A%22%22%2C%22apeMat%22%3A%22%22%2C%22strFechaNac%22%3A%22".$dia."%2F".$mes."%2F".$anio."%22%2C%22indSexo%22%3A%22%22%2C%22indEstCivil%22%3A%22%22%2C%22edad%22%3A0%2C%22codUbigeo%22%3A%22%22%2C%22desDireccion%22%3A%22%22%2C%22desFecNacReniec%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.persona.model.Persona%22%7D%2C%22vinlab%22%3A%7B%22numCorVlab%22%3A0%2C%22numCorPer%22%3A0%2C%22codTipDocIde%22%3A%22%22%2C%22numDocIde%22%3A%22%22%2C%22indOriCodPer%22%3A%22%22%2C%22desCorrElect%22%3A%22%22%2C%22codLdn%22%3A%22%22%2C%22numTelf%22%3A%22%22%2C%22codPaisNac%22%3A%22%22%2C%22cussp%22%3A%22%22%2C%22numRucCas%22%3A%22%22%2C%22indCatTra%22%3A%226%22%2C%22indCatPen%22%3A%226%22%2C%22indCatTer%22%3A%226%22%2C%22indCatPfl%22%3A%226%22%2C%22indSituVlab%22%3A%22%22%2C%22numCorDom1%22%3A0%2C%22numCorDom2%22%3A0%2C%22codFtramTra%22%3A%22%22%2C%22codFtramPen%22%3A%22%22%2C%22codFtramPfl%22%3A%22%22%2C%22codFtramTer%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.prestador.model.T4476Bean%22%7D%2C%22direccion1%22%3A%7B%22numCorDom%22%3A0%2C%22desDom%22%3A%22%22%2C%22desUbigeo%22%3A%22%22%2C%22codUbigeo%22%3A%22%22%2C%22numBlock%22%3A%22%22%2C%22numDpto%22%3A%22%22%2C%22numEtapa%22%3A%22%22%2C%22numInterior%22%3A%22%22%2C%22numKm%22%3A%22%22%2C%22numLote%22%3A%22%22%2C%22desMzn%22%3A%22%22%2C%22desNomVia%22%3A%22%22%2C%22numVia%22%3A%22%22%2C%22codTipVia%22%3A%22%22%2C%22desTipVia%22%3A%22%22%2C%22desNomZona%22%3A%22%22%2C%22codTipZona%22%3A%22%22%2C%22desTipZona%22%3A%22%22%2C%22desRef%22%3A%22%22%2C%22indOrigDom%22%3A%22%22%2C%22indDomiAct%22%3A%22%22%2C%22indSituDom%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.persona.model.Domicilio%22%7D%2C%22direccion2%22%3A%7B%22numCorDom%22%3A0%2C%22desDom%22%3A%22%22%2C%22desUbigeo%22%3A%22%22%2C%22codUbigeo%22%3A%22%22%2C%22numBlock%22%3A%22%22%2C%22numDpto%22%3A%22%22%2C%22numEtapa%22%3A%22%22%2C%22numInterior%22%3A%22%22%2C%22numKm%22%3A%22%22%2C%22numLote%22%3A%22%22%2C%22desMzn%22%3A%22%22%2C%22desNomVia%22%3A%22%22%2C%22numVia%22%3A%22%22%2C%22codTipVia%22%3A%22%22%2C%22desTipVia%22%3A%22%22%2C%22desNomZona%22%3A%22%22%2C%22codTipZona%22%3A%22%22%2C%22desTipZona%22%3A%22%22%2C%22desRef%22%3A%22%22%2C%22indOrigDom%22%3A%22%22%2C%22indDomiAct%22%3A%22%22%2C%22indSituDom%22%3A%22%22%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.persona.model.Domicilio%22%7D%2C%22class%22%3A%22pe.gob.sunat.servicio2.rtps.prestador.model.PrestadorBean%22%7D&filtrarPersona=&tipDocIden=01&numDocIden=".$number_dni."&fecNac=".$anio."-".$mes."-".$dia."&dojo.preventCache=1627604568217";
                $result= $this->requestHttp2("https://ww1.sunat.gob.pe/ol-ti-itrtpspresta/prestadores.htm?accion=buscarPersona&isActualiza=0&codPais=604",'POST',$data,true);
                
                

                $data_ubigeo=explode("-",$result['direccion1']['desUbigeo']);
                $fecha_nacimiento=explode("/",$result['persona']['strFechaNac']);
             
             
                if($result['persona']['indEstCivil']=="1"){
                    $estado_civil=($result['persona']['indSexo']=="1")? 'Soltero':'Soltera';
                }

                if($result['persona']['indEstCivil']=="2"){
                    $estado_civil=($result['persona']['indSexo']=="1")? 'Casado':'Casada';
                }

                if($result['persona']['indEstCivil']=="3"){
                    $estado_civil=($result['persona']['indSexo']=="1")? 'Divorciado':'Divorciada';
                }

                if($result['persona']['indEstCivil']=="4"){
                    $estado_civil=($result['persona']['indSexo']=="1")? 'Divorciado':'Divorciada';
                }

                $data_form=[
                    "DNI"=>$number_dni
                ];


                $headers=[];

                $ubigeo=Ubigeo::where('ubigeo',$result['direccion1']['codUbigeo'])->first();

               
                
                if($ubigeo!=null){

                

                    $departamento=Department::where('description',strtoupper($ubigeo->dpto))->first();
                    $province=Province::where('description',$ubigeo->prov)->where('department_id',$departamento->id)->first();
                    $distrito=District::where('description',$ubigeo->distrito)->first();
                    
                    
            

                    $ubigeo_reniec=[
                        str_pad($departamento->id, 2, "0", STR_PAD_LEFT),
                        str_pad($province->id, 4, "0", STR_PAD_LEFT),
                        str_pad($distrito->id, 6, "0", STR_PAD_LEFT)
                    ];

                

                    $ubigeo=Ubigeo::where('dpto',$departamento->description)->where('prov',$province->description)->where('distrito',$distrito->description)->first();
                    $ubigeo2=$distrito->id;
                    
                    

                  
                }else{

                    $ubigeo2="";
                    $ubigeo_reniec=[null,null,null];

                }

                $headers=[];
                $data_form=[
                    "DNI"=>$number_dni
                ];    

                $response=$this->requestHttp("http://intranet.agrorural.gob.pe/php/servicios.php?servicio=validacionDNI&operacion=",'POST',$data_form,$headers,"photo");
          
                $photo = "";

                if($response['success']==true){

                   $photo = json_decode($response['data'],true)['FOTO'];

                } 

                $dni=Dni::create([
                    "numero"=>$number_dni,
                    "nombre_completo"=>$result['persona']['apePat']." ".$result['persona']['apeMat']." ".$result['persona']['nombres'],
                    "nombres"=>$result['persona']['nombres'],
                    "apellido_paterno"=>$result['persona']['apePat'],
                    "apellido_materno"=>$result['persona']['apeMat'],
                    "fecha_nacimiento"=>$fecha_nacimiento[0]."-".$fecha_nacimiento[1]."-".$fecha_nacimiento[2],
                    "estado_civil"=>$estado_civil,
                    "sexo"=>($result['persona']['indSexo']=="1")? 'Masculino':'Femenino',
                    "edad"=>$result['persona']['edad'],
                    "domicilio"=>$result['direccion1']['desDom'],
                    "direccion_completa"=>$result['direccion1']['desDom']." ".strtoupper($data_ubigeo[0])." ".strtoupper($data_ubigeo[1])." ".strtoupper($data_ubigeo[2]),
                    "departamento"=>($ubigeo==null ) ? "" : strtoupper($ubigeo->dpto),
                    "provincia"=>($ubigeo==null ) ? "" : strtoupper($ubigeo->prov),
                    "distrito"=>($ubigeo==null ) ? "" : strtoupper($ubigeo->distrito),
                    "ubigeo2"=>($ubigeo==null ) ? "" :  $result['direccion1']['codUbigeo'],
                    "photo"=>$photo,
                ]);
                

                
                if($plus==true){

                    return response()->json([
                        'success' => true,
                        "data"=>[
                            "numero"=>$number_dni,
                            "nombre_completo"=>$result['persona']['apePat']." ".$result['persona']['apeMat']." ".$result['persona']['nombres'],
                            "nombres"=>$result['persona']['nombres'],
                            "apellido_paterno"=>$result['persona']['apePat'],
                            "apellido_materno"=>$result['persona']['apeMat'],
                            "fecha_nacimiento"=>$fecha_nacimiento[0]."-".$fecha_nacimiento[1]."-".$fecha_nacimiento[2],
                            "estado_civil"=>$estado_civil,
                            "sexo"=>($result['persona']['indSexo']=="1")? 'Masculino':'Femenino',
                            "edad"=>$result['persona']['edad'],
                            "direccion"=> (env('VIEW_ADRESS_DNI')==true) ? $result['direccion1']['desDom'] : '',
                            "direccion_completa"=>$result['direccion1']['desDom']." ".strtoupper($data_ubigeo[0])." ".strtoupper($data_ubigeo[1])." ".strtoupper($data_ubigeo[2]),
                            "departamento"=>($ubigeo==null ) ? "" : strtoupper($data_ubigeo[0]),
                            "provincia"=>($ubigeo==null ) ? "" : strtoupper($data_ubigeo[1]),
                            "distrito"=>($ubigeo==null ) ? "" : strtoupper($data_ubigeo[2]),
                            "codigo_verificacion" =>$this->digit_control($number_dni),
                            "ubigeo_reniec"=>($ubigeo==null ) ? "" : $result['direccion1']['codUbigeo'],
                            "location_id"=>(env('VIEW_ADRESS_DNI')==true) ?  $ubigeo_reniec : [ null,null,null],
                             "photo"=>$photo,
                             "ubigeo"=>(env('VIEW_ADRESS_DNI')==true) ?   $ubigeo_reniec
                           : [
                               null,
                               null,
                               null
                           ],
                            "nombre_o_razon_social"=>$result['persona']['apePat']." ".$result['persona']['apeMat']." ".$result['persona']['nombres'],
                            //"photo"=>$result_photo['FOTO'],
                        ]
                    ]);

                }else{

                    return response()->json([
                        'success' => true,
                        "data"=>[
                            "numero"=>$number_dni,
                            "nombre_completo"=>$result['persona']['apePat']." ".$result['persona']['apeMat']." ".$result['persona']['nombres'],
                            "nombres"=>$result['persona']['nombres'],
                            "apellido_paterno"=>$result['persona']['apePat'],
                            "apellido_materno"=>$result['persona']['apeMat'],
                            "direccion"=> (env('VIEW_ADRESS_DNI')==true) ? $result['direccion1']['desDom'] : '',
                            "direccion_completa"=>$result['direccion1']['desDom']." ".strtoupper($data_ubigeo[0])." ".strtoupper($data_ubigeo[1])." ".strtoupper($data_ubigeo[2]),
                            "departamento"=>($ubigeo==null ) ? "" : strtoupper($ubigeo->dpto),
                            "provincia"=>strtoupper($data_ubigeo[1]),
                            "distrito"=>strtoupper($data_ubigeo[2]),
                           
                            "codigo_verificacion" =>$this->digit_control($number_dni),
                            "nombre_o_razon_social"=>$result['persona']['apePat']." ".$result['persona']['apeMat']." ".$result['persona']['nombres'],
                            "ubigeo_reniec"=>$result['direccion1']['codUbigeo'],
                            "location_id"=>(env('VIEW_ADRESS_DNI')==true) ?  $ubigeo_reniec : [ null,null,null],
                            "ubigeo"=>(env('VIEW_ADRESS_DNI')==true) ?
                               $ubigeo_reniec
                           : [
                               null,
                               null,
                               null
                           ],
                            "photo"=> $photo,
                            ]
                    ]);
                    
                }

            }else{

                return response()->json([
                    "success"=>false,
                    "message"=>"N. no existe"
                ]);

            }
            

        } catch(Exception $e) {
            return [
                "line"    => $e->getLine(),
                "message" => $e->getMessage(),
                "file"    => $e->getFile()
            ];
           // echo "La excepción se creó en la línea: " . $e->getLine();
        }



    }


    public function login($userRuc, $userSunat, $userPassword){

        $this->requestHttp(
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
        $this->requestHttp('https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm?action=execute&code=10.5.3.1.1&s=ww1');

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


    public function requestHttp2($url, $method = 'get', $data ='',$result =false)
    {
        try {
           $directorio = dirname(__FILE__) . '..//..//..//public//tempo';
            $cookieFileLocation = $directorio . '//' . uniqid();

            $curl = [
                CURLOPT_USERAGENT =>  "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0",
                CURLOPT_HTTPHEADER => [],
                CURLOPT_ENCODING => "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => false,

                CURLOPT_HTTP_VERSION=> CURL_HTTP_VERSION_1_1,
                CURLOPT_VERBOSE => true,
                CURLOPT_POST => strtoupper($method) == 'POST',
                CURLOPT_FAILONERROR => TRUE,
                CURLOPT_COOKIEFILE => $cookieFileLocation,
                CURLOPT_COOKIEJAR => $cookieFileLocation,
            ];

            if (!empty($data)) {
                $curl[CURLOPT_POSTFIELDS] = is_array($data) ? http_build_query($data) : $data;
            }
            $curl[ CURLOPT_CUSTOMREQUEST]= "POST";
            $responses = $this->client->request(strtoupper($method), $url, [
                'curl' => $curl,
                'http_errors' => false,
            ]);
           // dd($result);
            if($result==false){
                return json_decode($responses->getBody()->getContents(), true);
            }else{
                return json_decode($responses->getBody()->getContents(), true);

            }


       } catch (Exception $e) {
        //    dd($e);
          return response()->json([
              $e
              ]);
       }

   }


}