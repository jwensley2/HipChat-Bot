<?php namespace App\Http\Controllers;

use App\HipChat\Commands\Math;
use App\HipChat\Commands\Roll as RollCommand;
use App\HipChat\Dispatcher;
use App\HipChat\Installer;
use App\HipChat\Webhooks\Events\RoomMessage;
use App\Http\Requests;
use App\Installation;
use Config;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use Log;
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
            'name'         => Config::get('hipchat.bot.name'),
            'description'  => Config::get('hipchat.bot.description'),
            'vendor'       => [
                'url'  => 'http://josephwensley.com',
                'name' => 'Joseph Wensley',
            ],
            'key'          => Config::get('hipchat.bot.key'),
            'links'        => [
                'homepage' => \URL::to('/'),
                'self'     => \URL::route('capabilities'),
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

        $installer = new Installer($data);
        $installer->install();

        return response()->json([]);
    }

    public function command()
    {
        $event = new RoomMessage(json_decode(Request::getContent()));

        /** @var Installation $install */
        $install = Installation::whereOauthId($event->oAuthClientId)->first();

        $this->hipchat->checkToken($install);

        $auth = new OAuth2($install->token->access_token);
        $client = new Client($auth);

        $dispatcher = new Dispatcher($client);

        $dispatcher->registerCommand(new RollCommand($client));
        $dispatcher->registerCommand(new Math($client));

        if ($install) {
            $dispatcher->dispatch($event);
        } else {
            Log::info("Installation not found || Client ID: {$event->oAuthClientId}");
        }
    }

    public function installWebhook()
    {
        $this->hipchat->createWebhook(Installation::find(5));
    }
}
