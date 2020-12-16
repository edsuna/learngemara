<?php

namespace App\Http\Controllers;

use App\Models\GemaraCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Requests\GemaraCaseRequest;

class GemaraCaseController extends Controller
{
    /**
     * Return the data for creating or updating a GemaraCase retrieved from the request
     *
     * @param array $requestData
     * @return array
     */
    private function getDataFromRequest($requestData) {
        $data = [
            'masechet' => $requestData['masechet'],
            'daf' => $requestData['daf'],
            'gemara_text' => $requestData['gemara_text'],
            'title' => $requestData['title'],
            'din_type' => $requestData['din_type'],
            'act' => $requestData['act'],
            'public' => $requestData['public'],
        ];

        foreach(GemaraCase::inputConditions as $inputCondition) {
            $data[$inputCondition] = $requestData[$inputCondition];
            $data[$inputCondition . '_nr'] = $requestData[$inputCondition . '_nr'];
        }

        return $data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $masechtot = \App\Models\Tractate::all();

        return view('gemara_case.create')
            ->with('masechtot', $masechtot)
            ->with('inputConditions', \App\Models\GemaraCase::inputConditions)
            ->with('hideIcons', $request->query('hideicons'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GemaraCaseRequest $request)
    {
        $validation = [
            'masechet' => ['required', 'exists:tractates,english_name'],
            'daf' => ['required'],
            'gemara_text' => ['required'],
            'title' => 'nullable',
            'din_type' => ['required', Rule::in(GemaraCase::dinTypes)],
            'act' => ['required'],
            'public' => ['required'],
        ];

        foreach (GemaraCase::inputConditions as $inputCondition) {
            $validation[$inputCondition] = ["required_unless:{$inputCondition}_nr,1"];
            $validation[$inputCondition . '_nr'] = ["required_without:$inputCondition"];
        }

        $request->validate($validation);

        $gemaraCaseData = $this->getDataFromRequest($request->all());
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
    public function show(GemaraCase $gemaraCase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GemaraCase  $gemaraCase
     * @return \Illuminate\Http\Response
     */
    public function edit(GemaraCase $gemaraCase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GemaraCase  $gemaraCase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GemaraCase $gemaraCase)
    {
        //
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
