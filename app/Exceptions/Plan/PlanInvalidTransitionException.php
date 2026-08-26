<?php

declare(strict_types=1);

namespace App\Exceptions\Plan;

use App\Enums\PlanStatus;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class PlanInvalidTransitionException extends ConflictHttpException
{
    public static function forPublish(PlanStatus $from): self
    {
        return new self(sprintf(
            'プランの現在の状態(%s)は公開への変更を許可されていません。',
            $from->label(),
        ));
    }

    public static function forArchive(PlanStatus $from): self
    {
        return new self(sprintf(
            'プランの現在の状態(%s)はアーカイブへの変更を許可されていません。',
            $from->label(),
        ));
    }

    public static function forUnarchive(PlanStatus $from): self
    {
        return new self(sprintf(
            'プランの現在の状態(%s)は下書きへの変更を許可されていません。',
            $from->label(),
        ));
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
