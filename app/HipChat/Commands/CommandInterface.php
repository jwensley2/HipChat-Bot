<?php
namespace App\HipChat\Commands;

use GorkaLaucirica\HipchatAPIv2Client\Client;

interface CommandInterface
{

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
     * @param Client $client   The API client
     * @param \StdClass  $hookData The data passed to the webhook
     */
    public function trigger(Client $client, $hookData);
}