<?php

namespace App\Http\Controllers;

use App\Model\Tareo;
use App\Model\Parametro;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\Labor;
use App\Model\EtiquetaCaja;
use App\Model\Zpl;

class PrintZPLController extends Controller
{
    /**
     * ip_print: x.x.x.x    => IP de Impresora Zebra.
     * codigo_operador
     */
    public function cajas(Request $request){
        $ip_print = $request->ip_print;
        if (!$request->has('return')) {
            if (!$this->ping($ip_print)){
                return response()->json([
                    "status"    => "ERROR",
                    "data"      => "Impresora Desconectada."
                ]);
            }
        }
        $codigo_operador = $request->codigo_operador;

        $tareo=Tareo::where('codigo_operador',$codigo_operador)
                    ->select('tareo.*','operador.ape_operador')
                    ->join('operador','tareo.codigo_operador','=','operador.dni')
                    ->orderBy('id','DESC')
                    ->first();
        if ($tareo==null) {
            return response()->json([
                "status"    => "ERROR",
                "data"      => "Tareo no existe."
            ]);
        }
        $nombre_operador=$tareo->ape_operador;

        // dd($tareo);
        $labor=Labor::where('codigo_auxiliar','like','%'.$tareo->labor_id.'%')
            ->first();
        // dd($labor);
        
        if ($labor==null) {
            return response()->json([
                "status"    => "ERROR",
                "data"      => "Labor no permitida."
            ]);
        }
        $labor_letra=($labor->descripcion)[0];
        $labor_id=$labor->codigo_labor;
        // $linea_id=str_pad($tareo->linea_id, 2, "0", STR_PAD_LEFT);
        $linea_id=($tareo->linea_id==null) ? '00': str_pad($tareo->linea_id, 2, "0", STR_PAD_LEFT);
            $string_zpl="^XA
                ^FT0,26
                ^A0N,14,15
                ^FB280,1,0,C
                ^FD{nombre_operador}^FS
                ^FT160,100
                ^AAN,40,15
                ^FD{labor_letra}^FS
                ^FT40,160
                ^BQN,2,5
                ^FDMA,{linea}{labor}{operador}{autonumerico}^FS
                ^XZ";
            $parametros=array(
                'linea'         =>  $linea_id,
                'operador'      =>  $codigo_operador,
                'labor'         =>  $labor_id,
                'labor_letra'   =>  $labor_letra,
                'nombre_operador'=> $nombre_operador
            );
        
            // foreach($data[$i*$columna+$j] as $key=>$value){
            //     $string_zpl_bk=str_replace('['.$key.']',$value,$string_zpl_bk);
            // }
            $columna=3;
            $string_zpl=str_replace('^XA','',$string_zpl);
            $string_zpl=str_replace('^XZ','',$string_zpl);
            
            $string_zpl_new="";
            $string_zpl_new.="^XA";
            for ($j=0; $j < $columna; $j++) {
                $string_zpl_new.=$this->columnaEtiqueta($string_zpl,$j,270);
            }
            $string_zpl_new.="^XZ";
        if ($request->has('return')) {
            return response()->json([
                "status"    =>  "OK",
                "data"      =>  $this->cast_zpl($string_zpl_new,$parametros)
            ]);
        }else{
            $this->print_red($ip_print,9100,$this->cast_zpl($string_zpl_new,$parametros));
            return response()->json([
                "status" => "OK",
                "data"   => "Imprimiendo."

            ]);
        }
    }
    public function caja_palet(Request $request){
        $ip_print=$request->ip_print;
        $codigo="P-".$request->codigo;
        if ($this->ping($ip_print)){            
            $string_zpl="^XA
                        ^FO15,15
                        ^BQN,2,4
                        ^FDQA,$codigo^FS
                        ^XZ";
            $this->print_red($ip_print,9100,$string_zpl);
            return response()->json([
                "status" => "OK",
                "data"   => "Imprimiendo."
            ]);
        }else{
            return response()->json([
                "status"    => "ERROR",
                "data"      => "Impresora Desconectada."
            ]);
        }
    }
    public function palet_entrada_all(Request $request){
        $ip_print = $request->ip_print;
        $sub_lote_id=$request->sub_lote_id;

        $w_etiqueta=420;
                    //hola
        $string_zpl="^XA
        ^FT25,60
        ^AAN,35,15
        ^FB780,1,0,C
        ^FD[empresa]^FS
        
        ^FT260,120
        ^AAN,30,15
        ^FD[variedad]^FS
        
        ^FT260,170
        ^AAN,30,15
        ^FDN. Jabas: [num_jabas]^FS
        
        ^FT260,220
        ^AAN,30,15
        ^FDN. Viaje: [viaje]^FS
        
        ^FT260,270
        ^AAN,30,15
        ^FDCosecha: [fecha_cosecha]^FS
        
        ^FT25,380
        ^AAN,40,20
        ^FB760,1,0,R
        ^FD[num_palet]^FS
        
        ^FT25,320
        ^BQN,2,10
        ^FDMA,P-[palet_id]^FS
        ^XZ";
            // dd($string_zpl);      
            // $string_zpl=
        $string_zpl=str_replace('^XA','',$string_zpl);
        $string_zpl=str_replace('^XZ','',$string_zpl);
        $query="SELECT 	PE.num_palet,
                        codigo, 
                        CL.descripcion empresa,
                        PE.peso peso, 
                        VA.nombre_variedad variedad,
                        PE.num_jabas,
                        SL.viaje,
                        PE.id palet_id,
                        DATE_FORMAT(LI.fecha_cosecha,'%d-%m-%Y') fecha_cosecha
                FROM lote_ingreso LI 
                INNER JOIN cliente CL ON CL.id=LI.cliente_id
                INNER JOIN sub_lote SL ON SL.lote_id=LI.id
                INNER JOIN palet_entrada PE ON PE.sub_lote_id=SL.id
                INNER JOIN variedad VA on LI.variedad_id=VA.id
                WHERE sub_lote_id=?
                ORDER BY palet_id DESC";
        $data=DB::select(DB::raw("$query"),[$sub_lote_id]);
        
        $columna=1;
        $string_zpl_new="";
        $data_part=array_chunk($data,$columna);
        $zpl_envio="";
        for ($i=0; $i < count($data_part); $i++) {
            $zpl_fila="";
            for ($j=0; $j < count($data_part[$i]); $j++) {
                $zpl_columna=$this->columnaEtiqueta($string_zpl,$j);
                foreach($data_part[$i][$j] as $key=>$value){
                    $zpl_columna=str_replace('['.$key.']',$value,$zpl_columna);
                }
                $zpl_fila.=$zpl_columna;
            } 
            $zpl_envio.="^XA$zpl_fila^XZ";
        }

        if ($request->has('getZPL')) {
            return response()->json([
                "status" => "OK",
                "data"   => $zpl_envio
            ]);
        }
        if ($this->ping($ip_print)){
            $this->print_red($ip_print,9100,$zpl_envio);
            return response()->json([
                "status" => "OK",
                "data"   => "Imprimiendo."
            ]);
        }
        else {
            return response()->json([
                "status"    => "ERROR",
                "data"      => "Impresora Desconectada."
            ]);
        }
    }

