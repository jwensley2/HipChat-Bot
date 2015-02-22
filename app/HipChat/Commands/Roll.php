<?php
namespace App\HipChat\Commands;


use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;

class Roll extends AbstractCommand implements CommandInterface
{
    protected $command     = 'roll';
    protected $name        = 'Roll';
    protected $description = 'Roll a random number';
    protected $usage       = '[max]';
    protected $aliases     = ['dice'];

    /**
     * Triggers the command
     *
     * @param CommandParser $command
     * @param RoomMessage   $event
     * @return void
     */
    public function trigger(CommandParser $command, RoomMessage $event)
    {
        $roomId = $event->item->room->id;
        $max = $command->getMessage();
        $max = is_numeric($max) ? (int)$max : 100;

        $roll = mt_rand(0, $max);

        $name = $event->item->message->from->name;

        $this->sendMessage($roomId, "{$name} rolled (0-{$max}): {$roll}");
    }

}