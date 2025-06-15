<?php

namespace Modules\Admin\App\Transformers\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Admin\App\Transformers\Role\RoleResource;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'email'         => $this->email,
            'phone'    => $this->phone,
            'name'          => $this->name,
            'status'        => $this->status,
            'blocked'        => $this->blocked,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            "image_url" => $this->image_url,
            "role" => new RoleResource($this->role),
            'permissions' => $this->permissions()
        ];
    }
}
