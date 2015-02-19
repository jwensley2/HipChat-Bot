<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Room;
use App\Token;
use Request;

class Hipchat extends Controller
{
    /** @var \App\Services\HipChat */
    protected $hipchat;

    public function __construct(\App\Services\HipChat $hipchat)
    {
        $this->hipchat = $hipchat;
    }

    public function capabilities()
    {
        $response = [
            'name'         => 'HipChat Imgur Bot',
            'description'  => 'Post images to Imgur from HipChat',
            'key'          => 'com.josephwensley.hipchatbot',
            'links'        => [
                'homepage' => 'http://154b882c.ngrok.com/',
                'self'     => 'http://154b882c.ngrok.com/addon/capabilities',
            ],
            'capabilities' => [
                'hipchatApiConsumer' => [
                    'scopes' => ['send_notification', 'admin_room']
                ],
                'installable'        => [
                    'callbackUrl' => 'http://154b882c.ngrok.com/addon/install'
                ],
            ],
        ];

        return response()->json($response);
    }

    public function install()
    {
        $data = json_decode(Request::getContent());

        \Log::info(Request::getContent());

        $this->hipchat->install($data);

        return response()->json([]);
    }

    public function imgur()
    {
        \Log::info(Request::getContent());

        $this->hipchat->sendMessage(Room::find(3));

        return response('test');
    }

    public function installWebhook()
    {
        $this->hipchat->createWebhook(Room::find(3));
    }
}
