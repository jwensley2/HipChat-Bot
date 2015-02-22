<?php
namespace App\HipChat\Webhooks;

/**
 * @property-read int    $id
 * @property-read string $mentionName
 * @property-read string $name
 */
class User
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $mentionName;

    /** @var string */
    protected $name;

    public function __construct($data)
    {
        $this->id = $data->id;
        $this->mentionName = $data->mention_name;
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