<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Installation;
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
        $capabilities = [
            'name'         => 'JoeBot',
            'description'  => 'Does the things',
            'vendor'       => [
                'url'  => 'http://josephwensley.com',
                'name' => 'Joseph Wensley',
            ],
            'key'          => 'com.josephwensley.hipchatbot',
            'links'        => [
                'homepage' => \URL::to('/'),
                'self' => \URL::route('capabilities'),
            ],
            'capabilities' => [
                'hipchatApiConsumer' => [
                    'scopes' => ['send_notification', 'admin_room']
                ],
                'installable'        => [
                    'callbackUrl' => \URL::route('install')
                ],
            ],
        ];

        return response()->json($capabilities);
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
        $data = json_decode(Request::getContent());

        $install = Installation::whereOauthId($data->oauth_client_id)->first();

        if ($install) {
            $this->hipchat->dispatch($install, $data);
        } else {
            \Log::warning('Installation not found');
            \Log::info(Request::getContent());
        }
    }

    public function installWebhook()
    {
        $this->hipchat->createWebhook(Installation::find(5));
    }
}
