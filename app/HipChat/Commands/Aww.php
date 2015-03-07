<?php
namespace App\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;

class Aww extends AbstractCommand implements CommandInterface
{
    protected $command     = 'aww';
    protected $name        = 'Aww';
    protected $description = 'Get a random image from /r/aww';
    protected $usage       = '';
    protected $aliases     = ['awww', '/r/aww'];

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
        $post = $this->getRandomPost();

        $view = view('hipchat.commands.aww')->with('data', $post['data']);
        $this->sendMessage($roomId, $view->render(), 'html');
    }

    protected function getRandomPost()
    {
        $guzzle = new \GuzzleHttp\Client(['base_url' => 'https://www.reddit.com']);
        $response = $guzzle->get('/r/aww/top/.json', [
            'query' => [
                'sort'  => $this->config['sort'],
                't'     => $this->config['timespan'],
                'limit' => $this->config['limit']
            ]
        ]);

        $json = $response->json();

        $posts = $json['data']['children'];

        return $posts[mt_rand(0, count($posts) - 1)];
    }
}
