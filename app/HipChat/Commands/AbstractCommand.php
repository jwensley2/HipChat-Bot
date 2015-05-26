<?php
/**
 * Created by PhpStorm.
 * User: Joseph
 * Date: 21/02/2015
 * Time: 8:22 PM
 */

namespace App\HipChat\Commands;

use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

abstract class AbstractCommand implements CommandInterface
{
    protected $command;
    protected $name;
    protected $description;
    protected $usage;

    /** @var array */
    protected $aliases = [];

    /** @var Client */
    protected $client;

    /** @var RoomApi */
    protected $roomApi;

    /** @var array */
    protected $config;

    /**
     * @param Client  $client The API client
     * @param RoomAPI $roomApi
     * @param array   $config
     */
    public function __construct(Client $client, RoomAPI $roomApi, $config = [])
    {
        $this->client = $client;
        $this->roomApi = $roomApi;
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


    public function sendMessage($roomId, $messageText, $format = 'html')
    {
        $format = in_array($format, ['html', 'text']) ? $format : 'html';

        $message = new Message();
        $message->setMessage($messageText);
        $message->setMessageFormat($format);

        $this->roomApi->sendRoomNotification($roomId, $message);
    }
}
