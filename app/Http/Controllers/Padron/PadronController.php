<?php

namespace App\Http\Controllers\Padron;

use App\Http\Controllers\Controller;
use App\Models\Update;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PadronController extends Controller
{

    use ApiResponser;

   
    public function __construct(){

        ini_set('memory_limit', '-1');

        ini_set('max_execution_time', 3600); //3 minutes

    }

    public function download(){

        try {

            $carpeta = base_path().'/public/padron_rar/';
            
            if(!file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
            }


            $filepath = str_replace(DIRECTORY_SEPARATOR, '/', public_path("padron_rar".DIRECTORY_SEPARATOR."padron_reducido_ruc.zip"));

            $contents = file_get_contents('https://www2.sunat.gob.pe/padron_reducido_ruc.zip');   

            file_put_contents($filepath, $contents);

           $data = [ 
             'success' => true,
             'message' => 'Datos descargados de sunat correctamente'
            ];

              return $this->showMessage($data,200);

        }catch(Exception $e)
        {   
            $error = [ 
                'success' => false, 
                'message' => $e->getMessage()
            ];

            return $this->errorResponse($error,400);
        }

    }

    public function extract(){

        try {

            $carpeta = base_path().'/public/padron_extract/';
            
            if(!file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            $zip = new ZipArchive;
       
            $file = base_path().'/public/padron_rar/padron_reducido_ruc.zip';
            $comprimido= $zip->open($file);
            if ($comprimido=== TRUE) {
           
                $zip->extractTo($carpeta);
                $zip->close();
    
                $data = [ 
                    'success' => true,
                    'message' => 'Archivo descomprimido con exito'
                   ];
       
                 
                return $this->showMessage($data,200);

            } else {
                
                $error = [

                    'success' => false,
                    'message' => 'Error descomprimiendo el archivo zip'

                ];

                
               return $this->errorResponse($error,400);
            }

      

        } catch (Exception $e) {

            $error = ['success' => false, 'message' => $e->getMessage()];

            return $this->errorResponse($error,400);

        }


    }

    public function loadtdata()
    {
       

        try {


            DB::table('empresas')->truncate();
      
            $file = public_path();
            $prefix = 'padron_extract';
            $prefix2 = 'txt';
            $key = 'padron_reducido_ruc';
            $file =  str_replace(DIRECTORY_SEPARATOR, '/', public_path("{$prefix}" . DIRECTORY_SEPARATOR . "{$key}.{$prefix2}"));
           
            $query = "LOAD DATA LOCAL INFILE '" . $file . "'
            INTO TABLE empresas  FIELDS TERMINATED BY '|' LINES TERMINATED BY '\n' IGNORE 1 LINES
                    (ruc,
                    razon,
                    estado,
                    condicion,
                    ubigeo,
                    tipo_via,
                    nombre_via,
                    codigo_zona,
                    tipo_zona,
                    numero,
                    interior,
                    lote,
                    departamento,
                    manzana,
                    km,
                    @status,
                    @created_at,
                    @updated_at)
            SET status=1,created_at=NOW(),updated_at=null";
            DB::connection()->getPdo()->exec($query);
            $date = Carbon::now();
            $date = $date->format('Y-m-d');
            Update::create([
                "date" => $date
            ]);
            $row = Update::get()->last();
            $update_last = Carbon::parse($row->date)->format('d-m-Y');
            // if (file_exists($file)) {
            //     if (unlink($file)) {
            //     }
            // }

            $extract = base_path().'/public/padron_extract/';
            $rar = base_path().'/public/padron_rar/';

            // $this->rmDir_rf($extract);
            // $this->rmDir_rf($rar);

            return [
                'success' => true,
                'message' => 'Datos csv cargados a BD correctamente',
                'ultima_actualizacion' => $update_last
            ];
        } catch (Exception $e) {
                 $error = ['success' => false, 'message' => $e->getMessage()];

                 return $this->errorResponse($error,400);
        }
    }


}
