<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Home extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $masechtot = \App\Models\Tractate::all();

        return view('welcome')->with('masechtot', $masechtot)->with('hideIcons', $request->query('hideicons'));
    }
}
