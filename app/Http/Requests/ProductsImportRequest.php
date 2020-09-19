<?php

namespace App\Http\Requests;

use App\Rules\MaxFileSize;
use Illuminate\Foundation\Http\FormRequest;

class ProductsImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xls,xlsx', new MaxFileSize()]
        ];
    }


    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => "Ви не вибрали файл",
            'file'     => "Передане значення повинне бути файлом",
            'mimes'    => "Приймаються лише Excel файли",
        ];
    }
}
