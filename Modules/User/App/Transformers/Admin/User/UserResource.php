<?php

namespace Modules\User\App\Transformers\Admin\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Interest\App\Transformers\Admin\Interest\InterestResource;
use Modules\Language\App\Transformers\Admin\Language\LanguageResource;
use Modules\User\App\Transformers\Admin\UserProfile\UserProfileResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone_code' => $this->whenLoaded('phone', fn() => $this->phone->phone_code),
            'phone' => $this->whenLoaded('phone', fn() => $this->phone->phone),
            'name' => $this->name,
            "image_url" => $this->image_url,
            'blocked' => $this->blocked,
            'status' => $this->status,
            'active' => $this->active,
            'banned_at' => $this->bans->first()?->created_at, // Date when the user was banned
            'expired_at' => $this->bans->first()?->expired_at, // Expiration of the ban
            'ban_comment' => $this->bans->first()?->comment, // Ban reason/comment
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'verified_at' => $this->verified_at,
            'profile' => new UserProfileResource(resource: $this->profile),
           
        ];
    }
}