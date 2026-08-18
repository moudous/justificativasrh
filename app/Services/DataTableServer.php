<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTableServer
{
    public function response(Request $request, Builder $query, array $columns, callable $transform): JsonResponse
    {
        $total = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $query->where(function (Builder $filter) use ($columns, $search): void {
                foreach ($columns as $column) {
                    if ($column !== null) {
                        $filter->orWhere($column, 'like', '%'.$search.'%');
                    }
                }
            });
        }

        $filtered = (clone $query)->count();
        $orderIndex = (int) $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderIndex] ?? null;
        $direction = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        if ($orderColumn !== null) {
            $query->orderBy($orderColumn, $direction);
        }

        $length = min(max((int) $request->input('length', 10), 1), 100);
        $start = max((int) $request->input('start', 0), 0);

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $query->skip($start)->take($length)->get()->map($transform)->values(),
        ]);
    }
}
