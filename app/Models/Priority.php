<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'response_time'];

    protected $table = 'priority';

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
