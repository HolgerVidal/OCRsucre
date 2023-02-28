<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Imagick;
use Log;

class OcrController extends Controller
{
    
    public function obtener_texto_pdf(){

		try{
			
			$tiempo_inicial = microtime(true);
			$paginate = false;
			if(isset($_REQUEST['paginate'])){
				if($_REQUEST['paginate']=='true'){
					$paginate = true;
				}
			}
			
			$tmpFile = "documento_ejemplo";
			$extension = "pdf";
			$ruta = "/var/www/html/archivo/tmp";
    
            $img = new Imagick();
            $img->setResolution(300, 300);
            $img->readImage("$ruta/$tmpFile.$extension");  //Open after yuo set resolution.
            $num_paginas = $img->getNumberImages(); //obtenemos el numero de paginas para iterar
            $img->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH); //Declare the units for resolution.
            $img->setImageCompression(imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(10);
            $img->setImageFormat('jpeg');
            $img->writeImages("$ruta/$tmpFile.jpg", false);
            $img->clear();
            $img->destroy();
    
            //@unlink("$ruta/$tmpFile.$extension"); //eliminamos el documento pdf
            $array_res = [];
            $text_res = "";
            $response = null;
            for($x=0; $x<$num_paginas; $x++){
                $name="";
                if($num_paginas>1){$name="-$x";}
                $cmd = "tesseract $ruta/$tmpFile$name.jpg $ruta/$tmpFile$name";
                exec($cmd, $salida, $err);
                $res = file_get_contents("$ruta/$tmpFile$name.txt");
                @unlink("$ruta/$tmpFile$name.jpg");
                @unlink("$ruta/$tmpFile$name.txt");
                $res=preg_replace("/[\r\n|\n|\r]+/"," ", $res);
                $array_res[$x]=["pag"=>$x,"texto"=>$res]; #solo si se pide paginar
                $text_res = $text_res." ".$res; #si no se quiere paginar
            }
            
            if($paginate){$response = $array_res;}
            else{$response = $text_res;}
            
            //calculamos el tiempo de ejecucion
            $tiempo_final = microtime(true);
            $tiempo = $tiempo_final - $tiempo_inicial;
            $tiempo = round($tiempo, 2);
            
            return response([
                "error"=>false,
                "tiempo"=>$tiempo,
                "tiempounidad"=>"segundos",
                "numpaginas"=>$num_paginas,
                "response"=>$response
            ]);
          
        }catch(\Throwable $th){
            // header("Content-Type: application/json; charset=UTF-8");
            $msg_error = __CLASS__." => ".__FUNCTION__." => Mensaje => ".$th->getMessage()." => en la linea: ".$th->getLine();
            Log::error($msg_error);
            dd($msg_error);
        }

    }

}
