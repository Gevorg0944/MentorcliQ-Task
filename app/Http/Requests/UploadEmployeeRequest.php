<?php

namespace App\Http\Requests;

use App\Rules\Uploads\CheckEmployeeImportFileRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => ['bail', 'required', 'mimes:csv,txt', 'max:8000', new CheckEmployeeImportFileRule()]
        ];
    }
}
