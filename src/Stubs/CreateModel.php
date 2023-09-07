<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateModel
{

    private array $modelBinding;

    public function __construct(array $modelBinding)
    {
        $this->modelBinding = $modelBinding;
    }

    public function generate(): string
    {
        return "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$this->modelBinding['className']} extends Model
{
    use SoftDeletes, HasFactory;

    protected \$table = '{$this->modelBinding['className']}'; // Table name if different from model name

    protected \$primaryKey = 'id'; // Primary key field

    protected \$fillable = [
        'name',
        'description',
        // Add other attributes that can be mass-assigned here
    ];

    protected \$guarded = [
        // 'admin_only_field', // Add attributes that should not be mass-assigned here
    ];

    protected \$dates = [
        'created_at',
        'updated_at',
        // Add other date fields here
    ];

    protected \$casts = [
        // 'price' => 'decimal:2', // Cast 'price' attribute to a decimal with 2 decimal places
        // 'is_active' => 'boolean', // Cast 'is_active' attribute to boolean
        // Add other attribute casting here
    ];
}
        ";
    }
}
