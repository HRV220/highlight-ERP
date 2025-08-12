<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Repositories\Admin\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Throwable;

class UserController extends Controller
{

    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

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
        $employees = User::where('role', 'employee')->orderBy('last_name')->get();
        return response()->json($employees);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/users",
     *      summary="Создание нового сотрудника",
     *      tags={"Администратор - Сотрудники"},
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody
     *          (required=true,
     *          description="Данные нового сотрудника",
     *          @OA\JsonContent(ref="#/components/schemas/StoreUserRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Сотрудник успешно создан",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *      @OA\Response(response=422, description="Ошибка валидации"),
     *      @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validatedData = $request->validate((new StoreUserRequest())->rules());

        try {
            // 4. Передаем в репозиторий только что проверенные данные
            $user = $this->userRepository->create($validatedData);
            $user->load('documents');

            return response()->json($user, 201);

        } catch (Throwable $e) {
            // report($e);
            return response()->json(['message' => 'Не удалось создать сотрудника.'], 500);
        }
    }
}