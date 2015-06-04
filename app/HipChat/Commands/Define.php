<?php
namespace App\HipChat\Commands;

use App\HipChat\Api;
use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;
use GuzzleHttp\Client;

class Define extends AbstractCommand implements CommandInterface
{
    protected $command     = 'define';
    protected $name        = 'Define';
    protected $description = 'Get the definition of a word from Urbandictionary';
    protected $usage       = '<word>';
    protected $aliases     = ['def', 'dictionary'];

    /**
     * @var Client
     */
    protected $guzzleClient;

    public function __construct(Api $api, Client $guzzleClient, $config = [])
    {
        $this->guzzleClient = $guzzleClient;

        parent::__construct($api, $config);
    }

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

    /**
     * Get the definition for a term
     *
     * @param $term
     * @return string
     */
    protected function getDefinition($term)
    {
        $guzzle = new \GuzzleHttp\Client(['base_url' => 'http://api.urbandictionary.com']);
        $response = $guzzle->get('/v0/define', [
            'query' => ['term' => $term]
        ]);

        $json = $response->json();

        // Filter out long definitions
        $definitions = array_filter($json['list'], function ($definition) {
            if (strlen($definition['definition']) < 1000) {
                return true;
            }

            return false;
        });

        if (count($definitions) === 0) {
            return 'No definitions found';
        }

        // Take the top X
        $definitions = array_slice($definitions, 0, $this->config['definitions']);

        return view('hipchat.commands.define')
            ->with('definitions', $definitions)
            ->render();
    }
}
