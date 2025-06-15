<?php

namespace Modules\Admin\App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:256',],
            'email' => ['required', 'email',  'unique:admins,email'],
            'phone' => ['required','alpha_num','between:11,13' ,'unique:admins,phone'],
            'status' => ['required', 'in:1,0'],
            'blocked' => ['required', 'in:1,0'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required','string','min:3','max:256','confirmed']
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'message' => 'Validation Error',
            'statusCode'=>422
        ], 422));
    }
}