    public function palet_entrada(Request $request){
        $palet_entrada_id=$request->palet_entrada_id;

        $w_etiqueta=410;
        $string_zpl="^XA
        ^FT25,60
        ^AAN,35,15
        ^FB780,1,0,C
        ^FD[empresa]^FS
        
        ^FT260,120
        ^AAN,30,15
        ^FD[variedad]^FS
        
        ^FT260,170
        ^AAN,30,15
        ^FDN. Jabas: [num_jabas]^FS
        
        ^FT260,220
        ^AAN,30,15
        ^FDN. Viaje: [viaje]^FS
        
        ^FT260,270
        ^AAN,30,15
        ^FDCosecha: [fecha_cosecha]^FS
        
        ^FT25,380
        ^AAN,40,20
        ^FB760,1,0,R
        ^FD[num_palet]^FS
        
        ^FT25,320
        ^BQN,2,10
        ^FDMA,P-[palet_id]^FS
        ^XZ";
        $string_zpl=str_replace('^XA','',$string_zpl);
        $string_zpl=str_replace('^XZ','',$string_zpl);

        $query="SELECT 	PE.num_palet,
                        codigo, 
                        CL.descripcion empresa,
                        PE.peso peso, 
                        VA.nombre_variedad variedad,
                        PE.num_jabas,
                        SL.viaje,
                        PE.id palet_id,
                        DATE_FORMAT(LI.fecha_cosecha,'%d-%m-%Y') fecha_cosecha
                FROM lote_ingreso LI 
                INNER JOIN cliente CL ON CL.id=LI.cliente_id
                INNER JOIN sub_lote SL ON SL.lote_id=LI.id
                INNER JOIN palet_entrada PE ON PE.sub_lote_id=SL.id
                INNER JOIN variedad VA on LI.variedad_id=VA.id
                WHERE PE.id=?
                ORDER BY palet_id DESC";
            $data=DB::select(DB::raw("$query"),[$palet_entrada_id]);
            $string_zpl_new="";

            $columna=1;
            
            for ($i=0; $i < count($data)/$columna; $i++) {
                $recorrido=($i+1)*$columna<count($data) ? $columna : (count($data)-($i)*$columna);
                $string_zpl_new.="^XA";
                for ($j=0; $j < $recorrido; $j++) {
                    
                    $string_zpl_bk=$this->columnaEtiqueta($string_zpl,$j);
                    foreach($data[$i*$columna+$j] as $key=>$value){
                        $string_zpl_bk=str_replace('['.$key.']',$value,$string_zpl_bk);
                    }
                    $string_zpl_new.=$string_zpl_bk;
                }
                $string_zpl_new.="^XZ";
            }            
        return response()->json([
            "status" => "OK",
            "data"   => $string_zpl_new
        ]);
    }

