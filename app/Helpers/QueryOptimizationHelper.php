<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryOptimizationHelper
{
    /**
     * Optimize pagination queries with cursor-based pagination for large datasets
     */
    public static function optimizedPaginate(Builder $query, $perPage = 15, $cursor = null)
    {
        $perPage = min($perPage, 100); // Limit max per page
        
        if ($cursor) {
            $query->where('id', '>', $cursor);
        }
        
        return $query->take($perPage + 1)->get();
    }

    /**
     * Cache expensive query results
     */
    public static function cacheQuery(string $key, \Closure $callback, int $ttl = 300)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Optimize search queries by using fulltext search when available
     */
    public static function optimizeSearch(Builder $query, string $column, string $search)
    {
        // Use LIKE with leading anchor for better index usage
        return $query->where($column, 'LIKE', $search . '%');
    }

    /**
     * Batch update operations for better performance
     */
    public static function batchUpdate(string $table, array $updates, string $keyColumn = 'id')
    {
        if (empty($updates)) {
            return 0;
        }

        $sql = "UPDATE {$table} SET ";
        $sets = [];
        $bindings = [];
        
        foreach ($updates as $id => $values) {
            foreach ($values as $column => $value) {
                if (!in_array($column, $sets)) {
                    $sets[] = "{$column} = CASE {$keyColumn}";
                }
            }
        }

        // Build CASE statements
        $cases = [];
        foreach ($updates as $id => $values) {
            foreach ($values as $column => $value) {
                $cases[$column][] = "WHEN ? THEN ?";
                $bindings[] = $id;
                $bindings[] = $value;
            }
        }

        // Complete SQL
        $setClauses = [];
        foreach ($cases as $column => $whenClauses) {
            $setClauses[] = "{$column} = CASE {$keyColumn} " . implode(' ', $whenClauses) . " ELSE {$column} END";
        }

        $sql .= implode(', ', $setClauses);
        $sql .= " WHERE {$keyColumn} IN (" . str_repeat('?,', count($updates) - 1) . "?)";
        
        foreach (array_keys($updates) as $id) {
            $bindings[] = $id;
        }

        return DB::update($sql, $bindings);
    }

    /**
     * Get optimized database statistics
     */
    public static function getTableStats(string $table)
    {
        return Cache::remember("table_stats_{$table}", 600, function () use ($table) {
            $stats = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    data_length,
                    index_length,
                    (data_length + index_length) as total_size
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = ?
            ", [$table]);

            return $stats[0] ?? null;
        });
    }

    /**
     * Optimize joins with proper indexing hints
     */
    public static function optimizeJoin(Builder $query, string $table, string $first, string $operator, string $second, string $type = 'inner')
    {
        // Add index hints for better join performance
        return $query->join(DB::raw("{$table} USE INDEX (PRIMARY)"), $first, $operator, $second, $type);
    }

    /**
     * Batch insert with conflict resolution
     */
    public static function batchInsert(string $table, array $data, array $updateColumns = [])
    {
        if (empty($data)) {
            return 0;
        }

        $columns = array_keys($data[0]);
        $placeholders = '(' . str_repeat('?,', count($columns) - 1) . '?)';
        $values = str_repeat($placeholders . ',', count($data) - 1) . $placeholders;
        
        $sql = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES {$values}";
        
        if (!empty($updateColumns)) {
            $updates = array_map(fn($col) => "{$col} = VALUES({$col})", $updateColumns);
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', $updates);
        }

        $bindings = [];
        foreach ($data as $row) {
            foreach ($columns as $column) {
                $bindings[] = $row[$column];
            }
        }

        return DB::insert($sql, $bindings);
    }

    /**
     * Clear related caches when data changes
     */
    public static function clearRelatedCache(string $entity, ...$relatedEntities)
    {
        $tags = [$entity];
        $tags = array_merge($tags, $relatedEntities);
        
        foreach ($tags as $tag) {
            Cache::forget("{$tag}_statistics");
            Cache::tags([$tag])->flush();
        }
    }

    /**
     * Get optimized count with approximation for large tables
     */
    public static function approximateCount(string $table, Builder $query = null)
    {
        if ($query === null) {
            // For simple counts, use table statistics
            $stats = self::getTableStats($table);
            return $stats->table_rows ?? 0;
        }

        // For complex queries, fall back to regular count but cache it
        $cacheKey = 'count_' . $table . '_' . md5($query->toSql() . serialize($query->getBindings()));
        
        return Cache::remember($cacheKey, 300, function () use ($query) {
            return $query->count();
        });
    }
}