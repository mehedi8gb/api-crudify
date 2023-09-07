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
        return "<?php

namespace App\Http\Controllers\api$this->controllerPath;

use App\Http\Controllers\Controller;
use App\Http\Requests\\{$this->modelBinding['className']}\\{$this->modelBinding['className']}StoreRequest;
use App\Http\Requests\\{$this->modelBinding['className']}\\{$this->modelBinding['className']}UpdateRequest;
use App\Http\Resources\\{$this->modelBinding['className']}\\{$this->modelBinding['className']}Resource;
use App\Http\Resources\\{$this->modelBinding['className']}\\{$this->modelBinding['className']}ResourceCollection;
use App\Models\\{$this->modelBinding['className']};
use Symfony\Component\HttpFoundation\Response;

class {$this->modelBinding['className']}Controller extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        return new {$this->modelBinding['className']}ResourceCollection(({$this->modelBinding['className']}::get()));
    }

    // Store a newly created resource in storage.
    public function store({$this->modelBinding['className']}StoreRequest \$request)
    {
         return new {$this->modelBinding['className']}Resource({$this->modelBinding['className']}::create(\$request->validated()));
    }

    // Display the specified resource.
    public function show({$this->modelBinding['className']} {$this->modelBinding['classVar']})
    {
        return new {$this->modelBinding['className']}Resource({$this->modelBinding['classVar']});
    }

    // Update the specified resource in storage.
    public function update({$this->modelBinding['className']}UpdateRequest \$request, {$this->modelBinding['className']} {$this->modelBinding['classVar']})
    {
        {$this->modelBinding['classVar']}->update(\$request->validated());
        return new {$this->modelBinding['className']}Resource({$this->modelBinding['classVar']});
    }

    // Remove the specified resource from storage.
    public function destroy({$this->modelBinding['className']} {$this->modelBinding['classVar']})
    {
        {$this->modelBinding['classVar']}->delete();

        return response()->json([
        'success' => true,
        'message' => '{$this->modelBinding['className']} deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }
}
        ";
    }
}
