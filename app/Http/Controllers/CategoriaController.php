<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{

    public function listado(Request $request){
        return view('categoria.listado');
    }

    public function ajaxListado(Request $request){
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
        $categorias = Categoria::all();
        return view('categoria.ajaxListado')->with(compact('categorias'))->render();
    }

    public function agregarCategoria(Request $request){
        if($request->ajax()){
            // dd($request->all());
            $nombre       = $request->input('nombre');
            $descripcion  = $request->input('descripcion');
            $categoria_id = $request->input('categoria_id');
            $usuario      = Auth::user();

            if($categoria_id == "0"){
                $categoria                     = new Categoria();
                $categoria->usuario_creador_id = $usuario->id;
            }else{
                $categoria                         = Categoria::find($categoria_id);
                $categoria->usuario_modificador_id = $usuario->id;
            }

            $categoria->nombre      = $nombre;
            $categoria->descripcion = $descripcion;
            $categoria->save();

            $data['text']   = 'Se registro con exito!';
            $data['estado'] = 'success';

        }else{
            $data['text']   = 'No existe';
            $data['estado'] = 'error';
        }
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        //
    }
}
