<?php

namespace App\Http\Controllers\api\Test;

use App\Http\Controllers\Controller;
use App\Http\Requests\Test\TestStoreRequest;
use App\Http\Requests\Test\TestUpdateRequest;
use App\Http\Resources\Test\TestResource;
use App\Http\Resources\Test\TestResourceCollection;
use App\Models\Test;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        return TestResourceCollection::make((Test::get()));
    }

    // Store a newly created resource in storage.
    public function store(TestStoreRequest $request)
    {
        return TestResource::make(Test::create($request->validated()));
    }

    // Display the specified resource.
    public function show($slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        return TestResource::make($test);
    }

    // Update the specified resource in storage.
    public function update(TestUpdateRequest $request, Test $test)
    {
        $test->update($request->validated());
        return TestResource::make($test);
    }

    // Remove the specified resource from storage.
    public function destroy(Test $test)
    {
        $test->delete();

        return response()->json([
        'success' => true,
        'message' => 'Test deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }
}
        