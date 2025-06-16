<?php

namespace Modules\User\App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\User\App\Enums\UserTypeEnum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:256',],
            'email' => ['required', 'email',  'unique:users,email,' . $this->route('user')],
            'phone'       => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $this->validatePhoneCodeAndPhoneUnique($attribute, $value, $fail);
                },
            ],
            'phone_code'  => ['required_with:phone', 'string', 'min:1', 'max:3'],
            'status' => ['required', 'in:1,0'],
            'blocked' => ['required', 'in:1,0'],
            'password' => ['nullable', 'string', 'min:3', 'max:256', 'confirmed'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5000'],
            //userProfile
            'bio' => ['nullable', 'string', 'min:3', 'max:256'],
            'language' => ['nullable', Rule::in(array_keys(core()->getSupportedLocales()))],
            'mode' => ['nullable', Rule::in(['dark', 'light', 'device_mode'])],
            'sound_effects' => ['nullable', Rule::in(['on', 'off'])],
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
     * Custom validation rule for phone_code + phone uniqueness.
     */
    public function validatePhoneCodeAndPhoneUnique($attribute, $value, $fail)
    {
        if ($this->phone_code && $value) {
            $exists = DB::table('user_phones')
                ->where('phone', $value)
                ->where('phone_code', $this->phone_code)
                ->where('user_id', '!=', $this->route('user')) // Exclude current user's record
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