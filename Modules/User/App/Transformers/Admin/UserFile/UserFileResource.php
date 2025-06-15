<?php

namespace Modules\User\App\Transformers\Admin\UserFile;

use Illuminate\Http\Resources\Json\JsonResource;

class UserFileResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->file_url,
        ];
    }
}
