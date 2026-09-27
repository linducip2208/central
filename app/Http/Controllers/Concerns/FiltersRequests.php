<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait FiltersRequests
{
    protected function tableQuery(Request $request, $query, array $searchFields = [], array $defaultSort = ['id', 'desc'])
    {
        if ($request->filled('q') && $searchFields) {
            $q = $request->get('q');
            $query->where(function ($w) use ($searchFields, $q) {
                foreach ($searchFields as $f) {
                    $w->orWhere($f, 'like', "%{$q}%");
                }
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        $sort = $request->get('sort', $defaultSort[0]);
        $dir = $request->get('dir', $defaultSort[1]) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $dir)->paginate($request->get('per_page', 15))->withQueryString();
    }

    protected function currentKitchen(Request $request): ?int
    {
        return $request->user()->central_kitchen_id;
    }

    protected function scopeKitchen(Request $request, $query, string $column = 'central_kitchen_id')
    {
        if ($request->user()->central_kitchen_id) {
            $query->where($column, $request->user()->central_kitchen_id);
        }

        return $query;
    }
}
