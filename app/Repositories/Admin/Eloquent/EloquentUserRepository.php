<?php

namespace App\Repositories\Admin\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use App\Repositories\Admin\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

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

      $explicitlyAssignedDocIds = collect($data['documents'] ?? []);

      $defaultDocIds = Document::where('is_for_all_employees', true)->pluck('id');

      $allDocumentIdsToAttach = $explicitlyAssignedDocIds->merge($defaultDocIds)->unique();

      if ($allDocumentIdsToAttach->isNotEmpty()) {
        $user->documents()->attach($allDocumentIdsToAttach->all());
      }

      return $user;
    });
  }
  public function update(User $user, array $data): User
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
        foreach ($readDocumentIds as $readId) {
          if (!in_array($readId, $newDocumentIds)) {
            throw new LogicException('Нельзя открепить документ, который сотрудник уже прочитал.');
          }
        }
        $user->documents()->sync($newDocumentIds);
      }
      return $user->fresh();
    });
  }
  public function delete(User $user): bool
  {
    return DB::transaction(function () use ($user) {
      $avatarPath = $user->avatar_path;
      $deletedFromDb = $user->delete();

      if ($deletedFromDb && $avatarPath) {
        Storage::disk('public')->delete($avatarPath);
      }
      return $deletedFromDb;
    });

  }
}
