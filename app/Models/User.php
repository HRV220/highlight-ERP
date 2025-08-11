<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'position',
        'phone',
        'password',
        'role',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // Мы убрали лишнее поле email_verified_at
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Документы, назначенные этому пользователю.
     */
    public function documents(): BelongsToMany
    {
        // Если имя таблицы отличается от конвенции, его нужно указать
        $tableName = 'document_users'; 

        return $this->belongsToMany(Document::class, $tableName, 'user_id', 'document_id')
                    // Это самая важная добавка!
                    ->withPivot('status', 'read_at')
                    ->withTimestamps();
    }
}