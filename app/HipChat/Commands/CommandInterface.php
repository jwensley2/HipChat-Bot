<?php
namespace App\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;
use GorkaLaucirica\HipchatAPIv2Client\Client;

interface CommandInterface
{

    /**
     * @param Client $client The API client
     * @param array  $config
     */
    public function __construct(Client $client, $config);

    /**
     * Get the command, eg. /bot <command>
     *
     * @return string
     */
    public function getCommand();

    /**
     * Get an array containing aliases for the command
     *
     * @return array
     */
    public function getAliases();

    /**
     * Mark an alias as disabled
     * Aliases may need to be disabled if multiple commands try to register the same one
     *
     * @param $alias
     * @return void
     */
    public function disableAlias($alias);

    /**
     * Get the name of the command
     *
     * @return string
     */
    public function getName();

    /**
     * Get the command description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get the command usage
     *
     * @return string
     */
    public function getUsage();

    /**
     * Triggers the command
     *
     * @param CommandParser $command
     * @param RoomMessage   $event
     * @return void
     */
    public function trigger(CommandParser $command, RoomMessage $event);
}