    public function palet_Salida(Request $request){
        $ip_print = $request->ip_print;
        $palet_id=$request->palet_id;

        $string_zpl="CT~~CD,~CC^~CT~
                    ^XA
                    ~TA000
                    ~JSN
                    ^LT0
                    ^MNW
                    ^MTT
                    ^PON
                    ^PMN
                    ^LH0,0
                    ^JMA
                    ^PR8,8
                    ~SD15
                    ^JUS
                    ^LRN
                    ^CI27
                    ^PA0,1,1,0
                    ^XZ
                    ^XA
                    ^MMT
                    ^PW831
                    ^LL1624
                    ^LS0
                    ^FO170,4^GB0,1620,5^FS
                    ^FT63,1580^A0B,37,38^FH\^CI28^FDEXPORTED^FS^CI27
                    ^FT140,1504^A0B,79,79^FH\^CI28^FD[nombre_productor]^FS^CI27
                    ^FT236,1580^A0B,37,38^FH\^CI28^FDPACKED AND PROCESSED^FS^CI27
                    ^FT318,1504^A0B,73,74^FB731,1,19,C^FH\^CI28^FDJAYANCA FRUITS S.A.C^FS^CI27
                    ^FO343,4^GB0,1620,5^FS
                    ^FO516,4^GB0,1620,5^FS
                    ^FT462,1580^A0B,102,101^FB1252,1,26,C^FH\^CI28^FDN° DE PALET:  [codigo_palet]^FS^CI27
                    ^FT359,187^BQN,2,6
                    ^FH\^FDLA,[palet_id]^FS
                    ^FO659,4^GB0,1620,5^FS
                    ^FO516,789^GB311,0,5^FS
                    ^FT609,745^A0B,37,38^FH\^CI28^FDN° BOXES^FS^CI27
                    ^FO516,453^GB311,0,5^FS
                    ^FT609,413^A0B,37,38^FH\^CI28^FD[numero_cajas]^FS^CI27
                    ^PQ1,0,1,Y
                    ^XZ";

            $query="SELECT 	CL.descripcion nombre_productor, 
                                CONCAT(LPAD(PS.numero,6,'0'),'-',CL.cod_cartilla) codigo_palet,
                                PS.id palet_id,
                                CAL.nombre_calibre calibre,
                                COUNT(CA.id) numero_cajas
                    FROM palet_salida PS 
                    INNER JOIN cliente CL ON PS.cliente_id=CL.id
                    INNER JOIN caja CA ON CA.palet_salida_id=PS.id
                    INNER JOIN etiqueta_caja EC ON CA.etiqueta_caja_id=EC.id
                    INNER JOIN calibre CAL ON CAL.id = EC.calibre_id
                    WHERE PS.id=?";
        $data=DB::select(DB::raw("$query"),[$palet_id])[0];
        // dd($data);
        $data=json_decode(json_encode($data), true);
        
        foreach($data as $key=>$value){
            $string_zpl=str_replace('['.$key.']',$value,$string_zpl);
        }
        return response()->json($string_zpl);
    }

