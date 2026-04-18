<?php

use App\Helpers\SearchParamMapper;
use App\Http\Resources\DefaultResource;
use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

if (! function_exists('validationException')) {
    /**
     * Throw a validation exception with flexible input.
     *
     * @param  array|string  $payload
     *                                 - If array: ['field' => 'message']
     *                                 - If string: treated as a general error message mapped to 'error'
     *
     * @throws ValidationException
     */
    function validationException(array|string $payload, string $key = 'error'): void
    {
        if (is_string($payload)) {
            // You can change `error` to any key you want
            $payload = [
                $key => $payload,
            ];
        }

        throw ValidationException::withMessages($payload);
    }
}
if (! function_exists('cacheQuery')) {
    /**
     * Execute a query chain with automatic caching.
     *
     * @param  string  $method  Method to execute: get, first, paginate
     * @param  array  $args  Arguments for the method (e.g., paginate page size)
     * @param  int|null  $ttl  TTL in seconds
     */
    function cacheQuery(Builder|Closure $query, string $method = 'get', array $args = [], ?int $ttl = null): mixed
    {
        if ($query instanceof Closure) {
            $query = $query();
        }

        if (! ($query instanceof Builder)) {
            throw new InvalidArgumentException('cached() requires a Builder or Closure returning Builder');
        }

        $model = $query->getModel();
        $table = $model->getTable();
        $tenantId = tenant('id') ?? 'global';

        // Handle pagination
        if ($method === 'paginate') {
            $args['page'] = $args['page'] ?? request('page', 1);
        }

        // Generate key based on SQL + bindings + method + args
        $key = sprintf(
            'tenant:%s:table:%s:method:%s:%s',
            $tenantId,
            $table,
            $method,
            md5($query->toSql().json_encode($query->getBindings()).json_encode($args))
        );

        $ttl = $ttl ?? config('cache.default_ttl', 300);

        return Cache::tags([$table, "tenant:$tenantId"])->remember($key, $ttl, function () use ($query, $method, $args) {
            return $query->{$method}(...$args);
        });
    }
}

if (! function_exists('getCreatedAtColumn')) {
    /**
     * Resolve model's "created at" column dynamically from Builder.
     *
     * This respects custom constants like CREATED_AT or fallback to Eloquent default.
     */
    function getCreatedAtColumn(Builder|Model $target): string
    {
        $model = $target instanceof Builder ? $target->getModel() : $target;

        return defined(get_class($model).'::CREATED_AT')
            ? $model::CREATED_AT
            : $model->getCreatedAtColumn();
    }
}

if (! function_exists('filterPayload')) {
    /**
     * Keep only allowed keys from a data array.
     */
    function filterPayload(array $data, array $allowedKeys): array
    {
        return array_intersect_key($data, array_flip($allowedKeys));
    }
}

if (! function_exists('generateCacheKey')) {
    /**
     * Generate a deterministic cache key for this query
     */
    function generateCacheKey(Builder $builder, Request $request, string $tenantId): string
    {
        $queryPayload = [
            'table' => $builder->getModel()->getTable(),
            'where' => $request->query('where', []),
            'orWhere' => $request->query('orWhere', []),
            'sortBy' => $request->query('sortBy', 'createdAt'),
            'sortDir' => $request->query('sortDirection', 'desc'),
            'page' => $request->query('page', 1),
            'limit' => $request->query('limit', 10),
            'with' => $request->query('with', []),
            'trashed' => $request->query('trashed', 'default'),
            'tenant' => $tenantId,
        ];

        return 'query_cache:'.md5(json_encode($queryPayload));
    }
}

if (! function_exists('getResourceClass')) {
    function getResourceClass($model): string
    {
        // Base model class name without namespace
        $modelClassName = class_basename($model);

        // Possible suffixes in order of priority
        $suffixes = ['Resource', 'ResourceV1'];

        foreach ($suffixes as $suffix) {
            $resourceClass = "App\\Http\\Resources\\{$modelClassName}{$suffix}";

            if (class_exists($resourceClass)) {
                return $resourceClass;
            }
        }

        // Fallback if none exist
        return DefaultResource::class;
    }
}
if (! function_exists('deepMerge')) {
    /**
     * Perform a deep merge of two arrays, allowing forced replacement with a "forceReplace" value.
     * Includes handling for array deletions based on the forceReplace flag.
     */
    function deepMerge(array $original, array $new, string $forceReplaceIndicator = 'forceReplace'): array
    {
        foreach ($new as $key => $value) {
            // If value is marked as a forced replacement
            if ($value === $forceReplaceIndicator) {
                // Remove the key from the original array
                unset($original[$key]);

                continue;
            }

            // Skip overwriting with null/empty values
            if (is_null($value) || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
                continue;
            }

            if (is_array($value) && isset($original[$key]) && is_array($original[$key])) {
                // Recursively merge arrays
                $original[$key] = deepMerge($original[$key], $value, $forceReplaceIndicator);
            } else {
                // Overwrite scalar values or arrays
                $original[$key] = $value;
            }
        }

        return $original;
    }
}

