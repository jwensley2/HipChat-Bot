<?php
namespace App\HipChat;


use App\HipChat\Commands\CommandInterface;
use Config;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

class Dispatcher
{
    /** @var  Client */
    protected $api;

    /** @var [] */
    protected $commands = [];

    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Register a command with the dispatcher
     *
     * @param CommandInterface $command
     * @throws \Exception
     */
    public function registerCommand(CommandInterface $command)
    {
        // Register the main command
        if (!array_key_exists($command->getCommand(), $this->commands)) {
            $this->commands[$command->getCommand()] = [
                'command' => $command,
                'alias'   => false,
            ];
        } else {
            throw new \Exception('Duplicate command');
        }

        // Register the commands aliases, first come first serve
        foreach ($command->getAliases() as $alias) {
            if (!array_key_exists($alias, $this->commands)) {
                $this->commands[$alias] = [
                    'command' => $command,
                    'alias'   => true,
                ];
            }
        }
    }

    /**
     * Get all the registered commands
     *
     * @param bool $includeAliases
     * @return CommandInterface[]
     */
    public function getRegisteredCommands($includeAliases = false)
    {
        $registeredCommands = [];

        foreach ($this->commands as $key => $command) {
            if ($command['alias'] && !$includeAliases) {
                continue;
            }

            $registeredCommands[$key] = $command['command'];
        }

        return $registeredCommands;
    }

    public function dispatch($hookData)
    {
        $botCommand = \Config::get('hipchat.bot.command');
        $messageParts = explode(' ', trim(str_replace("/{$botCommand}", '', $hookData->item->message->message)), 2);

        $command = $messageParts[0];
        $commands = $this->getRegisteredCommands(true);

        if (array_key_exists($command, $commands)) {
            $commands[$command]->trigger($this->api, $hookData);
        } else {
            $this->showHelp($hookData->item->room->id);
        }
    }

    /**
     * Show the help message
     *
     * @param $roomId
     */
    public function showHelp($roomId)
    {
        $roomAPI = new RoomAPI($this->api);

        $messageHtml = view('hipchat.help')
            ->with([
                'botname'  => Config::get('hipchat.bot.name'),
                'botcommand' => Config::get('hipchat.bot.command'),
                'commands' => $this->getRegisteredCommands(false),
            ])
            ->render();

        $message = new Message();
        $message->setMessage($messageHtml);
        $roomAPI->sendRoomNotification($roomId, $message);
    }
}