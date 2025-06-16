<?php

namespace Modules\User\App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\User\App\Enums\UserTypeEnum;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $supportedLocales = array_keys(core()->getSupportedLocales());

        return [
            //user data validation
            'name' => ['required', 'string', 'min:3', 'max:256',],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $this->validatePhoneCodeAndPhoneUnique($attribute, $value, $fail);
                },
            ],
            'phone_code' => ['required_with:phone', 'string', 'min:1', 'max:3'],
            'status' => ['required', 'in:1,0'],
            'blocked' => ['required', 'in:1,0'],
            'password' => ['required', 'string', 'min:3', 'max:256', 'confirmed'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
            //End user data validation

            //userProfile
            'bio' => ['nullable', 'string', 'min:3', 'max:256'],
            'language' => ['nullable', Rule::in($supportedLocales)],
            'mode' => ['nullable', Rule::in(['dark', 'light'])],
            'sound_effects' => ['required', Rule::in(['on', 'off'])],
            'allow_related_notifications' => ['required', Rule::in(['on', 'off'])],
            'send_email_notifications' => ['required', Rule::in(['on', 'off'])],
            'gender' => ['nullable', 'string', Rule::in(['Male', 'Female', 'Non-binary', 'Prefer not to say'])],
            'birth_date' => ['nullable', 'date'],
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
     * Custom validation rule for ensuring phone_code and phone combination is unique.
     */
    public function validatePhoneCodeAndPhoneUnique($attribute, $value, $fail)
    {
        if ($this->phone_code && $value) {
            $exists = DB::table('user_phones')
                ->where('phone', $value)
                ->where('phone_code', $this->phone_code)
                ->exists();

            if ($exists) {
                $fail('The combination of phone and phone_code is already taken.');
            }
        }
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
            'statusCode' => 422
        ], 422));
    }
}