if (! function_exists('processNestedArray')) {
    /**
     * Process nested arrays by removing missing indexes and merging incoming data.
     */
    function processNestedArray(array $existingArray, array $payloadArray): array
    {
        // Map payload by unique identifier (e.g., id)
        $payloadMap = collect($payloadArray)->keyBy('id');

        // Filter existing array to retain only indexes present in the payload
        $filteredArray = collect($existingArray)
            ->filter(fn ($item) => $payloadMap->has($item['id']))
            ->map(fn ($item) => array_merge($item, $payloadMap->get($item['id'])))
            ->values()
            ->toArray();

        // if array fragment same to same then remove 1 index
        return array_map('unserialize', array_unique(array_map('serialize', $filteredArray)));
    }
}

if (! function_exists('sendErrorResponse')) {
    /**
     * Format error response.
     */
    function sendErrorResponse(NotFoundHttpException|ModelNotFoundException|ErrorException|Exception|string $e, int $statusCode): JsonResponse
    {
        // Check if the environment is 'local' (for detailed error messages in dev)
        $isLocal = app()->environment('local');

        // check for duplicate entry error in QueryException
        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1];  // Get the MySQL error code

            // Check if it's a duplicate entry (error code 1062 for MySQL)
            if ($errorCode == 1062) {
                // Handle duplicate entry error
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate entry detected. Please ensure the value is unique.',
                ], 400);  // Send a 400 Bad Request response
            }
        }

        // Check for specific ErrorException related to roles access
        if ($e instanceof ErrorException && str_contains($e->getMessage(), 'Attempt to read property "roles" on false')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have the necessary permissions to access this resource.',
            ], 403); // Forbidden status code
        }

        // Handle ModelNotFoundException
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            $id = $e->getIds() ? implode(',', $e->getIds()) : 'Unknown';

            return response()->json([
                'success' => false,
                'message' => $isLocal ? "{$model} with ID {$id} not found. Details: {$e->getMessage()}" : 'The requested resource could not be found.',
            ], 405);
        }

        // Handle NotFoundHttpException (404)
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => $isLocal ? $e->getMessage() : 'The requested page could not be found.',
            ], 404);
        }

        // Handle QueryException (database query errors, 500)
        if ($e instanceof QueryException) {
            return response()->json([
                'success' => false,
                'message' => $isLocal ? $e->getMessage() : 'Database error. Please try again later.',
            ], 500);
        }

        // Handle general exceptions (500)
        if ($e instanceof Exception) {
            return response()->json([
                'success' => false,
                'message' => $isLocal ? $e->getMessage() : 'Internal Server Error. Please try again later.',
            ], 500);
        }

        // Handle string messages (fallback)
        if (is_string($e)) {
            return response()->json([
                'success' => false,
                'message' => $e,
            ], $statusCode);
        }

        // Fallback for unexpected cases (Internal Server Error)
        return response()->json([
            'success' => false,
            'message' => $isLocal ? $e->getMessage() : 'Internal Server Error',
        ], 500);
    }
}
if (! function_exists('sendSuccessResponse')) {
    /**
     * Format success response.
     */
    function sendSuccessResponse(string $message, mixed $data = null, int $statusCode = 200): JsonResponse
    {
        if ($data === null) {
            $data = new stdClass;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode, [], JSON_PRESERVE_ZERO_FRACTION);
    }
}

