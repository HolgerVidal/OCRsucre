<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Log;

class OcrController extends Controller
{
    
    public function obtener_texto_pdf(Request $request){

		try{
			
            set_time_limit(0);
            ini_set('memory_limit','-1');

            //obigen del documentos
            //TRAMITES = el cliente tiene que guardar el documento en 'Docs' y solo recivimos el nombre | finalmente nosotros lo borramos
            //EXTERNO = el cliente soli envia el nombre, nosotros tenemos que sacar el documento del servidor de archivos

            $origen = $request->origen;
            $ruta_documento = $request->ruta_documento;

            if($origen == "EXTERNO"){
                //obtenemos solo el nombre del documento
                $nombre_doc = strrpos($ruta_documento, '/');
                $nombre_doc = substr($ruta_documento, $nombre_doc+1);
                // Descargar el archivo del disco origen
                $descargado = Storage::disk("sftpArchivos")->get($ruta_documento);
                // Guardar el archivo en el disco destino
                Storage::disk("archivosLocal")->put($nombre_doc, $descargado);

                $tmpFile = $nombre_doc;
            }else{
                $tmpFile = $ruta_documento;
            }

			$tiempo_inicial = microtime(true);
			$paginate = false;
			if(isset($request->paginate)){
				if($request->paginate=='true'){
					$paginate = true;
				}
			}
			
			// $tmpFile = "documento_ejemplo.pdf";
			$ruta = base_path("docs");
    
            $img = new Imagick();
            $img->setResolution(300, 300);
            $img->readImage("$ruta/$tmpFile");  //Open after yuo set resolution.
            $num_paginas = $img->getNumberImages(); //obtenemos el numero de paginas para iterar
            $img->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH); //Declare the units for resolution.
            $img->setImageCompression(imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(10);
            $img->setImageFormat('jpeg');
            $img->writeImages("$ruta/$tmpFile.jpg", false);
            $img->clear();
            $img->destroy();
    
            //borramos el archivo temporal
            Storage::disk("archivosLocal")->delete($tmpFile);

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


    public function crear_img(Request $request){

		try{
			
            set_time_limit(0);
            ini_set('memory_limit','-1');

            //obigen del documentos
            //TRAMITES = el cliente tiene que guardar el documento en 'Docs' y solo recivimos el nombre | finalmente nosotros lo borramos
            //EXTERNO = el cliente soli envia el nombre, nosotros tenemos que sacar el documento del servidor de archivos

            $origen = $request->origen;
            $ruta_documento = $request->ruta_documento;

            if($origen == "EXTERNO"){
                //obtenemos solo el nombre del documento
                $nombre_doc = strrpos($ruta_documento, '/');
                $nombre_doc = substr($ruta_documento, $nombre_doc+1);
                // Descargar el archivo del disco origen
                $descargado = Storage::disk("sftpArchivos")->get($ruta_documento);
                // Guardar el archivo en el disco destino
                Storage::disk("archivosLocal")->put($nombre_doc, $descargado);

                $tmpFile = $nombre_doc;
            }else{
                $tmpFile = $ruta_documento;
            }

			$tiempo_inicial = microtime(true);
			$paginate = false;
			if(isset($request->paginate)){
				if($request->paginate=='true'){
					$paginate = true;
				}
			}
			
			// $tmpFile = "documento_ejemplo.pdf";
			$ruta = base_path("docs");

            $img = new Imagick();
            $img->setResolution(300, 300);
            $img->readImage("$ruta/$tmpFile");  //Open after yuo set resolution.
            $num_paginas = $img->getNumberImages(); //obtenemos el numero de paginas para iterar
            $img->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH); //Declare the units for resolution.
            $img->setImageCompression(imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(20);
            $img->setImageFormat('jpeg');
            $img->writeImages("$ruta/$tmpFile.jpg", false);
            $img->clear();
            $img->destroy();
    
            //borramos el archivo temporal
            Storage::disk("archivosLocal")->delete($tmpFile);

            $array_res = [];
            $text_res = "";
            $response = null;
            for($x=0; $x<$num_paginas; $x++){
                $name="";
                if($num_paginas>1){$name="-$x";}
                array_push($array_res, $tmpFile.$name);

            }
                        
            //calculamos el tiempo de ejecucion
            $tiempo_final = microtime(true);
            $tiempo = $tiempo_final - $tiempo_inicial;
            $tiempo = round($tiempo, 2);
            
            return response([
                "error"=>false,
                "tiempo"=>$tiempo,
                "tiempounidad"=>"segundos",
                "numpaginas"=>$num_paginas,
                "lista_img"=>$array_res
            ]);
          
        }catch(\Throwable $th){
            // header("Content-Type: application/json; charset=UTF-8");
            $msg_error = __CLASS__." => ".__FUNCTION__." => Mensaje => ".$th->getMessage()." => en la linea: ".$th->getLine();
            Log::error($msg_error);
            dd($msg_error);
        }

    }

}
