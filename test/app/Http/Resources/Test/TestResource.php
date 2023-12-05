<?php
namespace App\Http\Resources\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class TestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $id = $this->id;
        return [
            'title' => $this->title,
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y'),
            'links' => [
                'show' => $this->unless(Route::currentRouteName() === 'test.show', function () {
                    return route('test.show', [
                        'slug' => $this->slug
                    ]);
                }),
                'update' => route('test.update', $id),
                'delete' => route('test.destroy', $id),
            ]
        ];
    }
}
        