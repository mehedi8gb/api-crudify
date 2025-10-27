<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Illuminate\Support\Str;
use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateModel extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $domainPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = $this->normalizeNamespaceToGetSingleDirectory($domainPath);
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];
        $tableName = strtolower($className); // 'user'
        $pluralTableName = $this->pluralize($tableName);
        $primaryKey = Str::camel($className) . 'Id';

        return "<?php

namespace App\Models\\{$this->namespace};

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$className} extends Model
{
use SoftDeletes, HasFactory;
    //use Sluggable;

    protected \$table = '{$pluralTableName}'; // Table name if different from model name

    protected \$primaryKey = '{$primaryKey}'; // Primary key field

    protected \$fillable = [
        'title',
        'slug',
        'status'
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
