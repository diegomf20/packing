<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaletSalida extends Model
{
    protected $table="palet_salida";

    public function cajas()
    {
        return $this->hasMany('App\Model\Caja')
                    ->join('etiqueta_caja as EC','EC.id','=','caja.etiqueta_caja_id')
                    ->join('calibre as CL','CL.id','=','EC.calibre_id')
                    ->join('categoria as CT','CT.id','=','EC.categoria_id')    
                    ->join('presentacion as PE','PE.id','=','EC.presentacion_id')
                    ->select(
                        'caja.palet_salida_id',
                        DB::raw('count(caja.id) cantidad'),
                        'CL.nombre_calibre',
                        'CT.nombre_categoria',
                        'PE.nombre_presentacion'
                    )
                    ->groupBy('caja.etiqueta_caja_id');
    }
}
