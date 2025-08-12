<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * @OA\Schema(
 *     schema="StoreUserRequest",
 *     title="StoreUserRequest",
 *     description="Данные для создания нового сотрудника",
 *     required={"last_name", "first_name", "position", "phone", "password"},
 *     @OA\Property(property="last_name", type="string", description="Фамилия", example="Петров"),
 *     @OA\Property(property="first_name", type="string", description="Имя", example="Петр"),
 *     @OA\Property(property="patronymic", type="string", nullable=true, description="Отчество", example="Петрович"),
 *     @OA\Property(property="position", type="string", description="Должность", example="Старший повар"),
 *     @OA\Property(property="phone", type="string", description="Номер телефона (логин)", example="79991234502"),
 *     @OA\Property(property="password", type="string", format="password", description="Пароль (минимум 8 символов)", example="password123"),
 *     @OA\Property(
 *         property="documents",
 *         description="Массив ID документов для назначения",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         example={1, 2}
 *     )
 * )
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Мы проверяем права доступа через middleware 'role:admin',
        // поэтому здесь просто разрешаем выполнение запроса.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 2. Добавляем наши правила валидации
        return [
            'last_name'  => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'position'   => 'required|string|max:255',
            'phone'      => 'required|string|unique:users,phone',
            'password'   => ['required', 'string', Password::min(8)],
            'documents'    => 'nullable|array',
            'documents.*'  => 'integer|exists:documents,id',
        ];
    }
}