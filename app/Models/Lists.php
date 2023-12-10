<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lists extends Model
{
    use HasFactory;

    public function boards()
    {
        return $this->belongsToMany(Board::class, 'user_board');
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'list_id');
    }
}
