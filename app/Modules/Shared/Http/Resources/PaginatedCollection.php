<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class PaginatedCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        $paginator = $this->paginator();

        return [
            'data' => $this->collection,
            'meta' => $paginator ? [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ] : null,
        ];
    }

    private function paginator(): ?LengthAwarePaginator
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return $this->resource;
        }
        // AnonymousResourceCollection wraps the paginator in ->resource
        $inner = $this->resource->resource ?? null;
        return $inner instanceof LengthAwarePaginator ? $inner : null;
    }
}
