<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Массив ролей, которым разрешен доступ.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Мы предполагаем, что этот middleware ВСЕГДА используется ПОСЛЕ 'auth:sanctum',
        // поэтому $request->user() всегда будет существовать.

        $user = $request->user();

        // Проверяем, есть ли у пользователя одна из разрешенных ролей
        foreach ($roles as $role) {
            if ($user->role === $role) {
                // Роль совпала, пропускаем запрос дальше.
                return $next($request);
            }
        }

        // Если цикл завершился и совпадений не найдено, значит, доступ запрещен.
        // Возвращаем ошибку 403 Forbidden.
        return response()->json(['message' => 'This action is unauthorized.'], 403);
    }
}