<?php
namespace App\HipChat;

class CommandParser
{
    /**
     * The command
     *
     * @var mixed
     */
    protected $command;

    /**
     * The message with the '/bot command' part removed
     *
     * @var string
     */
    protected $message;

    public function __construct($botCommand, $message)
    {
        // Strip out the bot command
        $message = trim(str_replace("/{$botCommand}", '', $message));

        $this->command = current(explode(' ', $message));
        $this->message = trim(str_replace($this->command, '', $message));
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
