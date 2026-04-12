<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\GemaraCase;
use Illuminate\Support\Facades\Auth;

class GemaraCaseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [];

        if (isset($this->gemaraText)) {
            $data['gemara_text'] = $this->gemaraText;
        }
        if (isset($this->dinType)) {
            $data['din_type'] = $this->dinType;
        }

        if (isset($this->inputConditions)) {
            foreach ($this->inputConditions as $conditionName => $conditionValue) {
                $data[$conditionName] = $conditionValue['value'];
                $data[$conditionName . '_nr'] = $conditionValue['notRelevant'];
            }
        }

        $this->merge($data);
    }

    public function rules(): array
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
            $validation[$inputCondition] = ["required_unless:{$inputCondition}_nr,true"];
            $validation[$inputCondition . '_nr'] = ["required_without:$inputCondition"];
        }

        return $validation;
    }

    public function authorize(): bool
    {
        if (isset($this->caseId) && $this->caseId) {
            $case = GemaraCase::find($this->caseId);
            if (!$case || ($case->user_id != Auth::id())) {
                return false;
            }
        }

        return true;
    }
}
