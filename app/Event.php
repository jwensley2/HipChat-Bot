<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Event
 *
 * @property integer $id
 * @property integer $room_id
 * @property integer $creator_id
 * @property string $description
 * @property \Carbon\Carbon $date
 * @property string $created_at
 * @property string $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereRoomId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereCreatorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Event whereUpdatedAt($value)
 */
class Event extends Model
{
    protected $table = 'events';

    public function getDates()
    {
        return ['date'];
    }
}
