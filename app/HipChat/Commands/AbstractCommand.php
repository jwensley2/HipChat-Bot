<?php
/**
 * Created by PhpStorm.
 * User: Joseph
 * Date: 21/02/2015
 * Time: 8:22 PM
 */

namespace App\HipChat\Commands;

use App\HipChat\Api;

abstract class AbstractCommand implements CommandInterface
{
    protected $command;
    protected $name;
    protected $description;
    protected $usage;

    /** @var array */
    protected $aliases = [];

    /** @var array */
    protected $config;

    /**
     * @param Api   $api The API client
     * @param array $config
     */
    public function __construct(Api $api, $config = [])
    {
        $this->api = $api;
        $this->config = $config;

        if ($this->aliases) {
            $this->aliases = array_fill_keys($this->aliases, true);
        }
    }

    /**
     * Get the command, eg. /bot <command>
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get the name of the command
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the command description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the command usage
     *
     * @return string
     */
    public function getUsage()
    {
        return "{$this->command} {$this->usage}";
    }


    /**
     * Get an array containing aliases for the command
     *
     * @return array
     */
    public function getAliases()
    {
        $aliases = array_keys(array_filter($this->aliases, function ($enabled) {
            return $enabled;
        }));

        return $aliases;
    }

    /**
     * Mark an alias as disabled
     * Aliases may need to be disabled if multiple commands try to register the same one
     *
     * @param $alias
     * @return void
     */
    public function disableAlias($alias)
    {
        $this->aliases[$alias] = false;
    }


    /**
     * Send a message to a room
     *
     * @param int    $roomId The id of the room
     * @param string $body   The message body
     * @param string $format text or html
     * @deprecated
     * @see \App\HipChat\Api::sendMessage
     */
    public function sendMessage($roomId, $body, $format = 'html')
    {
        $this->api->sendMessage($roomId, $body, $format);
    }
}
