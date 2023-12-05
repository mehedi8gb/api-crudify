<?php
namespace App\Http\Resources\Test;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TestResourceCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => TestResource::collection($this->collection),
        ];
    }
}
        