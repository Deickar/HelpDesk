<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'message',
    ];

    // Relación con el usuario que hizo la acción
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function register($module, $action, $message)
    {
        self::create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'message' => $message,
        ]);
    }
}
