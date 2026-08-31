<?php

declare(strict_types=1);

namespace App\Exceptions\MeetingPack;

use App\Enums\MeetingPackStatus;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class MeetingPackInvalidTransitionException extends ConflictHttpException
{
    public static function forPublish(MeetingPackStatus $from): self
    {
        return new self(sprintf(
            '面談パックの現在の状態(%s)は公開への変更を許可されていません。',
            $from->label(),
        ));
    }

    public static function forArchive(MeetingPackStatus $from): self
    {
        return new self(sprintf(
            '面談パックの現在の状態(%s)はアーカイブへの変更を許可されていません。',
            $from->label(),
        ));
    }

    public static function forUnarchive(MeetingPackStatus $from): self
    {
        return new self(sprintf(
            '面談パックの現在の状態(%s)は下書きへの変更を許可されていません。',
            $from->label(),
        ));
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
