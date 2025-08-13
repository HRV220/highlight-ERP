<?php

namespace App\Repositories\Admin\Eloquent;
use App\Models\User;


use App\Repositories\Admin\Contracts\StatisticRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentStatisticRepository implements StatisticRepositoryInterface
{
  public function getEmployeesWithDocumentsStatus(): Collection
  {
    return User::where('role', 'employee')
    ->with('documents')
    ->orderBy('last_name')
    ->get();
  
  }
}