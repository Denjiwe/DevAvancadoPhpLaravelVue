<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    public function rules() {
        return [
            'nome' => 'required|min:3|max:30'
        ];
    }

    public function locacoes() {
        return $this->hasMany('App\Models\Locacao');
    }
}
