<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateController
{
    private array $modelBinding;
    private mixed $controllerPath;

    public function __construct(array $modelBinding, string $controllerPath)
    {
        $this->modelBinding = $modelBinding;
        if ($controllerPath === '') {
            $this->controllerPath = '';
        } else {
            $this->controllerPath = '\\' . str_replace('/', '\\', $controllerPath);
        }
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];
        $classVar = $this->modelBinding['classVar'];
        $classNameLower = strtolower($className);
        $classNamePlural = $this->pluralize($classNameLower);
        $classNamePluralTitle = ucfirst($classNamePlural);

        return "<?php

namespace App\Http\Controllers\V1$this->controllerPath;

use App\Http\Requests\\{$className}\\{$className}StoreRequest;
use App\Http\Requests\\{$className}\\{$className}UpdateRequest;
use App\Http\Resources\\{$className}\\{$className}Resource;
use App\Models\\{$className};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class {$className}Controller extends Controller
{
    // Display a listing of the resource.
    /**
     * @throws Exception
     */
    public function index(Request \$request)
    {
        \$query = {$className}::query();
        \$result = handleApiRequest(\$request, \$query);

        return sendSuccessResponse('{$classNamePluralTitle} retrieved successfully', \$result, Response::HTTP_OK);
    }

    // Store a newly created resource in storage.
    public function store({$className}StoreRequest \$request)
    {
        \$data = {$className}::create(\$request->validated());

        return sendSuccessResponse(
            \"{$className} inserted successfully\",
            {$className}Resource::make(\$data),
            Response::HTTP_CREATED
        );
    }

    // Display the specified resource.
    public function show(\$slug)
    {
        {$classVar} = {$className}::where('slug', \$slug)->firstOrFail();

        return sendSuccessResponse(
            \"{$className} retrieved successfully\",
            {$className}Resource::make({$classVar}),
            Response::HTTP_OK
        );
    }

    // Update the specified resource in storage.
    public function update({$className}UpdateRequest \$request, {$className} {$classVar})
    {
        {$classVar}->update(\$request->validated());
        return sendSuccessResponse(
            '{$className} updated successfully',
            {$className}Resource::make({$classVar}),
            Response::HTTP_ACCEPTED
        );
    }

    // Remove the specified resource from storage.
    public function destroy({$className} {$classVar})
    {
        {$classVar}->delete();

        return sendSuccessResponse(
            '{$className} deleted successfully',
            [],
            Response::HTTP_NO_CONTENT
        );
    }
}
        ";
    }

    private function pluralize(string $word): string
    {
        // Simple pluralization logic
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        } elseif (in_array(substr($word, -1), ['s', 'x', 'z']) || in_array(substr($word, -2), ['ch', 'sh'])) {
            return $word . 'es';
        } else {
            return $word . 's';
        }
    }
}
