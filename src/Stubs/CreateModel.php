<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateModel
{

    private string $modelBinding;
    private string $modelBindingLower;

    public function __construct(array $modelBinding)
    {
        $this->modelBinding = $modelBinding['className'];
        $this->modelBindingLower = strtolower($modelBinding['className']) . 's';
    }

    public function generate(): string
    {
        return "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class {$this->modelBinding} extends Model
{
use SoftDeletes, HasFactory;
//use Sluggable;

    protected \$table = '{$this->modelBindingLower}'; // Table name if different from model name

    protected \$primaryKey = 'id'; // Primary key field

    protected \$fillable = [
        'title',
        'slug',
        // Add other attributes that can be mass-assigned here
    ];

    protected \$casts = [
        // Add other attribute casting here
    ];

//    public function sluggable(): array
//    {
//        return [
//            'slug' => [
//                'source' => ['title', 'id'], // Generate slug from 'title' and 'id' attributes
//                'onUpdate' => true,          // Regenerate slug when the title is updated
//            ],
//        ];
//    }
}
        ";
    }
}
