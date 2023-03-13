<?php

namespace App\Services;
use Carbon\Carbon;
use GuzzleHttp\Client;
use DiDom\Document as DiDom;
use GuzzleHttp\Exception\RequestException;


Class TipoCambio {

    private $client;    

    public function consultartipocambio($desde,$hasta){
        try {
            consultar:
            $response=$this->curl_sbs("02",$desde,$hasta);
            $fechaEmision = Carbon::parse($desde);
            $fechaExpiracion = Carbon::parse($hasta);
            $dias = $fechaExpiracion->diffInDays($fechaEmision);
            $result=[];
       
            if($response!=false){
            $xp = new DiDom($response);
            $pos = strpos($response,"No existe");
                if($pos === false){
                    $rows_body = $xp->find('body')[0]->find('table');
                    if(count($rows_body)==0){
                        sleep(5);
                        goto consultar;
                    }
                    $rows = $xp->find('table')[11]->find('tr');
                    if($dias>0){
                         $inicio=1;
                    }else{
                        $inicio=0;
                    }
                    for ($y=$inicio; $y < count($rows); $y++) { 
                        $cell = $rows[$y]->find('td');
                        for ($i=0; $i < count($cell); $i++) { 
                            $result[] = [
                                'dia' => str_replace([" ", "\n", "\r", "\t"], '', $cell[$i++]->text()),
                                'compra' => str_replace([" ", "\n", "\r", "\t"], '', $cell[$i++]->text()),
                                'venta' => str_replace([" ", "\n", "\r", "\t"], '', $cell[$i++]->text()),
                            ];
                            $i--;
                        }
                    }
                    $result_dolar=array(
                        "moneda"=>"Dolar Americano",
                        "codigo_sbs"=>"02",
                        "result"=>$result
                    );
               
                    return response()->json( [
                        "success"=>true,
                        "USD"=>$result_dolar,
                    ]);
                    }else{
                        return response()->json( [
                            "success"=>false,
                            "message"=>"No existe informacion para la fecha elegida"
                        ]);
                    }
            }else{
                return response()->json( [
                    "success"=>true,
                    "message"=>"Ocurrio un Error al consultar"
                ]);
            }    
        } catch (RequestException $exception) {
            return $exception->getResponse()->getBody();
         }
 
    }

    public function curl_sbs($moneda,$desde,$hasta) {

        $explode_desde=explode("-",$desde);
        $explode_hasta=explode("-",$hasta);
        $dia_desde= $explode_desde[0];
        $mes_desde= $explode_desde[1];
        $anio_desde= $explode_desde[2];
        $dia_hasta= $explode_hasta[0];
        $mes_hasta= $explode_hasta[1];
        $anio_hasta= $explode_hasta[2];
        try {
            $this->client = new Client(['verify' => false, 'http_errors' => false]);
           $curl = [
            CURLOPT_URL => "https://www.sbs.gob.pe/app/stats/seriesH_TC-CV-Historico.asp",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "FECHA_CONSULTA_1=".$dia_desde."%2F".$mes_desde."%2F".$anio_desde."&FECHA_CONSULTA_2=".$dia_hasta."%2F".$mes_hasta."%2F".$anio_hasta."&s_moneda=".$moneda."&button22=Consultar",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
            ),
           ];
          $responses = $this->client->request(strtoupper("POST"),"https://www.sbs.gob.pe/app/stats/seriesH_TC-CV-Historico.asp", [
               'curl' => $curl,
           ]);
           return (string)$responses->getBody(true);
           
      } catch (RequestException $exception) {
         return $exception->getResponse()->getBody();
      }
     
    }
 }



?>