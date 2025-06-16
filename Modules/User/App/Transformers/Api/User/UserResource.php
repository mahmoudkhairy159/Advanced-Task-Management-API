<?php

namespace Modules\User\App\Transformers\Api\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Interest\App\Transformers\Api\Interest\InterestResource;
use Modules\Language\App\Transformers\Api\Language\LanguageResource;
use Modules\User\App\Transformers\Api\UserProfile\UserProfileResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'slug' => $this->slug,
            'email'         => $this->email,
            'phone_code'    => $this->whenLoaded('phone', fn() => $this->phone->phone_code),
            'phone'         => $this->whenLoaded('phone', fn() => $this->phone->phone),
            'name'          => $this->name,
            "image_url" => $this->image_url,

            'status'        => $this->status,
            'active'        => $this->active,
            'blocked'        => $this->blocked,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'verified_at' => $this->verified_at,
            'profile' => new UserProfileResource($this->profile),

        ];
    }
}