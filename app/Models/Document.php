<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'original_filename',
        'file_mime_type',
        'file_size',
        'is_for_all_employees',
    ];
    
    protected $casts = [
    'is_for_all_employees' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'document_users', 'document_id', 'user_id');
    }

}
