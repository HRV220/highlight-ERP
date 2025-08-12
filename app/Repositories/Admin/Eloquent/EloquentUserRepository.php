<?php

namespace App\Repositories\Admin\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Admin\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{

  public function all(): Collection
  {
    return User::all();
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

      if (!empty($data['documents'])) {
        $user->documents()->sync($data['documents']);
      }

      return $user;
    });
  }
}
