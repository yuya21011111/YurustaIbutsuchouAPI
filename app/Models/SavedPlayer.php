<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPlayer extends Model
{
    use HasFactory;

     protected $table = 'saved_players';

    protected $fillable = [
        'uid',
        'player_data',
    ];

    protected $casts = [
        'player_data' => 'array',
    ];
}
