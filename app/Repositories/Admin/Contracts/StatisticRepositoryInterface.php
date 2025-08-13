<?php

namespace App\Repositories\Admin\Contracts;

use Illuminate\Support\Collection;

interface StatisticRepositoryInterface
{
  public function getEmployeesWithDocumentsStatus(): Collection;
}