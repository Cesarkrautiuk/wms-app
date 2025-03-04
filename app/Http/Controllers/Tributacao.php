<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Tributacao extends Controller
{
   public function Index(){
       return view('tributacao');
   }
}
