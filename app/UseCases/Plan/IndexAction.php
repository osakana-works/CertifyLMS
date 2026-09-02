<?php

declare(strict_types=1);

namespace App\UseCases\Plan;

use App\Enums\PlanStatus;
use App\Enums\UserStatus;
use App\Models\Plan;
use Illuminate\Pagination\LengthAwarePaginator;

final class IndexAction
{
    public function __invoke(
        ?string $keyword,
        ?PlanStatus $status,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Plan::query()->withCount([
            'users as users_count' => fn ($q) => $q->where('status', UserStatus::InProgress->value),
        ]);

        if ($keyword !== null && $keyword !== '') {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }
}
