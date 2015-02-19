<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Token
 *
 * @property integer $id 
 * @property string $service 
 * @property string $oauth_id 
 * @property string $oauth_secret 
 * @method static \Illuminate\Database\Query\Builder|\App\Token whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Token whereService($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Token whereOauthId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Token whereOauthSecret($value)
 */
class Token extends Model
{
    protected $table = 'oauth_tokens';
    public $timestamps = false;
}
