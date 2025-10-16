<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['ticket_id', 'user_id', 'action', 'created_at'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Método estático para registrar logs
    public static function register($ticket_id, $action)
    {
        self::create([
            'ticket_id'   => $ticket_id,
            'user_id'     => auth()->id(),
            'action'      => $action,
            'created_at'  => now(),
        ]);
    }

}
