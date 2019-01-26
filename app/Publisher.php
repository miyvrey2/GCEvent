<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    // Which items are fill-able in the database
    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'image', 'url', 'found'];

    // Select which columns from the database contain dates (and can be used by Carbon)
    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getDateAttribute()
    {
        return is_null($this->updated_at) ? '' : $this->updated_at->diffForHumans();
    }

    public function getFoundedAttribute()
    {
        if(!is_null($this->found)) {

            $date = $this->found;
            $separator = '-';
            $d = explode($separator, $date);

            // If month (and day as well) are unkown, show only the year
            if($d[1] == "00") {
                return Carbon::CreateFromFormat("Y-m-d", $d[0] . '-' . '01-01')->format('Y');
            } else if($d[2] == "00") {
                return Carbon::CreateFromFormat("Y-m-d", $d[0] . '-' . $d[1] . '-01')->format('F Y');
            }

            $dateText = Carbon::CreateFromFormat("Y-m-d", $this->found)->format('l jS \\of F Y');
            return "<a href='#' title='" . $dateText . "'>$d[0]</a>";
        }
        return 'Unkown.';
    }

    public function getBodyHtmlAttribute()
    {
        return $this->body;
    }

    public function getExcerptHtmlAttribute()
    {
        return e($this->excerpt);
    }

    // A publisher has many games
    public function games()
    {
        return $this->belongsToMany(Game::class);
    }

    // A publisher has many stands
    public function stands()
    {
        return $this->hasMany(Stand::class);
    }

    public function exhibitor_games()
    {
        return $this->hasMany(Game::class, 'exhibitor_id', 'id');
    }
}
