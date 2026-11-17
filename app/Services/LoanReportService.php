<?php

namespace App\Services;

use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LoanReportService
{
    public function paginate(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return Loan::with(['member', 'user', 'approver', 'loanItems.book'])
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', max(1, $page));
    }

    /**
     * Bentuk payload identik dengan respons GET /api/loans.
     *
     * @return array{data: array<int, mixed>, links: array<string, mixed>, meta: array<string, mixed>}
     */
    public function payload(int $page = 1, int $perPage = 10): array
    {
        return LoanResource::collection($this->paginate($page, $perPage))
            ->response()
            ->getData(true);
    }
}
