<?php

namespace App\Repositories\Admin\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use App\Repositories\Admin\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

use LogicException;

class EloquentUserRepository implements UserRepositoryInterface
{

  public function all(): Collection
  {
    return User::where('role', 'employee')->orderBy('last_name')->get();
  }

  public function create(array $data): User
  {
    return DB::transaction(function () use ($data) {
      $user = User::create([
        'last_name' => $data['last_name'],
        'first_name' => $data['first_name'],
        'patronymic' => $data['patronymic'] ?? null,
        'position' => $data['position'],
        'phone' => $data['phone'],
        'password' => Hash::make($data['password']),
        'role' => 'employee',
      ]);

      // 1. Получаем ID документов, выбранных администратором вручную.
      // Используем collect() для удобной работы с массивами.
      $explicitlyAssignedDocIds = collect($data['documents'] ?? []);

      // 2. Получаем ID всех документов с флагом "для всех".
      // pluck('id') сразу вернет коллекцию ID, что очень эффективно.
      $defaultDocIds = Document::where('is_for_all_employees', true)->pluck('id');

      // 3. Объединяем две коллекции и удаляем дубликаты.
      // merge() объединяет коллекции, а unique() оставляет только уникальные значения.
      $allDocumentIdsToAttach = $explicitlyAssignedDocIds->merge($defaultDocIds)->unique();

      // 4. Если итоговый список документов не пуст, привязываем их к пользователю.
      if ($allDocumentIdsToAttach->isNotEmpty()) {
        $user->documents()->attach($allDocumentIdsToAttach->all());
      }

      return $user;
    });
  }
  public function update(User $user, array $data): User // Новая, правильная сигнатура
  {
    return DB::transaction(function () use ($user, $data) {
      $userData = Arr::except($data, ['documents']);
      if (isset($userData['password'])) {
        $userData['password'] = Hash::make($userData['password']);
      }
      $user->update($userData);
      
      if (Arr::has($data, 'documents')) {
        $newDocumentIds = $data['documents'] ?? [];
        $readDocumentIds = $user->documents()
        ->wherePivot('status', 'read')
        ->pluck('documents.id');
        // Проверяем, не пытается ли админ открепить прочитанный документ.
        foreach ($readDocumentIds as $readId) {
          // Если ID прочитанного документа отсутствует в новом списке 
          // которые должны быть привязаны к пользователю...
          if (!in_array($readId, $newDocumentIds)) {
            // ...то это недопустимая операция. Выбрасываем исключение
            // Транзакция будет автоматически отменена, и данные не сохранят
            throw new LogicException('Нельзя открепить документ, который сотрудник уже прочитал.');
          }
        }
        $user->documents()->sync($newDocumentIds);
      }
      return $user->fresh();
    });
  }
}
