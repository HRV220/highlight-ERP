<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->string('title'); // Название документа
            $table->text('description')->nullable(); // Краткое описание (полезно иметь для карточек)
            $table->string('file_path'); // Путь к файлу в хранилище (например, 'documents/doc1.pdf')
            $table->string('original_filename'); // Исходное имя файла для скачивания
            $table->string('file_mime_type'); // MIME-тип (например, 'application/pdf')
            $table->unsignedInteger('file_size'); // Размер файла в байтах
            $table->boolean('is_for_all_employees')->default(false); // Чекбокс "Использовать для всех"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
