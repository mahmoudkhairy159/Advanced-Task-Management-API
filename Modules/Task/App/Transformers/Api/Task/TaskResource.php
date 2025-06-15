<?php

namespace Modules\Task\App\Transformers\Api\Task;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assignable_id' => $this->assignable_id,
            'assignable_type' => $this->assignable_type,
            'creator_id' => $this->creator_id,
            'creator_type' => $this->creator_type,
            'updater_id' => $this->updater_id,
            'updater_type' => $this->updater_type,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'assignable' => $this->whenLoaded('assignable', function ($assignable) {
                return [
                    'id' => $assignable->id,
                    'name' => $assignable->name,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function ($creator) {
                return [
                    'id' => $creator->id,
                    'name' => $creator->name,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function ($updater) {
                return [
                    'id' => $updater->id,
                    'name' => $updater->name,
                ];
            }),

        ];
    }
}