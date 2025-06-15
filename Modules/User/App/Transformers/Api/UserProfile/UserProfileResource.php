<?php

namespace Modules\User\App\Transformers\Api\UserProfile;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'bio' => $this->bio,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'language' => $this->language,
            'mode' => $this->mode,
            'sound_effects' => $this->sound_effects,
            'allow_related_notifications' => $this->allow_related_notifications,
            'send_email_notifications' => $this->send_email_notifications,
           
        ];
    }
}