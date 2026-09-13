<?php
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationMerger
{
    /**
     * Merges two pagination instances
     *
     * @param  Illuminate\Pagination\LengthAwarePaginator $collection1
     * @param  Illuminate\Pagination\LengthAwarePaginator $collection2
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    static public function merge(LengthAwarePaginator $collection1, LengthAwarePaginator $collection2)
    {
        $total = $collection1->total() + $collection2->total();

        $perPage = $collection1->perPage() + $collection2->perPage();

        $items = array_merge($collection1->items(), $collection2->items());

        $paginator = new LengthAwarePaginator($items, $total, $perPage);

        return $paginator;
    }
}