    public function columnaEtiqueta($zpl,$columna,$width=420)
    {
        preg_match_all("/\^FT(.*?),/",$zpl,$arr);
        $modificado=$zpl;
        for ($i=0; $i < count($arr[0]); $i++) { 
            $x=($arr[1][$i]+$width*$columna);
            $modificado=preg_replace("/\\".$arr[0][$i]."/",'^FT'.$x.',',$modificado,1);
        }
        return $modificado;
    }

    public function print_red($ip,$port,$string_print){
        try
        {    
            $fp=pfsockopen($ip,$port);
            fputs($fp,$string_print);
            fclose($fp);
        }
        catch (Exception $e) 
        {
            dd($e->getMessage());
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    public function cast_zpl($string_zpl,$parametros){
        foreach($parametros as $key=>$value){
            $string_zpl=str_replace('{'.$key.'}',$value,$string_zpl);
        }

        $print="";
        $number=0;

        $parametro=Parametro::where('descripcion','index_codigo_trabajador')->first();
        $index_db=(int)$parametro->valor;
        $cantidad=60;
        
        if(-1<strpos($string_zpl,'{autonumerico}')){
            $separate_autonumerico=explode('{autonumerico}',$string_zpl);
            $conteo=count($separate_autonumerico);
            // dd($index_db);
            $temp_index_db=$index_db;
            while ($temp_index_db<$index_db+$cantidad ){
                /**
                 * Imprimer entre la separacion
                 */
                for ($i=0; $i < $conteo-1; $i++) { 
                    $value=$separate_autonumerico[$i];
                    $temp_index_db+=1;
                    $index=substr(str_pad($temp_index_db, 5, "0", STR_PAD_LEFT),-5);
                    $print=$print.$value.$index;
                }
                /**
                 * imprime el ultimo retazo
                 */
                $print=$print.$separate_autonumerico[$conteo-1];
            }
        }
        $parametro->valor=$temp_index_db;
        $parametro->save();
        return $print;
    }


    public function myOS(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === (chr(87).chr(73).chr(78)))
            return true;

        return false;
    }

    public function ping($ip_addr){
        if ($this->myOS()){
            if (!exec("ping -n 1 -w 1 ".$ip_addr." 2>NUL > NUL && (echo 0) || (echo 1)"))
                return true;
        } else {
            if (!exec("ping -q -c1 ".$ip_addr." >/dev/null 2>&1 ; echo $?"))
                return true;
        }

        return false;
    }

    public function muestra_etiqueta_caja(Request $request){
        $etiqueta_id=$request->etiqueta_caja_id;
        DB::statement("SET lc_time_names = 'es_ES'");

        $etiquetaCaja=EtiquetaCaja::select(
                        DB::raw('CONCAT("C-",etiqueta_caja.id) codigo_caja'),
                        'etiqueta_caja.*',
                        DB::raw("SUBSTRING(YEAR(LI.fecha_cosecha),-2,2) anio_cos"),
                        DB::raw("SUBSTRING(YEAR(etiqueta_caja.fecha_empaque),-2,2) anio_emp"),
                        DB::raw("DAYOFYEAR(DATE_FORMAT(etiqueta_caja.fecha_empaque, '2016-%m-%d')) jul_emp"),
                        DB::raw("DAYOFYEAR(DATE_FORMAT(LI.fecha_cosecha, '2016-%m-%d')) jul_cos"),
                        DB::raw("CONCAT(UPPER(LEFT(DATE_FORMAT(etiqueta_caja.fecha_empaque,'%b-%d-%Y'), 1)), LOWER(SUBSTRING(DATE_FORMAT(etiqueta_caja.fecha_empaque,'%b-%d-%Y'), 2))) fecha_empaque"),
                        DB::raw("DATE_FORMAT(etiqueta_caja.fecha_empaque,'%Y') as e_yyyy"),
                        DB::raw("CONCAT(UPPER(LEFT(DATE_FORMAT(etiqueta_caja.fecha_empaque,'%b'), 1)), LOWER(SUBSTRING(DATE_FORMAT(etiqueta_caja.fecha_empaque,'%b'), 2))) as e_mb"),
                        DB::raw("DATE_FORMAT(etiqueta_caja.fecha_empaque,'%d') as e_dd"),
                        'LI.codigo as codigo_lote',
                        'CLI.descripcion as productor',
                        'CL.nombre_calibre as calibre',
                        'MA.nombre_materia as materia',
                        DB::raw("IF(SUBSTR(peso_neto,-1) = '0', LEFT(peso_neto,length(peso_neto)-1),peso_neto) peso_neto"),
                        'VA.nombre_variedad as variedad',
                        'VA.variedad_licenciada',
                        'FU.cod_lugar_produccion',
                        'PLU.nombre_plu as plu',
                        // 'EAN.descripcion as codigo_ean',
                        'CT.nombre_categoria as categoria'
                        )
                    ->join('calibre as CL','CL.id','=','etiqueta_caja.calibre_id')
                    ->join('categoria as CT','CT.id','=','etiqueta_caja.categoria_id')
                    ->join('lote_ingreso as LI','LI.id','=','etiqueta_caja.lote_ingreso_id')
                    ->join('cliente as CLI','CLI.id','=','LI.cliente_id')
                    ->join('plu as PLU','PLU.id','=','etiqueta_caja.plu_id')
                    ->join('presentacion as PE','PE.id','=','etiqueta_caja.presentacion_id')
                    ->join('materia as MA','MA.id','=','LI.materia_id')
                    ->join('fundo as FU','FU.id','=','LI.fundo_id')
                    ->join('variedad as VA','VA.id','=','LI.variedad_id')
                    // ->leftJoin('codigo_ean as EAN',function ($join) {
                    //     $join->on('EAN.variedad_id', '=', 'LI.variedad_id')
                    //     ->on('EAN.calibre_id', '=', 'etiqueta_caja.calibre_id');
                    // })
                    ->where('etiqueta_caja.id',$etiqueta_id)
                    ->orderBy('id','DESC')
                    ->first();
                    // dd($etiquetaCaja);
        
        $zpl=ZPL::where('id',$request->zpl_id)->first()->contenido;
                 
        foreach($etiquetaCaja->toArray() as $key=>$value){
            $zpl=str_replace('['.$key.']',$value,$zpl);
        }
        return response()->json($zpl);
    }

    public function preview(Request $request){
        $zpl=$request->zpl;
        // dd($zpl);
        $curl = curl_init();
        // adjust print density (8dpmm), label width (4 inches), label height (6 inches), and label index (0) as necessary
        curl_setopt($curl, CURLOPT_URL, "http://api.labelary.com/v1/printers/8dpmm/labels/4x3/0/");
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $zpl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: image/png")); // omit this line to get PNG images back
        $result = curl_exec($curl);
        
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            // $file = fopen("label.png", "w");
            // fwrite($file, $result);
            // fclose($file);
        } else {
            print_r("Error: $result");
        }
        curl_close($curl);
        // dd($file);
        return response($result)->header('Content-type','image/png');
    }
    
}
