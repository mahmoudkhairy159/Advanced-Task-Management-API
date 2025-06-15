<?php

namespace Modules\User\App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthUpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'min:3', 'max:256'],
            'email'       => ['required', 'email', 'unique:users,email,' . Auth::id()],
            'phone'       => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $this->validatePhoneCodeAndPhoneUnique($attribute, $value, $fail);
                },

            ],
            'phone_code'  => ['required_with:phone', 'string', 'min:1', 'max:3'],
            'password'    => ['nullable', 'confirmed', 'min:6'],

        ];
    }

    /**
     * Custom validation rule to check uniqueness of phone and phone_code combination.
     */
    public function validatePhoneCodeAndPhoneUnique($attribute, $value, $fail)
    {
        if ($this->phone_code && $value) {
            $exists = DB::table('user_phones')
                ->where('phone', $value)
                ->where('phone_code', $this->phone_code)
                ->where('user_id', '!=', Auth::id()) // Exclude the current user's record
                ->exists();

            if ($exists) {
                $fail('The combination of phone and phone_code is already taken.');
            }
        }
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
            'errors'    => $validator->errors(),
            'message'   => 'Validation Error',
            'statusCode'=> 422
        ], 422));
    }
}