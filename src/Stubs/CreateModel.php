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

    protected \$fillable = [
        'title',
        'description'
    ];
}
        ";
    }
}
