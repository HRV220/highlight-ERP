<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // В ТЗ поле называется "Название документа", в миграции - 'title'. Используем title.
            'title' => 'required|string|max:255',
            
            // В ТЗ поле называется "Загрузить файл". Назовем его 'file' в запросе.
            'file' => [
                'required',
                // 'file' - проверяет, что это действительно файл
                'file',
                // 'mimes' - проверяет разрешенные расширения (и MIME-типы)
                'mimes:pdf,docx,xlsx',
                // Ограничим максимальный размер файла, например, 10 МБ (10240 килобайт)
                'max:10240',
            ],

            // В ТЗ поле "Использовать для всех". В миграции `is_for_all_employees`
            'is_for_all_employees' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_for_all_employees')) {
            $this->merge([
                'is_for_all_employees' => filter_var($this->input('is_for_all_employees'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
