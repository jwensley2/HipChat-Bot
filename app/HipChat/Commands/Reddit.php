<?php
namespace App\HipChat\Commands;

use App\HipChat\Api;
use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;
use GuzzleHttp\Client;

class Reddit extends AbstractCommand implements CommandInterface
{
    protected $command     = 'reddit';
    protected $name        = 'Reddit';
    protected $description = 'Get a random image from the specified subreddit';
    protected $usage       = '<subreddit>';
    protected $aliases     = [];

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
        $subreddit = $command->getMessage();
        $roomId = $event->item->room->id;

        if (empty($subreddit)) {
            $this->sendMessage($roomId, "Usage: {$this->getUsage()}");

            return;
        }

        $post = $this->getRandomPost($subreddit);

        $view = view('hipchat.commands.reddit')->with('data', $post['data']);
        $this->sendMessage($roomId, $view->render(), 'html');
    }

    /**
     * Get a random post from a subreddit
     *
     * @param string $subreddit
     * @return mixed
     */
    protected function getRandomPost($subreddit)
    {
        $response = $this->guzzleClient->get("/r/{$subreddit}/top/.json", [
            'base_url' => 'https://www.reddit.com',
            'query'    => [
                'sort'  => $this->config['sort'],
                't'     => $this->config['timespan'],
                'limit' => $this->config['limit']
            ],
        ]);

        $json = $response->json();

        $posts = $json['data']['children'];

        if (!$this->config['nsfw']) {
            // Filter out NSFW posts
            $posts = array_filter($posts, function ($post) {
                return !$post['data']['over_18'];
            });
        }

        return $posts[mt_rand(0, count($posts) - 1)];
    }
}
