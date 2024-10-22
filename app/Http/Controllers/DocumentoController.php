<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
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
        $categorias = Categoria::all();
        return view('documento.listado')->with(compact('categorias'));
    }


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
            // dd($request->all(), "s");
            $nombre       = $request->input('nombre');
            $descripcion  = $request->input('descripcion');
            $documento_id = $request->input('documento_id');
            $categoria_id = $request->input('categoria_id');
            $usuario      = Auth::user();

            if($documento_id == "0"){
                $documento                     = new Documento();
                $documento->usuario_creador_id = $usuario->id;
            }else{
                $documento                         = Documento::find($categoria_id);
                $documento->usuario_modificador_id = $usuario->id;
            }

            if($request->has('documento')){
                // Obtén el archivo de la solicitud
                $file = $request->file('documento');

                // Define el nombre del archivo y el directorio de almacenamiento
                $originalName = $file->getClientOriginalName();
                $filename     = time() . '_'. str_replace(' ', '_', $originalName);
                $directory    = 'assets/docs';

                // Guarda el archivo en el directorio especificado
                $file->move(public_path($directory), $filename);

                // Obtén la ruta completa del archivo
                $filePath = $directory . '/' . $filename;

                $documento->documento    = $filePath;
            }

            $documento->categoria_id = $categoria_id;
            $documento->nombre       = $nombre;
            $documento->descripcion  = $descripcion;
            $documento->save();

            $data['text']   = 'Se registro con exito!';
            $data['estado'] = 'success';

        }else{
            $data['text']   = 'No existe';
            $data['estado'] = 'error';
        }
        return $data;
    }
}
