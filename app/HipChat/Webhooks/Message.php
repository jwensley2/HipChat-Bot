<?php
namespace App\HipChat\Webhooks;

/**
 * @property-read string    $id
 * @property-read string    $type
 * @property-read User      $from
 * @property-read string    $message
 * @property-read User[]    $mentions;
 * @property-read \DateTime $date
 */
class Message
{
    /** @var \DateTime */
    protected $date;

    /** @var File */
    protected $file;

    /** @var User */
    protected $from;

    /** @var string */
    protected $id;

    /** @var User[] */
    protected $mentions;

    /** @var string */
    protected $message;

    /** @var MessageLink[] */
    protected $messageLinks;

    /** @var string */
    protected $type;

    public function __construct($data)
    {
        $this->id = $data->id;
        $this->type = $data->type;
        $this->message = $data->message;
        $this->date = new \DateTime($data->date);

        if ($data->from) {
            $this->from = new User($data->from);
        }

        if ($data->mentions) {
            $this->mentions = array_map(function ($mention) {
                return new User($mention);
            }, $data->mentions);
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