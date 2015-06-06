<?php
namespace App\HipChat\Commands;

use App\HipChat\Api;
use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Reddit extends AbstractCommand implements CommandInterface
{
    const REDDIT_URL = 'https://www.reddit.com';

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

        try {
            $post = $this->getRandomPost($subreddit);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $this->api->sendMessage($roomId, "{$subreddit} is not a valid subreddit", 'text');

                return;
            }

            throw $e;
        }

        if (is_null($post)) {
            $this->api->sendMessage($roomId, 'No posts were retrieved', 'text');
        } else {
            $view = view('hipchat.commands.reddit')->with('data', $post['data']);
            $this->api->sendMessage($roomId, $view->render(), 'html');
        }
    }

    /**
     * Get a random post from a subreddit
     *
     * @param string $subreddit
     * @return mixed
     */
    protected function getRandomPost($subreddit)
    {
        $defaultParams = ['sort' => 'top', 'timespan' => 'day', 'limit' => 50];
        $params = array_merge($defaultParams, $this->config);

        $response = $this->guzzleClient->get(self::REDDIT_URL . "/r/{$subreddit}/top/.json", [
            'query' => [
                'sort'  => $params['sort'],
                't'     => $params['timespan'],
                'limit' => $params['limit']
            ],
        ]);

        $json = $response->json();

        $posts = $json['data']['children'];

        if (!$this->config['nsfw']) {
            $posts = $this->filterNSFWPosts($posts);
        }

        if (is_array($posts) && count($posts) > 0) {
            return $posts[mt_rand(0, count($posts) - 1)];
        }

        return null;
    }

    /**
     * Filter out NSFW posts
     *
     * @param array $posts
     * @return array
     */
    protected function filterNSFWPosts(array $posts)
    {
        return array_values(array_filter($posts, function ($post) {
            return !$post['data']['over_18'];
        }));
    }
}
