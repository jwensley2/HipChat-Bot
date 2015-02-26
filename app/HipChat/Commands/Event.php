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

        $commandMessage = trim(preg_replace("/^{$subCommand}/i", '', $command->getMessage()));

        switch ($subCommand) {
            case 'create':
            case 'add':
            case 'new':
                try {
                    $this->createEvent($commandMessage, $hookEvent);
                    $this->sendMessage($roomId, 'Event created');
                } catch (\Exception$e) {
                    $this->sendMessage($roomId, $e->getMessage());
                }
                break;
            case 'list':
                $events = $this->getRoomEvents($hookEvent->item->room->id, ['after' => new \DateTime()]);
                $this->sendMessage($roomId, view('hipchat.commands.eventlist')->with(['events' => $events])->render());
                break;
            case 'delete':
                try {
                    $this->deleteEvent($commandMessage, $hookEvent);
                    $this->sendMessage($roomId, 'Event deleted');
                } catch (\Exception$e) {
                    $this->sendMessage($roomId, $e->getMessage());
                }
                break;
            default:
                $this->sendMessage($roomId, 'Invalid Command');
                break;
        }
    }

    /**
     * Get the sub command
     *
     * @param \App\HipChat\CommandParser $command
     * @return mixed|null
     */
    protected function getSubCommand(CommandParser $command)
    {
        $subCommand = current(explode(' ', $command->getMessage()));

        if (in_array($subCommand, $this->subCommands)) {
            return $subCommand;
        }

        return null;
    }

    /**
     * Create a new event
     *
     * @param string      $commandMessage
     * @param RoomMessage $hookEvent
     * @return \App\Event
     * @throws \Exception
     */
    protected function createEvent($commandMessage, RoomMessage $hookEvent)
    {
        $regex = '/^{([a-zA-Z0-9\-:\s]*)}/';

        // Get the date
        preg_match($regex, $commandMessage, $date);
        $date = isset($date[1]) ? $date[1] : null;

        if (!$date) {
            throw new \Exception('No date found');
        }

        // Strip the date
        $description = trim(preg_replace($regex, '', $commandMessage, 1));

        if (empty($description)) {
            throw new \Exception('You must provide a description');
        }

        // Save the event
        $event = new \App\Event();
        $event->room_id = $hookEvent->item->room->id;
        $event->creator_id = $hookEvent->item->message->from->id;
        $event->date = new \DateTime($date);
        $event->description = $description;

        $event->save();

        return $event;
    }

    /**
     * Delete an event
     *
     * @param int|string  $id
     * @param RoomMessage $hookEvent
     * @throws \Exception
     */
    protected function deleteEvent($id, RoomMessage $hookEvent)
    {
        if (!is_numeric($id)) {
            throw new \Exception('ID must be a number');
        }

        /** @var \App\Event $event */
        $event = \App\Event::find($id);

        if (!$event) {
            throw new \Exception('Event not found');
        }

        if ($event->creator_id !== $hookEvent->item->message->from->id) {
            throw new \Exception('You cannot delete another user\'s event');
        }

        $event->delete();
    }

    /**
     * Get the events for a room
     *
     * @param int   $roomId
     * @param array $options
     * @return mixed
     */
    protected function getRoomEvents($roomId, array $options = null)
    {
        $events = \App\Event::whereRoomId($roomId)
            ->orderBy('date', 'desc');

        if (isset($options['after'])) {
            $events->where('date', '>=', $options['after']);
        }

        if (isset($options['before'])) {
            $events->where('date', '<=', $options['before']);
        }

        return $events->get();
    }
}
