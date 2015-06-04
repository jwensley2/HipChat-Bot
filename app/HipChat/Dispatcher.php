<?php
namespace App\HipChat;


use App\HipChat\Commands\CommandInterface;
use App\HipChat\Exceptions\DuplicateCommandException;
use App\HipChat\Webhooks\Events\RoomMessage;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

class Dispatcher
{
    /** @var  Api */
    protected $api;

    /** @var [] */
    protected $commands = [];

    /**
     * @param Api   $api    Api wrapper
     * @param array $config The bot configuration
     */
    public function __construct(Api $api, array $config = [])
    {
        $this->api = $api;
        $this->config = $config;
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
            throw new DuplicateCommandException('Duplicate command');
        }

        // Register the commands aliases, first come first serve
        if (is_array($command->getAliases())) {
            foreach ($command->getAliases() as $alias) {
                if (!array_key_exists($alias, $this->commands)) {
                    $this->commands[$alias] = [
                        'command' => $command,
                        'alias'   => true,
                    ];
                }
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

    /**
     * Dispatch the event to the proper command handler
     *
     * @param RoomMessage $event
     */
    public function dispatch(RoomMessage $event)
    {
        $command = new CommandParser($this->config['command'], $event->item->message->message);
        $commands = $this->getRegisteredCommands(true);

        if (array_key_exists($command->getCommand(), $commands)) {
            $commands[$command->getCommand()]->trigger($command, $event);
        } else {
            $this->showHelp($event->item->room->id);
        }
    }

    /**
     * Show the help message
     *
     * @param $roomId
     */
    public function showHelp($roomId)
    {
        $messageHtml = view('hipchat.help')
            ->with([
                'botname'    => $this->config['name'],
                'botcommand' => $this->config['command'],
                'commands'   => $this->getRegisteredCommands(false),
            ])
            ->render();

        $this->api->sendMessage($roomId, $messageHtml);
    }
}
