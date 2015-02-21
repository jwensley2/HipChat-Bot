<?php
namespace App\HipChat\Commands;

use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

class Math implements CommandInterface
{
    /**
     * Get the command, eg. /bot <command>
     *
     * @return string
     */
    public function getCommand()
    {
        return 'math';
    }

    /**
     * Get an array containing aliases for the command
     *
     * @return array
     */
    public function getAliases()
    {
        return ['calc'];
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
        return 'Math';
    }

    /**
     * Get the command description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Perform math operations';
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

        $commands = array_map(function ($alias) use ($botCommand) {
            return "/{$botCommand} {$alias}";
        }, $this->getAliases());

        $commands[] = "/{$botCommand} {$this->getCommand()}";

        $value = trim(str_replace($commands, '', $hookData->item->message->message));

        $compiler = \Hoa\Compiler\Llk::load(
            new \Hoa\File\Read('hoa://Library/Math/Arithmetic.pp')
        );

        $visitor = new \Hoa\Math\Visitor\Arithmetic();

        $message = new Message();

        try {
            $ast = $compiler->parse($value);
            $message->setMessage("Result: " . (string)$visitor->visit($ast));
        } catch (\Exception $e) {
            $message->setMessage($e->getMessage());
        }

        $roomApi = new RoomAPI($client);
        $roomApi->sendRoomNotification($hookData->item->room->id, $message);
    }
}