<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Профиль: все поля необязательны, пароль меняется только вместе с текущим. */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:30'],
            'username' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'regex:/^[a-z0-9_]{3,32}$/',
                Rule::unique('users', 'username')->ignore($this->user()?->id),
            ],
            'phone' => ['nullable', 'string', 'max:255', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:512'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'current_password' => ['required_with:password', 'nullable', 'current_password:web'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'password.confirmed' => 'Пароли не совпадают.',
            'phone.regex' => 'Телефон в формате +7 (900) 000-00-00.',
            'username.required' => 'Укажите username.',
            'username.lowercase' => 'Username должен быть в нижнем регистре.',
            'username.regex' => 'Username: от 3 до 32 символов, только латиница, цифры и _.',
            'username.unique' => 'Этот username уже занят.',
        ];
    }
}
