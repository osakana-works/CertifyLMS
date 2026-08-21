<?php

declare(strict_types=1);

namespace App\Http\Requests\QaThread;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\QaThread::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'certification_id' => ['required', 'exists:certifications,id'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'certification_id' => '資格',
            'title' => 'タイトル',
            'body' => '本文',
        ];
    }
}