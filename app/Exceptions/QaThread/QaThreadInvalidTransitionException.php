<?php

declare(strict_types=1);

namespace App\Exceptions\QaThread;

use App\Enums\QaThreadStatus;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class QaThreadInvalidTransitionException extends ConflictHttpException
{
    public static function forResolve(QaThreadStatus $from): self
    {
        return new self(sprintf(
            'スレッドの現在の状態(%s)は解決済みへの変更を許可されていません。',
            $from->label(),
        ));
    }

    public static function forUnresolve(QaThreadStatus $from): self
    {
        return new self(sprintf(
            'スレッドの現在の状態(%s)は未解決への変更を許可されていません。',
            $from->label(),
        ));
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}