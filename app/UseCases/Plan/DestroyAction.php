<?php

declare(strict_types=1);

namespace App\UseCases\Plan;

use App\Models\Plan;
use Illuminate\Support\Facades\DB;

final class DestroyAction
{
    public function __invoke(Plan $plan): void
    {
        DB::transaction(fn () => $plan->delete());
    }
}
