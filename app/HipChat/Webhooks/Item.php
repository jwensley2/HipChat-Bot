<?php
namespace App\HipChat\Webhooks;

/**
 * @property-read Message $message
 * @property-read Room    $room
 */
class Item
{
    /** @var Message */
    protected $message;

    /** @var Room */
    protected $room;

    public function __construct($data)
    {
        if ($data->message) {
            $this->message = new Message($data->message);
        }

        if ($data->room) {
            $this->room = new Room($data->room);
        }
    }

    public function __get($prop)
    {
        if (isset($this->{$prop})) {
            return $this->{$prop};
        }

        return null;
    }
}