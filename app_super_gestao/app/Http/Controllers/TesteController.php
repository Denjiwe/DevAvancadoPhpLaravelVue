<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function teste($p1, $p2) {
        // echo "$p1 x $p2 é igual a: ".($p1*$p2);

        // return view('site.teste', ['p1' => $p1, 'p2' => $p2]); // array associativo

        return view('site.teste', compact('p1', 'p2')); // compact, mais utilizado

        // return view('site.teste')->with('p1', $p1)->with('p2', $p2); // with
    }
}
