<?php

namespace Modules\User\App\Http\Requests\Api\UserProfile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Admin\App\Models\Role;

class StoreUserProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'image'=>['nullable','image','mimes:jpeg,png,jpg,gif', 'max:5000'],
            'bio'=>['nullable','string', 'min:3', 'max:256'],
            'language'=>['required',Rule::in(core()->getSupportedLocales())],
            'mode'=>['required',Rule::in(['dark','light','device_mode'])],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'message' => 'Validation Error',
            'statusCode'=>422
        ], 422));
    }
}