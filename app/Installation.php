<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Installation
 *
 * @property integer        $id
 * @property string         $oauth_id
 * @property string         $oauth_secret
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereOauthId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereOauthSecret($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereUpdatedAt($value)
 * @property integer        $token_id
 * @property integer        $room_id
 * @property integer        $group_id
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereTokenId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereRoomId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Installation whereGroupId($value)
 * @property-read \App/Token $token
 */
class Installation extends Model
{
    protected $table = 'installations';

    public function token()
    {
        return $this->belongsTo('App\Token');
    }
}
