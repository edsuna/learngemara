<?php

namespace App\Http\Controllers;

use App\Models\GemaraCase;
use Illuminate\Http\Request;
use App\Http\Requests\GemaraCaseRequest;
use Illuminate\Support\Facades\Auth;

class GemaraCaseController extends Controller
{
    private function showForm($request, $gemaraCase)
    {
        $masechtot = \App\Models\Tractate::all();

        return view('gemara_case.create')
            ->with('masechtot', $masechtot)
            ->with('inputConditions', GemaraCase::inputConditions)
            ->with('hideIcons', $request->query('hideicons'))
            ->with('allowUpdates', !$gemaraCase || ($gemaraCase->user_id === Auth::id()))
            ->with('theCase', $gemaraCase);
    }

    public function index(Request $request)
    {
        return view('gemara_case.index')->with('public', $request->input('public', false));
    }

    public function create(Request $request)
    {
        return $this->showForm($request, null);
    }

    public function store(GemaraCaseRequest $request)
    {
        $gemaraCaseData = $request->all();
        $gemaraCaseData['user_id'] = $request->user()->id;

        $gemaraCase = GemaraCase::create($gemaraCaseData);

        if ($gemaraCase->exists) {
            return response()->json($gemaraCase, 201);
        }
        return response()->json(['errorMsg' => 'Failed to save to database'], 500);
    }

    public function show(Request $request, GemaraCase $gemaraCase)
    {
        return $this->showForm($request, $gemaraCase);
    }

    public function edit(Request $request, GemaraCase $gemaraCase)
    {
        return $this->showForm($request, $gemaraCase);
    }

    public function update(GemaraCaseRequest $request, GemaraCase $gemaraCase)
    {
        $gemaraCaseData = $request->all();
        $case = GemaraCase::find($gemaraCaseData['caseId']);
        if ($case->fill($gemaraCaseData)->save()) {
            return response()->json($gemaraCase, 200);
        }
        return response()->json(['errorMsg' => 'Failed to save update to database'], 500);
    }

    public function destroy(GemaraCase $gemaraCase)
    {
        //
    }
}
