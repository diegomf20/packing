<?php

namespace App\Http\Controllers;

use App\Model\LoteIngreso;
use App\Model\SubLote;
use App\Model\PaletSalida;
use App\Http\Requests\LoteIngresoValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->has('descarte')) {
            $lotesIngreso=LoteIngreso::join('cliente','cliente.id','=','lote_ingreso.cliente_id')
                ->join('materia','materia.id','=','lote_ingreso.materia_id')
                ->join('variedad','variedad.id','=','lote_ingreso.variedad_id')
                ->leftJoin('tipo','tipo.id','=','lote_ingreso.tipo_id')
                ->leftJoin('descarte as DE','DE.lote_id','=','lote_ingreso.id')
                ->select('lote_ingreso.*','cliente.descripcion','materia.nombre_materia','variedad.nombre_variedad','tipo.nombre_tipo')
                ->whereNull('DE.id')
                ->get();
            return response()->json($lotesIngreso);
        }
        $lotesIngreso=LoteIngreso::join('cliente','cliente.id','=','lote_ingreso.cliente_id')
            ->join('materia','materia.id','=','lote_ingreso.materia_id')
            ->join('variedad','variedad.id','=','lote_ingreso.variedad_id')
            ->leftJoin('tipo','tipo.id','=','lote_ingreso.tipo_id')
            ->select('lote_ingreso.*','cliente.descripcion','materia.nombre_materia','variedad.nombre_variedad','tipo.nombre_tipo');
        if ($request->has('estado')) {
            $lotesIngreso=$lotesIngreso->whereIn('estado',explode(',',$request->estado));
        }
        $lotesIngreso=$lotesIngreso
                        ->orderBy('lote_ingreso.id','DESC')
                        ->get();
        return response()->json($lotesIngreso); 
    }

    public function generar_codigo(Request $request){
        $materia_id=$request->materia_id;
        $variedad_id=$request->variedad_id;
        $cliente_id=$request->cliente_id;
        $fundo_id=$request->fundo_id;
        $fecha_cosecha=$request->fecha_cosecha;
        $query="SELECT 
                CONCAT(
                    'J',
                    LPAD(DAYOFYEAR(DATE_FORMAT('$fecha_cosecha', '2016-%m-%d')),3,'0'),
                    (SELECT cod_cartilla FROM cliente where id=$cliente_id),
                    (SELECT cod_cartilla FROM fundo where id=$fundo_id)
                    -- (SELECT cod_cartilla FROM materia where id=$materia_id),
                    -- (SELECT cod_cartilla FROM variedad where id=$variedad_id),
                    -- SUBSTRING(YEAR('$fecha_cosecha'),-1,1),
                ) codigo";
        $query="SELECT 
                    'J' codigo_planta,
                    LPAD(DAYOFYEAR(DATE_FORMAT('$fecha_cosecha', '2016-%m-%d')),3,'0') juliano,
                    (SELECT cod_cartilla FROM cliente where id=$cliente_id) codigo_cliente,
                    (SELECT cod_cartilla FROM fundo where id=$fundo_id) codigo_fundo,
                    (SELECT cod_cartilla FROM materia where id=$materia_id) codigo_materia,
                    (SELECT cod_cartilla FROM variedad where id=$variedad_id) codigo_variedad,
                    SUBSTRING(YEAR('$fecha_cosecha'),-1,1) digito_anio
                ";
        $data=DB::select(DB::raw("$query"),[])[0];      
        switch ($materia_id) {
            case 3:
                $data=$data->codigo_planta.$data->juliano.$data->codigo_cliente.$data->codigo_fundo;
                break;
            
            default:
                $data=$data->codigo_planta.$data->codigo_cliente.$data->codigo_fundo.$data->codigo_materia.$data->codigo_variedad.$data->digito_anio.$data->juliano;
                break;
        }
        return response()->json([
            "codigo" =>$data
        ]);  
    }

    public function palets_salida(Request $request){
        $loteIngreso=LoteIngreso::with('palets_salida')
                            ->join('cliente','cliente.id','=','lote_ingreso.cliente_id')
                            ->join('materia','materia.id','=','lote_ingreso.materia_id')
                            ->join('variedad','variedad.id','=','lote_ingreso.variedad_id')
                            ->join('tipo','tipo.id','=','lote_ingreso.tipo_id')
                            ->select('lote_ingreso.*',
                                    'cliente.descripcion as cliente',
                                    'materia.nombre_materia as materia',
                                    'variedad.nombre_variedad as variedad',
                                    'tipo.nombre_tipo as tipo'
                            )
                            ->where('estado',$request->estado)
                            ->get();
        return response()->json($loteIngreso);
    }

    public function store(LoteIngresoValidate $request)
    {
        $loteIngreso=new LoteIngreso();
        $loteIngreso->codigo=$request->codigo;
        $loteIngreso->cliente_id=$request->cliente_id;
        $loteIngreso->materia_id=$request->materia_id;
        $loteIngreso->variedad_id=$request->variedad_id;
        $loteIngreso->tipo_id=0;
        $loteIngreso->fundo_id=$request->fundo_id;
        $loteIngreso->parcela_id=$request->parcela_id;
        $loteIngreso->fecha_cosecha=$request->fecha_cosecha;
        $loteIngreso->fecha_proceso=$request->fecha_cosecha;
        $loteIngreso->estado="Pendiente";
        $loteIngreso->save();
        return response()->json([
            "status" => "OK",
            "data"   => $loteIngreso,
        ]);
    }

    public function show($id)
    {
        $loteIngreso=LoteIngreso::where('lote_ingreso.id',$id)
                    ->join('cliente','cliente.id','=','lote_ingreso.cliente_id')
                    ->join('materia','materia.id','=','lote_ingreso.materia_id')
                    ->join('variedad','variedad.id','=','lote_ingreso.variedad_id')
                    ->select('lote_ingreso.*','cliente.descripcion','materia.nombre_materia','variedad.nombre_variedad')
                    ->first();
        return response()->json($loteIngreso);
    }
    public function codigo($codigo){
        $loteIngreso=LoteIngreso::where('lote_ingreso.codigo',$codigo)
                    ->join('cliente','cliente.id','=','lote_ingreso.cliente_id')
                    ->join('materia','materia.id','=','lote_ingreso.materia_id')
                    ->join('variedad','variedad.id','=','lote_ingreso.variedad_id')
                    ->select('lote_ingreso.*','cliente.descripcion','materia.nombre_materia','variedad.nombre_variedad')
                    ->first();
        return response()->json($loteIngreso);
    }

    public function update(Request $request, $id)
    {
        if ($request->has('estado')) {
            $estado=$request->estado;
            switch ($estado) {
                case 'Cerrado':
                    $loteIngreso=LoteIngreso::where('id',$id)->first();
                    $loteIngreso->estado=$estado;
                    $loteIngreso->save();
                    $sublotes=SubLote::where('lote_id',$loteIngreso->id)->get();
                    foreach ($sublotes as $key => $sublote) {
                        $sublote->estado=$estado;
                        $sublote->save();
                    }
                    return response()->json([
                        "status" => "OK",
                        "data"   => "Lote $estado."
                    ]);
                    break;
                
                default:
                    $loteIngreso=LoteIngreso::where('id',$id)->first();
                    $loteIngreso->estado=$estado;
                    $loteIngreso->save();
                    return response()->json([
                        "status" => "OK",
                        "data"   => "Lote Ingreso Actulizado."
                    ]);
                    break;
            }
        }else {
            $loteIngreso=LoteIngreso::where('id',$id)->first();
            $loteIngreso->fecha_proceso=$request->fecha_proceso;
            $loteIngreso->save();
            return response()->json([
                "status" => "OK",
                "data"   => "Lote Ingreso Actulizado."
            ]);
        }
    }

   
    public function destroy($id)
    {
        $loteIngreso=LoteIngreso::where('id',$id)->first();
        $loteIngreso->delete();
        return response()->json([
            "status" => "OK"
        ]);
    }
    
}

