<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateMigration
{
    private mixed $modelBinding;

    /**
     * @param array $modelBinding
     */
    public function __construct(array $modelBinding)
    {
        $this->modelBinding = strtolower($modelBinding['className']);
    }

    public function generate()
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
            \$table->id();
            \$table->string('title');
            \$table->string('description');
            \$table->softDeletes();
            \$table->timestamps();
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
