<?php

declare(strict_types=1);

namespace App\Http\Requests\QaReply;

use App\Models\QaThread;
use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var QaThread $thread */
        $thread = $this->route('thread');

        return $this->user()?->can('create', [\App\Models\QaReply::class, $thread]) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'body' => '本文',
        ];
    }
}