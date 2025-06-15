<?php

namespace Modules\User\App\Transformers\Api\UserFile;

use Illuminate\Http\Resources\Json\JsonResource;

class UserFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->file_url,
        ];
    }
}
