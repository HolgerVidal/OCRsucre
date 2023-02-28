<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class OcrController extends Controller
{
    
    public function obtener_texto_pdf(){

        try {
            
            print("pruebas de sistema");

        } catch (\Throwable $th) {
            $msg_error = __CLASS__." => ".__FUNCTION__." => Mensaje => ".$th->getMessage()." => en la linea: ".$th->getLine();
            Log::error($msg_error);
            dd($msg_error);
        }

    }

}
