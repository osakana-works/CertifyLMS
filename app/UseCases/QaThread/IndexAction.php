<?php

declare(strict_types=1);

namespace App\UseCases\QaThread;

use App\Enums\CertificationStatus;
use App\Enums\QaThreadStatus;
use App\Enums\UserRole;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class IndexAction
{
    public function __invoke(
        User $viewer,
        ?string $keyword,
        ?string $certificationId,
        ?QaThreadStatus $status,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = QaThread::query()->with(['certification', 'user'])->withCount('replies');

        if ($viewer->role !== UserRole::Admin) {
            $query->whereHas('certification', fn ($q) => $q->published());
        } else {
            $query->whereHas('certification', fn ($q) => $q->where('status', '!=', CertificationStatus::Draft->value));
        }

        if ($keyword !== null && $keyword !== '') {
            $query->where('body', 'LIKE', "%{$keyword}%");
        }

        if ($certificationId !== null && $certificationId !== '') {
            $query->where('certification_id', $certificationId);
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}