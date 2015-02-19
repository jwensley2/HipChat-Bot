<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Room
 *
 * @property-read \App\Token $token
 * @property integer $id 
 * @property integer $room_id 
 * @property integer $group_id 
 * @property integer $token_id 
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereRoomId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereGroupId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereTokenId($value)
 */
class Room extends Model
{
    protected $table = 'rooms';
    public $timestamps = false;

    public function token()
    {
        return $this->belongsTo('App\Token');
    }

}
