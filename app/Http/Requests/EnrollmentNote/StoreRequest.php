<?php

declare(strict_types=1);

namespace App\Http\Requests\EnrollmentNote;

use App\Models\Enrollment;
use App\Models\EnrollmentNote;
use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Enrollment $enrollment */
        $enrollment = $this->route('enrollment');

        return $this->user()?->can('create', [EnrollmentNote::class, $enrollment]) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'body' => '新規メモ',
        ];
    }
}
