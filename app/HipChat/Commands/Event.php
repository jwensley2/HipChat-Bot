<?php
namespace App\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;

class Event extends AbstractCommand implements CommandInterface
{
    protected $command     = 'event';
    protected $name        = 'Event';
    protected $description = 'Create an event';
    protected $usage       = 'N/A';
    protected $aliases     = ['calendar', 'date'];
    protected $subCommands = ['create', 'add', 'new', 'remove', 'delete', 'update', 'list'];

    /**
     * Triggers the command
     *
     * @param CommandParser $command
     * @param RoomMessage   $hookEvent
     * @return void
     */
    public function trigger(CommandParser $command, RoomMessage $hookEvent)
    {
        $roomId = $hookEvent->item->room->id;
        $subCommand = $this->getSubCommand($command);

        switch ($subCommand) {
            case 'create':
            case 'add':
            case 'new':
                $event = $this->createEvent($command, $hookEvent);
                $this->sendMessage($roomId, 'Event created');
                break;
            case 'list':
                $events = $this->getRoomEvents($hookEvent);
                $this->sendMessage($roomId, view('hipchat.commands.eventlist')->with(['events' => $events])->render());
                break;
        }
    }

    protected function getSubCommand(CommandParser $command)
    {
        $subCommand = current(explode(' ', $command->getMessage()));

        if (in_array($subCommand, $this->subCommands)) {
            return $subCommand;
        }

        return null;
    }

    protected function createEvent(CommandParser $command, RoomMessage $hookEvent)
    {
        $regex = '/{([a-zA-Z0-9\-:\s]*)}/';

        // Get the message without the sub command
        $message = implode(' ', array_slice(explode(' ', $command->getMessage()), 1));

        // Get the date
        preg_match($regex, $message, $date);
        $date = $date[1];

        // Strip the date
        $description = preg_replace($regex, '', $message, 1);

        // Save the event
        $event = new \App\Event();
        $event->room_id = $hookEvent->item->room->id;
        $event->creator_id = $hookEvent->item->message->from->id;
        $event->date = new \DateTime($date);
        $event->description = $description;

        $event->save();

        return $event;
    }

    protected function getRoomEvents(RoomMessage $hookEvent)
    {
        $events = \App\Event::whereRoomId($hookEvent->item->room->id)->get();

        return $events;
    }
}
