<?php
namespace App\HipChat\Webhooks;

/**
 * @property-read string $id
 * @property-read string $name
 */
class Room
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    public function __construct($data)
    {
        $this->id = $data->id;
        $this->name = $data->name;
    }

    public function __get($prop)
    {
        if (isset($this->{$prop})) {
            return $this->{$prop};
        }

        return null;
    }
}