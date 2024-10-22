<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function listado(Request $request)
    {
        return view('documento.listado');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function ajaxListado(Request $request)
    {
        if($request->ajax()){
            $data['estado'] = 'success';
            $data['listado'] = $this->listadoArrayCategoria();
        }else{
            $data['text']   = 'No existe';
            $data['estado'] = 'error';
        }
        return $data;
    }

    private function listadoArrayCategoria(){
        $documentos = Documento::all();
        return view('documento.ajaxListado')->with(compact('documentos'))->render();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function agregarDocumento(Request $request)
    {
        if($request->ajax()){
            // dd($request->all());
            $nombre       = $request->input('nombre');
            $descripcion  = $request->input('descripcion');
            $documento_id = $request->input('documento_id');
            $categoria_id = $request->input('categoria_id');
            $usuario      = Auth::user();

            if($categoria_id == "0"){
                $categoria                     = new Documento();
                $categoria->usuario_creador_id = $usuario->id;
            }else{
                $categoria                         = Documento::find($categoria_id);
                $categoria->usuario_modificador_id = $usuario->id;
            }

            $categoria->categoria_id = $categoria_id;
            $categoria->nombre       = $nombre;
            $categoria->documento    = "";
            $categoria->descripcion  = $descripcion;
            $categoria->save();

            $data['text']   = 'Se registro con exito!';
            $data['estado'] = 'success';

        }else{
            $data['text']   = 'No existe';
            $data['estado'] = 'error';
        }
        return $data;
    }
}
