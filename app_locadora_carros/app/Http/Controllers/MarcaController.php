<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Repositories\MarcaRepository;

class MarcaController extends Controller
{
    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')) {
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;

            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        } else {
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }

        if($request->has('filtro')) {
            $marcaRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $marcaRepository->selectAtributos($request->atributos);
        }
        
        return response()->json($marcaRepository->getResultadoComPaginacao(3), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //$marca = Marca::create($request->all());

        // dd($request->imagem);
        // dd($request->file('imagem'));
        // algumas maneiras de recuperar os arquivos de imagem

        $request->validate($this->marca->rules(), $this->marca->feedback());

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  integer id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);

        if($marca === null) {
            return response()->json(['erro'=>'Não foi possível exibir o registro pois o mesmo não foi encontrado'], 404);
        }

        return response()->json($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $marca->update($request->all());

        $marca = $this->marca->with('modelos')->find($id);

        if($marca === null) {
            return response()->json(['erro'=>'Não foi possível fazer a atualização pois o registro não foi encontrado'], 404);
        }

        if($request->method() == 'PATCH') {
            $regrasDinamicas = array();

            // percorrendo todas as regras definidas na model
            foreach ($marca->rules() as $input => $regras) {

                // coletando apenas as regras encontradas na requisição
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regras;
                }

            }

            $request->validate($regrasDinamicas, $marca->feedback());
        } else {

            $request->validate($marca->rules(), $marca->feedback());
        }

        $marca->fill($request->all());

        // verifica se a imagem está na requisição, pois como há o patch, não é obrigatório passar outra imagem
        if ($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);

            $imagem = $request->file('imagem');
            $imagem_urn = $imagem->store('imagens', 'public');
            $marca->imagem = $imagem_urn;
        }

        $marca->save();
        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $marca->delete();

        // $this->marca->find($id)->delete();
        $marca = $this->marca->find($id);

        if($marca === null) {
            return response()->json(['erro'=>'Não foi possível fazer a exclusão pois o registro não foi encontrado'], 404);
        }

        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();
        return response()->json(['msg' => 'Registro excluído!'], 200);
    }
}
