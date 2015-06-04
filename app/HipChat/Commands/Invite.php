<?php
namespace App\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;

class Invite extends AbstractCommand implements CommandInterface
{
    protected $command     = 'invite';
    protected $name        = 'Invite';
    protected $description = 'Invite a user to the current room';
    protected $usage       = 'mentionname|email';
    protected $aliases     = ['adduser'];

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
        $users = explode(' ', $command->getMessage());

        foreach ($users as $user) {
            $this->api->getRoomApi()->addMember($roomId, $user);
        }

        $this->sendMessage($roomId, 'Invitation(s) send to ' . implode(',', $users, 'text'));
    }
}
