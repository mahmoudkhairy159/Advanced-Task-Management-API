<?php

namespace Modules\Task\App\Http\Requests\Admin\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Admin\App\Models\Admin;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\User\App\Models\User;

class StoreTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
            'priority' => ['nullable', Rule::in(TaskPriorityEnum::getConstants())],
            'assignable_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->input('assignable_type');
                    $exists = match($type) {
                        User::class => User::where('id', $value)->exists(),
                        Admin::class => Admin::where('id', $value)->exists(),
                        default => false
                    };

                    if (!$exists) {
                        $fail('The selected assignable id does not exist.');
                    }
                }
            ],
            'assignable_type' => ['required', 'string', Rule::in([User::class, Admin::class])],
            'creator_id' => ['required', 'exists:admins,id'],
            'creator_type' => ['required', 'string', Rule::in([Admin::class])],
            'updater_id' => ['required', 'exists:admins,id'],
            'updater_type' => ['required', 'string', Rule::in([Admin::class])],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'assignable_type' => $this->input('assignable_type', User::class),
            'creator_type' => Admin::class,
            'updater_type' => Admin::class,
            'creator_id' => Auth::id(),
            'updater_id' => Auth::id(),
        ]);
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
            'statusCode' => 422
        ], 422));
    }
}