<?php

namespace Modules\Admin\App\Transformers\Role;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'    => $this->description,
            'permission_type'    => $this->permission_type,
            'permissions' => $this->permissions,
        ];
    }
}
