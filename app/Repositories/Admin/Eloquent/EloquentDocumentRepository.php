<?php

namespace App\Repositories\Admin\Eloquent;

use App\Models\Document;
use App\Models\User;
use App\Repositories\Admin\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
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
          'original_filename' => $file->getClientOriginalName(),
          'file_mime_type' => $file->getClientMimeType(),
          'file_size' => $file->getSize(),
          'is_for_all_employees' => $data['is_for_all_employees'] ?? false,
        ]);
        if ($document->is_for_all_employees) {
          $employeeIds = User::where('role', 'employee')->pluck('id');
          if ($employeeIds->isNotEmpty()) {
            $document->users()->attach($employeeIds);
          }
        }
        return $document;
      });
    }

        public function update(Document $document, array $data): Document
    {
        // Используем транзакцию, чтобы вся операция была единым целым.
        return DB::transaction(function () use ($document, $data) {

            // 1. Обновляем текстовые поля (название, флаг).
            // Arr::except убирает из данных поле 'file', чтобы Eloquent не пытался записать его в БД.
            $document->update(Arr::except($data, ['file']));

            // 2. Если в запросе был новый файл, обрабатываем его.
            if (Arr::has($data, 'file')) {
                $newFile = $data['file'];
                
                // Удаляем старый файл, чтобы не копить мусор.
                Storage::disk('public')->delete($document->file_path);

                // Сохраняем новый файл и получаем его путь.
                $newFilePath = Storage::disk('public')->putFile('documents', $newFile);

                // Обновляем в БД поля, связанные с файлом.
                $document->update([
                    'file_path' => $newFilePath,
                    'original_filename' => $newFile->getClientOriginalName(),
                    'file_mime_type' => $newFile->getClientMimeType(),
                    'file_size' => $newFile->getSize(),
                ]);
            }
            
            // Возвращаем обновленную модель документа.
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