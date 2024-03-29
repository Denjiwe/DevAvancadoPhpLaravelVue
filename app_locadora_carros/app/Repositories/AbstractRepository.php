<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class AbstractRepository {
    public function __construct(Model $model) {
        $this->model = $model;
    }

    public function selectAtributosRegistrosRelacionados($atributos) {
        $this->model = $this->model->with($atributos);
        // montagem da query
    }

    public function filtro($filtros) {
        $filtros = explode(';', $filtros);

        foreach($filtros as $key => $condicao) {

            $c = explode(':', $condicao);
            $this->model = $this->model->where($c[0], $c[1], $c[2]);
            // c[0] = coluna, c[1] = operador lógico, c[2] = valor
            // montagem da query
        }
    }

    public function selectAtributos($atributos) {
        $this->model = $this->model->selectRaw($atributos);
    }

    public function getResultado() {
        return $this->model->get();
    }

    public function getResultadoComPaginacao($numerDeRegistrosPorPagina) {
        return $this->model->paginate($numerDeRegistrosPorPagina);
    }
}

?>