<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Turn extends Model
{
    public $incrementig = false;
    public $fillable = ['player_id','location','type','game_id','id'];

    public function game(){
        $this->belongsTo('App\Game','game_id');
    }
    
}
