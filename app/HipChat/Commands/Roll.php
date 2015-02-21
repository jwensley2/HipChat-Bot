<?php
namespace App\HipChat\Commands;


use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

class Roll implements CommandInterface
{
    /**
     * Get the command, eg. /bot <command>
     *
     * @return string
     */
    public function getCommand()
    {
        return 'roll';
    }

    /**
     * Get an array containing aliases for the command
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Mark an alias as disabled
     * Aliases may need to be disabled if multiple commands try to register the same one
     *
     * @param $alias
     * @return mixed
     */
    public function disableAlias($alias)
    {
        return;
    }

    /**
     * Get the name of the command
     *
     * @return string
     */
    public function getName()
    {
        return 'Roll';
    }

    /**
     * Get the command description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Roll the dice';
    }

    /**
     * Triggers the command
     *
     * @param Client    $client   The API client
     * @param \StdClass $hookData The data passed to the webhook
     */
    public function trigger(Client $client, $hookData)
    {
        $botCommand = \Config::get('hipchat.bot.command');

        $max = trim(str_replace("/{$botCommand} {$this->getCommand()}", '', $hookData->item->message->message));
        $max = is_numeric($max) ? (int)$max : 100;

        $roll = mt_rand(0, $max);

        $message = new Message();
        $message->setMessage("You rolled (0-{$max}): {$roll}");

        $roomApi = new RoomAPI($client);
        $roomApi->sendRoomNotification($hookData->item->room->id, $message);
    }

}