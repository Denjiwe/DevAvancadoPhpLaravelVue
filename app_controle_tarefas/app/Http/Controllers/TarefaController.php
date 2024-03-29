<?php

namespace App\Http\Controllers;

use App\Exports\TarefasExport;
use App\Mail\NovaTarefaMail;
use App\Models\Tarefa;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\PDF;

class TarefaController extends Controller
{
    public function validaCampos($request) 
    {
        $regras = 
        [
            'tarefa' => 'required',
            'data_limite_conclusao' => 'required'
        ];

        $feedback = 
        [
            'tarefa.required' => 'Informe a tarefa a ser realizada',
            'data_limite_conclusao.required' => 'Informe a data limite para a conclusão da tarefa'
        ];

        $request->validate($regras, $feedback);
    }

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = auth()->user()->id;
        $tarefas = Tarefa::where('user_id', $user_id)->paginate(10);
        return view('tarefa.index', ['tarefas' => $tarefas]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tarefa.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validaCampos($request);
        $dados = $request->all('tarefa', 'data_limite_conclusao');
        $dados['user_id'] = auth()->user()->id;        
        $tarefa = Tarefa::create($dados);

        $email = auth()->user()->email;
        Mail::to($email)->send(new NovaTarefaMail($tarefa));

        return redirect()->route('tarefa.show', ['tarefa' => $tarefa->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function show(Tarefa $tarefa)
    {
        return view('tarefa.show', ['tarefa' => $tarefa]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(Tarefa $tarefa)
    {
        if(!$tarefa->user_id == auth()->user()->id) 
        {
            return view('acesso-negado');
        }

        return view('tarefa.edit', ['tarefa' => $tarefa]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tarefa $tarefa)
    {
        if(!$tarefa->user_id == auth()->user()->id) 
        {
            return view('acesso-negado');
        }

        $this->validaCampos($request);

        $tarefa->update($request->all());
        return view('tarefa.show', ['tarefa' => $tarefa]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tarefa  $tarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tarefa $tarefa)
    {
        if(!$tarefa->user_id == auth()->user()->id) 
        {
            return view('acesso-negado');
        }

        $tarefa->delete();
        
        return redirect()->route('tarefa.index');
    }

    public function exportacao($extensao) 
    {
        $nomeArquivo = 'lista_de_tarefas';

        if(in_array($extensao, ['xlsx', 'csv'])){
            return Excel::download(new TarefasExport, $nomeArquivo .= '.'.$extensao);
        } else {
            return redirect()->route('tarefa.index');
        }
    
    }

    public function exportarPdf() 
    {
        $tarefas = auth()->user()->tarefas()->get();
        $pdf = Pdf::loadView('tarefa.pdf', ['tarefas' => $tarefas]);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('lista_de_tarefas.pdf');
    }
}
