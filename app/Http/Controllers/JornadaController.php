<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use Auth;
use DB;

class JornadaController extends Controller
{
    public function listadoJornadas(){
		
    	$jornada =  App\Jornada::lista();
    	
    	return view('jornadas.listadoJornadas',compact('jornada'));

       }

   public function formJornada($id=null){
 		
        $estatus=App\Estatu::find(['4','5','6']);
        $tipo_beneficiario=App\Tipo_beneficiario::all();
        $tipoJornada=App\Tipo_jornada::all();
        $data="";
      	
	return view('jornadas.formJornadas',compact('estatus','tipoJornada','tipo_beneficiario','data'));

   }


   public function addJornadas(Request $request){

  	$id_sector = Auth::user()->id_sector;
  	
   	$nuevaJornada= new App\Jornada();
   	$request->validate([
    'asunto' => ['required', 'string'],
    'descripcion' => ['required', 'string'],
    'jornada' => ['required', 'integer'],
    'beneficiario' => ['required', 'integer'],
    'fecha_inicio' => ['required', 'string'],
   
]);
   	$nuevaJornada->asunto=$request->asunto;
   	$nuevaJornada->descripcion=$request->descripcion;
   	$nuevaJornada->fktipo_jornada=$request->jornada;
   	$nuevaJornada->fktipo_beneficiario=$request->beneficiario;
   	$nuevaJornada->fecha_inicio=$request->fecha_inicio;
   	$nuevaJornada->fecha_fin=$request->fecha_fin;
  	$nuevaJornada->tiempo_estimado=$request->tiempo;
  	$nuevaJornada->fkestatus=3;
  	$nuevaJornada->fkid_sector=$id_sector;

    $nuevaJornada->save();

   	 return redirect('/jornada');
   }

 //return view('jornadas.listadoJornadas',compact('jornada'));

   public function detalleJornadas($id){

     $jornada =  App\Jornada::buscar_id($id);     
      
     $xml='';
     $xml.='<div class="card">
  <h5 class="card-header"><b>Detalle Jornada de "'.$jornada[0]->jornadas.'"</b></h5>
  <div class="card-body">
    <h5 class="card-title">Asunto: <b>"'.$jornada[0]->asunto.'"</b></h5>
    <p class="card-text">Descripcion: '.$jornada[0]->descripcion.' Inicia:<b>'.$jornada[0]->fecha_inicio.'</b></p>
    <p class="card-text">Elaborada: '.$jornada[0]->created_at.' En el Sector: <b>'.$jornada[0]->sector.' actual mente se encuentra '.$jornada[0]->estatus.'</b></p>
     </div>
</div>';
  
      
     echo json_encode(array('xml' => $xml ));
}
public function asignarJornadas($cedula){

    $sql_persona=DB::select("SELECT * FROM personas WHERE cedula=?",[$cedula]);


    
      
     $xml='';

     if ($sql_persona[0]->cedula=="" || $sql_persona[0]->cedula==null) {

            $xml.='<h5 class="card-header">La persona para el número de cédula <b>'.$cedula.'</b> no se encuentra censada </h5>';
     
     echo json_encode(array('xml' => $xml,'tipo'=>false));
     }else{

     $xml.='<div class="card">
  <h5 class="card-header">Esta seguro que desea agregar a: </h5>
  <div class="card-body">';

     $xml.='<h5 class="card-title">Nombre: <b>'.strtoupper($sql_persona[0]->nombre) . " ".strtoupper($sql_persona[0]->apellido).'</b></h5>
    <h5 class="card-title">Cedula: <b>'.$sql_persona[0]->cedula.'</b></h5>
   ';

     $xml.=' </div>
</div>';
echo json_encode(array('xml' => $xml,'tipo'=>true));
     }


  



}







  public function eliminarJornadas($id){

    $jornada= App\Jornada::find($id);

    $jornada->delete();

        return redirect('/jornada');

  }

public function editarJornadasform($id){

$data =  App\Jornada::buscar_id($id);
if ($data[0]->fkestatus==4) {
  $estatus=App\Estatu::find(['5','6']);
}else{
  $estatus=App\Estatu::find(['4','5','6']);
}


$tipo_beneficiario=App\Tipo_beneficiario::all();
$tipoJornada=App\Tipo_jornada::all();   

  return view('jornadas.formJornadas',compact('estatus','tipoJornada','tipo_beneficiario','data'));
}


public function editarJornadas(Request $request,$id){

    $nuevaJornada =  App\Jornada::find($id);
    $nuevaJornada->id=$id;
    //$nuevaJornada->asunto=$request->asunto;
    //$nuevaJornada->descripcion=$request->descripcion;
    //$nuevaJornada->fktipo_jornada=$request->jornada;
    //$nuevaJornada->fktipo_beneficiario=$request->beneficiario;
    //$nuevaJornada->fecha_inicio=$request->fecha_inicio;
    //$nuevaJornada->fecha_fin=$request->fecha_fin;
    $nuevaJornada->tiempo_estimado=$request->tiempo;
    $nuevaJornada->fkestatus=$request->estatus;
  

    $nuevaJornada->save();
    return redirect('/jornada');
}


public function addjornadahistorico(Request $request,$id){

  $jornada= App\Jornada::find($id);
  $historico= App\JornadaHistorico::validarbeneficio($request->cedula,$id);
   $personas = DB::select('SELECT COUNT(*) as cantidad FROM personas WHERE cedula=?',[$request->cedula]);


if ($personas[0]->cantidad>0) {
  
  if ($historico[0]->cantidad==0) { 

if ($request->cedula=="" || $request->cedula==null) {

return back()->with('validator','Debe ingresar una cédula para continuar');        

}
$jornadaHistorico= new App\JornadaHistorico();
$jornadaHistorico->fkidjornada=$jornada->id;
$jornadaHistorico->cedula=$request->cedula;
$jornadaHistorico->asunto=$jornada->asunto;
$jornadaHistorico->descripcion=$jornada->descripcion;
$jornadaHistorico->fktipo_jornada=$jornada->fktipo_jornada;
$jornadaHistorico->fktipo_beneficiario=$jornada->fktipo_beneficiario;
$jornadaHistorico->fecha_inicio=$jornada->fecha_inicio;
$jornadaHistorico->fecha_fin=$jornada->fecha_fin;
$jornadaHistorico->fkestatus=$jornada->fkestatus;
$jornadaHistorico->fkid_sector=$jornada->fkid_sector;

$jornadaHistorico->save();


return back()->with('msg', 'La persona ha sido registrada Exitosamente');
  }else{

return back()->with('error', 'Esta persona ya ha sido registrada para este beneficio.');

  }

}else{
  return back()->with('error', 'Esta persona no se encuentra registrada como beneficio.');
}



  
}



}