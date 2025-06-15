<?php

namespace Modules\UserNotification\App\Transformers\Api\UserNotification;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'read_at' => $this->read_at ? $this->read_at: null,

        ];
    }
}
