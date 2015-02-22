<?php
namespace App\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;

class Define extends AbstractCommand implements CommandInterface
{
    protected $command     = 'define';
    protected $name        = 'Define';
    protected $description = 'Get the definition of a word from Urbandictionary';
    protected $usage       = '<word>';
    protected $aliases     = ['def', 'dictionary'];

    /**
     * Triggers the command
     *
     * @param CommandParser $command
     * @param RoomMessage   $event
     * @return void
     */
    public function trigger(CommandParser $command, RoomMessage $event)
    {
        $roomId = $event->item->room->id;
        $definition = $this->getDefinition($command->getMessage());

        $this->sendMessage($roomId, $definition, 'html');
    }

    protected function getDefinition($term)
    {
        $guzzle = new \GuzzleHttp\Client(['base_url' => 'http://api.urbandictionary.com']);
        $response = $guzzle->get('/v0/define', [
            'query' => ['term' => $term]
        ]);

        $json = $response->json();

        // Filter out long definitions
        $definitions = array_filter($json['list'], function ($definition) {
            if (strlen($definition['definition']) < 500) {
                return true;
            }

            return false;
        });

        // Take the top 2
        $definitions = array_slice($definitions, 0, 2);

        return view('hipchat.commands.define')
            ->with('definitions', $definitions)
            ->render();
    }
}
