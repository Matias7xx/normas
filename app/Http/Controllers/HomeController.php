<?php

namespace App\Http\Controllers;

use App\Models\Rh\Servidor;
use App\Models\Rh\Cargo;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
  public function home()
  {
    $servidor = Servidor::where('matricula', Auth::user()->matricula)->first();
    $cargo = Cargo::where('codigo', $servidor->cargo)->first();
    return view('home', compact(['servidor']));
  }
}
