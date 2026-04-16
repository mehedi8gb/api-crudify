<?php

namespace App\Core\Query\Handlers\Optimization;

use App\Core\Query\Handlers\AbstractQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class CacheHandler extends AbstractQueryHandler
{
    protected int $ttl = 300; // 5 minutes

    /**
     * Process the query with caching
     *
     * @param Builder $builder
     * @param Request $request
     * @return Builder|array
     * @throws InvalidArgumentException
     */
    protected function process(Builder $builder, Request $request): Builder|array
    {
        $tenantId = tenancy()->tenant->id ?? 'global';
        $cacheKey = generateCacheKey($builder, $request, $tenantId);
        $forceRefresh = $request->query('refreshCache', false);

        $driver = config('cache.default'); // e.g. redis, file, database

        $canUseTags = in_array($driver, ['redis', 'memcached'], true);

        if ($forceRefresh || !Cache::has($cacheKey)) {
            // Execute the next handler in chain
            $result = $this->nextHandler
                ? $this->nextHandler->handle($builder, $request)
                : $builder->get();

            if ($canUseTags) {
                Cache::tags($this->getCacheTags($builder, $tenantId))
                    ->put($cacheKey, $result, $this->ttl);
            } else {
                Cache::put($cacheKey, $result, $this->ttl);
            }

            return $result;
        }

        // Return cached data
        if ($canUseTags) {
            return Cache::tags($this->getCacheTags($builder, $tenantId))
                ->get($cacheKey);
        }

        return Cache::get($cacheKey);
    }


    /**
     * Generate cache tags for this query
     */
    protected function getCacheTags(Builder $builder, string $tenantId): array
    {
        // Tags: table + tenant
        return [
            'table:' . $builder->getModel()->getTable(),
            'tenant:' . $tenantId,
        ];
    }
}
