<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateMigration extends BaseStub
{
    private mixed $modelBinding;

    /**
     * @param array $modelBinding
     */
    public function __construct(array $modelBinding)
    {
        $this->modelBinding = strtolower($modelBinding['className']);
    }

    public function generate(): string
    {
        return "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$this->modelBinding}', function (Blueprint \$table) {
            \$table->id('{$this->modelBinding}Id');
            \$table->string('title');
            \$table->string('slug')->unique();
            \$table->boolean('status')->default(true);
            \$table->timestamp('createdAt')->nullable();
            \$table->timestamp('updatedAt')->nullable();
            \$table->timestamp('deletedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
        ";
    }
}
