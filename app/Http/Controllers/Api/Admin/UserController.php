<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/admin/users",
     *      summary="Получение списка всех сотрудников",
     *      tags={"Администратор - Сотрудники"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Успешный ответ",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/User")
     *          )
     *      )
     * )
     */
    public function index(): JsonResponse
    {
        $employees = User::where('role', 'employee')->get();

        return response()->json($employees);
    }


}