if (! function_exists('handleApiRequest')) {
    /**
     * Handle API request.
     *
     * @param  string|null  $resourceClass
     *
     * @throws Exception
     *
     * @deprecated This global function is deprecated. Use App\Core\Query\QueryHandler classes instead.
     */
    #[Deprecated('This global function is deprecated. Use use App\Core\Query\HandleApiQueryRequest class instead.')]
    function handleApiRequest(Request $request, Builder $query, array $with = [], $resourceClass = null): array
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sortBy', 'createdAt');
        $sortDirection = $request->query('sortDirection', 'desc');
        $selectFields = $request->query('select');
        $request->validate([
            'operator' => 'nullable|in:=,!=,<,<=,>,>=,like,ilike',
        ]);
        $operator = $request->query('operator', 'like');

        // this class will map and inject into request the low level query from the frontend high level query
        new SearchParamMapper($request);

        // Eager load relationships
        if (! empty($with)) {
            $query->with($with);
        }

        // Exclude from the query
        if ($request->query('exclude')) {
            $exclude = explode(',', $request->query('exclude'));
            $query->where($exclude[0], '!=', $exclude[1]);
        }

        // Check for the 'where' parameter
        if ($request->query('where')) {
            $filters = $request->query('where');
            // Multiple where conditions can be passed as an array
            $filters = is_array($filters) ? $filters : [$filters];

            $query->where(function ($q) use ($operator, $filters) {
                foreach ($filters as $filter) {
                    $parts = explode(',', $filter);

                    if (count($parts) < 2) {
                        throw new Exception(response()->json([
                            'error' => 'Invalid where format. Use where=column,value or where=with:relation,column,value',
                        ], 400));
                    }

                    $relationParts = [];

                    // Extract multiple 'with:' relations dynamically
                    while (! empty($parts) && str_starts_with($parts[0], 'with:')) {
                        $relationParts[] = str_replace('with:', '', array_shift($parts));
                    }

                    $column = $parts[0] ?? null;
                    $value = $parts[1] ?? null;

                    if (! $column || $value === null) {
                        throw new Exception(response()->json([
                            'error' => 'Invalid where format. Use where=column,value or where=with:relation,column,value',
                        ], 400));
                    }

                    if (! empty($relationParts)) {
                        // Handle nested relational filtering with where condition
                        $q->whereHas(implode('.', $relationParts), function ($relationQuery) use ($operator, $column, $value) {
                            $relationQuery->where($column, $operator, $value);
                        });
                    } else {
                        // Handle standard column filtering where
                        $q->where($column, $operator, $value);
                    }
                }
            });
        }

        // Check for the 'orWhere' parameter
        if ($request->query('orWhere')) {
            $filters = $request->query('orWhere');

            // Multiple where conditions can be passed as an array
            $filters = is_array($filters) ? $filters : [$filters];
            $query->orWhere(function ($orQuery) use ($operator, $filters) {
                foreach ($filters as $filter) {
                    $parts = explode(',', $filter);

                    if (count($parts) < 2) {
                        return ['error' => 'Invalid orWhere format. Use orWhere=column,value or orWhere=with:relation,column,value'];
                    }

                    $relationParts = [];

                    while (! empty($parts) && str_starts_with($parts[0], 'with:')) {
                        $relationParts[] = str_replace('with:', '', array_shift($parts));
                    }

                    $column = $parts[0] ?? null;
                    $value = $parts[1] ?? null;

                    if (! $column || $value === null) {
                        return ['error' => 'Invalid orWhere format. Use orWhere=column,value or orWhere=with:relation,column,value'];
                    }

                    if (! empty($relationParts)) {
                        $orQuery->orWhereHas(implode('.', $relationParts), function ($relationQuery) use ($operator, $column, $value) {
                            $relationQuery->where($column, $operator, $value);
                        });
                    } else {
                        $orQuery->orWhere($column, $operator, $value);
                    }
                }
            });
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDirection);

        // Select specific fields
        if ($selectFields !== null) {
            $query->select(explode(',', $selectFields));
        }

        // Fetch results
        if ($limit === 'all') {
            $results = $query->get();
            $total = $results->count();
        } else {
            $results = $query->paginate($limit, ['*'], 'page', $page);
            $total = $results->total();
        }

        // Meta information for pagination
        $meta = [
            'page' => (int) $page,
            'limit' => $limit === 'all' ? $total : $limit,
            'total' => $total,
            'totalPage' => $limit === 'all' ? 1 : $results->lastPage(),
        ];

        // Apply dynamic resource transformation
        if (! $resourceClass) {
            $resourceClass = getResourceClass($query->getModel());
        }

        $result = $request->query('select') !== null
            ? ($results instanceof LengthAwarePaginator ? $results->items() : $results->toArray())
            : $resourceClass::collection($results);

        return [
            'meta' => $meta,
            'result' => $result,
        ];
    }
}

if (! function_exists('getFormatedDate')) {
    function getFormatedDate(Carbon $date): string
    {
        // Format: 14th September at 08:21 AM in 2025
        $formatted = $date->format('jS F \a\t h:i A \i\n Y');

        return $date->diffForHumans()." ({$formatted})";
    }
}

if (! function_exists('generateUniqueNumber')) {
    function generateUniqueNumber(string $prefix = 'UNIQUE'): string
    {
        return strtoupper($prefix).'-'.date('Ymd').strtoupper(Str::random(6));
    }
}
