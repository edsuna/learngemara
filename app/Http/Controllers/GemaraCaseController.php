<?php

namespace App\Http\Controllers;

use App\Models\GemaraCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Requests\GemaraCaseRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;

class GemaraCaseController extends Controller
{
    /**
     * Display the form for creating or updating a gemara case.
     * @param mixed $request
     * @param mixed $gemaraCase
     * @return View|Factory
     * @throws BindingResolutionException
     */
    private function showForm($request, $gemaraCase) {
        $masechtot = \App\Models\Tractate::all();

        return view('gemara_case.create')
            ->with('masechtot', $masechtot)
            ->with('inputConditions', \App\Models\GemaraCase::inputConditions)
            ->with('hideIcons', $request->query('hideicons'))
            ->with('allowUpdates', !$gemaraCase || ($gemaraCase->user_id === Auth::id()))
            ->with('theCase', $gemaraCase);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('gemara_case.index')->with('public', $request->input('public', FALSE));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return $this->showForm($request, NULL);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\GemaraCaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GemaraCaseRequest $request)
    {
        $gemaraCaseData = $request->all();
        $gemaraCaseData['user_id'] = $request->user()->id;

        $gemaraCase = GemaraCase::create($gemaraCaseData);

        if ($gemaraCase->exists) {
            return response(json_encode($gemaraCase), 201);
        }
        return response(json_encode(['errorMsg' => 'Failed to save to database']), 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GemaraCase  $gemaraCase
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, GemaraCase $gemaraCase)
    {
        return $this->showForm($request, $gemaraCase);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GemaraCase  $gemaraCase
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, GemaraCase $gemaraCase)
    {
        return $this->showForm($request, $gemaraCase);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\GemaraCaseRequest $request
     * @param  \App\Models\GemaraCase  $gemaraCase
     * @return \Illuminate\Http\Response
     */
    public function update(GemaraCaseRequest $request, GemaraCase $gemaraCase)
    {
        $gemaraCaseData = $request->all();
        $case = GemaraCase::find($gemaraCaseData['caseId']);
        if ($case->fill($gemaraCaseData)->save()) {
            return response(json_encode($gemaraCase), 200);
        }
        return response(json_encode(['errorMsg' => 'Failed to save update to database']), 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GemaraCase  $gemaraCase
     * @return \Illuminate\Http\Response
     */
    public function destroy(GemaraCase $gemaraCase)
    {
        //
    }
}
