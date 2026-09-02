<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePasswordRequest extends FormRequest
{
    /**
     * このRequestのエラーを保存する、名前付きエラーバッグ
     */
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => '現在のパスワード',
            'password' => 'パスワード',
            'password_confirmation' => 'パスワード(確認)',
        ];
    }
}
