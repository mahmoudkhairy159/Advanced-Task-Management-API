<?php

namespace Modules\Task\App\Http\Requests\Api\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\User\App\Models\User;

class UpdateTaskRequest extends FormRequest
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
            'priority' => ['required', Rule::in(TaskPriorityEnum::getConstants())],
            'status' => [
                'nullable',
                Rule::in(TaskStatusEnum::getConstants()),
                function ($attribute, $value, $fail) {
                    // Only validate if trying to set status to completed
                    if ($value === TaskStatusEnum::STATUS_COMPLETED) {
                        // Get the current task status from database
                        $task = $this->route('task');
                        if ($task && $task->status !== TaskStatusEnum::STATUS_IN_PROGRESS) {
                            $fail('Task status can only be set to completed when it is currently in progress.');
                        }
                    }
                }
            ],
            'assignable_id' => ['required', 'exists:users,id'],
            'assignable_type' => ['required', 'string', 'in:' . User::class],
            'creator_id' => ['required', 'exists:users,id'],
            'creator_type' => ['required', 'string', 'in:' . User::class],
            'updater_id' => ['required', 'exists:users,id'],
            'updater_type' => ['required', 'string', 'in:' . User::class],
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'assignable_type' => User::class,
            'updater_type' => User::class,
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