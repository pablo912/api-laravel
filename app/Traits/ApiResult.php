<?php

namespace App\Traits;

trait ApiResult {


    public function digit_control($dni){
		if (strlen($dni) == 8 && is_numeric($dni)) {
			$suma = 0;
			$hash = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
			$suma = 5; // 10[NRO_DNI]X (1*5)+(0*4)
			for ($i = 2; $i < 10; $i++) {
				$suma += ($dni[$i - 2] * $hash[$i]); //3,2,7,6,5,4,3,2
			}
			$entero = (int) ($suma / 11);

			$digito = 11 - ($suma - $entero * 11);

			if ($digito == 10) {
				$digito = 0;
			} else if ($digito == 11) {
				$digito = 1;
			}
			return $digito;
		}
		return NULL;
    }


	function valid($valor){
        $valor = trim($valor);
        if ($valor){
            if ( strlen($valor) == 11 ){
                $suma = 0;
                $x = 6;
                for ( $i=0; $i<strlen($valor)-1; $i++ ){
                    if ( $i == 4 ){
                        $x = 8;
                    }
                    $digito = $valor[$i];
                    $x--;
                    if ($i==0){
                        $suma += ($digito*$x);
                    }else{
                        $suma += ($digito*$x);
                    }
                }
                $resto = $suma % 11;
                $resto = 11 - $resto;
                if ( $resto >= 10){
                    $resto = $resto - 10;
                }
                if ( $resto == $valor[strlen($valor)-1] ){
                    return true;
                }
            }
        }
        return false;
    }

    public function rmDir_rf($carpeta)
    {
      foreach(glob($carpeta . "/*") as $archivos_carpeta){             
        if (is_dir($archivos_carpeta)){
          rmDir_rf($archivos_carpeta);
        } else {
        unlink($archivos_carpeta);
        }
      }
      rmdir($carpeta);
     }


     public function validPlan($user){

        if($user->plan_id == 1 && $user->queries >= (int)$user->limit){

            return $this->errorResponse('Estimado usuario supero el limite de consultas por mes. Le recomendamos cambiar a la version premium',409);

        }


     }

}


