<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Repositories\Admin\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Throwable;
use LogicException;

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
        // ПРАВИЛЬНО: используем $request->validated(), а не $request->validate().
        // validated() возвращает только те данные, что прошли валидацию,
        // и не требует повторного вызова правил.
        $validatedData = $request->validated();

        try {
            $user = $this->userRepository->create($validatedData);
            // Загружаем документы, чтобы они были в ответе.
            $user->load('documents');

            return response()->json($user, 201);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Не удалось создать сотрудника.'], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/admin/users/{user}",
     *      operationId="getUserById",
     *      summary="Получение информации о конкретном сотруднике",
     *      tags={"Администратор - Сотрудники"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="user",
     *          description="ID сотрудника",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Успешный ответ",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *      @OA\Response(response=404, description="Сотрудник не найден")
     * )
     */

    public function show(User $user): JsonResponse
    {
        // Загружаем документы и возвращаем JSON
        return response()->json($user->load('documents'));
    }

    /**
     * @OA\Put(
     *      path="/api/admin/users/{user}",
     *      operationId="updateUser",
     *      summary="Обновление данных сотрудника",
     *      tags={"Администратор - Сотрудники"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="user",
     *          description="ID сотрудника",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Обновленные данные сотрудника",
     *          @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Сотрудник успешно обновлен",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *      @OA\Response(response=404, description="Сотрудник не найден"),
     *      @OA\Response(response=422, description="Ошибка валидации или бизнес-логики"),
     *      @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userRepository->update($user, $request->validated());

            // Возвращаем обновленного пользователя со свежими данными о документах.
            return response()->json($updatedUser->load('documents'));

        } catch (LogicException $e) {
            // Если репозиторий бросит наше исключение (например, про прочитанные доки)
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Ошибка при обновлении сотрудника.'], 500);
        }
    }
}