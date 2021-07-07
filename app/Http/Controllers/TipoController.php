<?php

namespace App\Http\Controllers;
use App\Http\Requests\TipoValidate;
use App\Model\Tipo;
use Illuminate\Http\Request;

class TipoController extends Controller
{
   
    public function index(Request $request)
    {
        if ($request->has('all')) {
            $tipo=Tipo::all();
        }else {
            $tipo=Tipo::select('Tipo.*','nombre_materia')
                                    ->join('materia','materia.id','=','Tipo.materia_id')
                                    ->paginate(10);
        }
        return response()->json($tipo);
    }

    public function store(TipoValidate $request)
    {
        $tipo=new Tipo();
        $tipo->nombre_Tipo=$request->nombre_Tipo;
        $tipo->materia_id=$request->materia_id;
        $tipo->save();

        return response()->json([
            "status" => "OK"
        ]);

    }

    public function show($id)
    {
        $tipo=Tipo::where('id',$id)->first();
        return response()->json($tipo);
    }


    
    public function update(TipoValidate $request, $id)
    {
        $tipo=Tipo::where('id',$id)->first();
        $tipo->nombre_Tipo=$request->nombre_Tipo;
        $tipo->materia_id=$request->materia_id;
        $tipo->save();

        return response()->json([
            "status" => "OK"
        ]);
    }

    
    public function destroy($id)
    {
        $tipo=Tipo::where('id',$id)->first();
        $tipo->delete();
        return response()->json([
            "status" => "OK"
        ]);
    }
}
