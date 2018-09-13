<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    // Many to many (to connect pivot table in DB)
    public function games() {
        return $this->belongsToMany(Game::class);
    }
}