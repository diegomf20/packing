<?php

namespace App\Http\Controllers;

use App\Model\PaletSalida;
use App\Model\JabaSalida;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class JabaSalidaController extends Controller
{
    public function store(Request $request,$id)
    {
        /**
         * Linea 00
         * labor 00
         * codigo_barras
         */
        $estructura='{linea}{codigo_barras}';
        $resultado=JabaSalida::select(DB::raw('COUNT(DISTINCT(numero)) contar'))
                        ->where('palet_salida_id',$id)
                        ->first();
        $numero=1;
        if($resultado!=null){
            $numero=$resultado->contar+1;
        }
        $paletSalida=PaletSalida::where('id',$id)->first();
        $paletSalida->cantidad=$numero;
        $paletSalida->save();
        foreach ($request->codigos_barras as $key => $codigo) {
            $jabaSalida=new JabaSalida();
            $jabaSalida->codigo_barras=$codigo;
            $jabaSalida->linea=substr($codigo,0,2);
            $jabaSalida->codigo_labor=substr($codigo,2,2);
            $jabaSalida->codigo_operador=substr($codigo,4,8);
            $jabaSalida->palet_salida_id=$id;
            $jabaSalida->numero=$numero;
            $jabaSalida->save();
        }
        return response()->json([
            "status" => "OK",
            "data"   => "Jaba registrada"
        ]);
    }
}
