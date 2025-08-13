<?php

namespace App\Repositories\Admin\Eloquent;

use App\Models\Document;
use App\Models\User;
use App\Repositories\Admin\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function getAll(): Collection
    {
      return Document::orderBy('created_at', 'desc')->get();
    }

    public function create(array $data): Document
    {
      return DB::transaction(function () use ($data)
      {
        $file = $data['file'];

        $filePath = Storage::disk('public')->putFile('documents', $file);

        $document = Document::create([
          'title' => $data['title'],
          'file_path' => $filePath,
          // Получаем метаданные из загруженного файла
          'original_filename' => $file->getClientOriginalName(),
          'file_mime_type' => $file->getClientMimeType(),
          'file_size' => $file->getSize(),
          'is_for_all_employees' => $data['is_for_all_employees'] ?? false,
        ]);
        // 3. Реализуем логику "Использовать для всех" из ТЗ (п. 4.2.2.3)
        // Если при создании документа был установлен этот флаг...
        if ($document->is_for_all_employees) {
          // ...находим всех существующих сотрудников...
          $employeeIds = User::where('role', 'employee')->pluck('id');
          // ...и привязываем этот документ к каждому из них.
          if ($employeeIds->isNotEmpty()) {
            $document->users()->attach($employeeIds);
          }
        }
        // Возвращаем созданную модель документа
        return $document;
      });
    }


    public function delete(Document $document): bool
    {
      return DB::transaction(function () use ($document)
      {
        $deleteFile = $document->delete();
        if($deleteFile)
        { 
          Storage::disk('public')->delete($document->file_path);
          return true;
        }
        return false;
      });
    }
}