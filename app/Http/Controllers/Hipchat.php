<?php namespace App\Http\Controllers;

use App\HipChat\Api;
use App\HipChat\Commands\Define;
use App\HipChat\Commands\Event;
use App\HipChat\Commands\Invite;
use App\HipChat\Commands\Math;
use App\HipChat\Commands\Reddit;
use App\HipChat\Commands\Roll as RollCommand;
use App\HipChat\Dispatcher;
use App\HipChat\Installer;
use App\HipChat\Webhooks\Events\RoomMessage;
use App\Http\Requests;
use App\Installation;
use Config;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
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
                    'callbackUrl' => \URL::route('install'),
                    'allowGlobal' => false,
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

        return response(null, 200);
    }

    public function uninstall($oAuthId)
    {
        $installer = new Installer(null);
        $installer->uninstall($oAuthId);

        return response(null, 200);
    }

    public function command()
    {
        $event = new RoomMessage(json_decode(Request::getContent()));

        /** @var Installation $install */
        $install = Installation::whereOauthId($event->oAuthClientId)->first();

        $this->hipchat->checkToken($install);

        $auth = new OAuth2($install->token->access_token);
        $client = new Client($auth);
        $roomApi = new RoomAPI($client);
        $api = new Api($client, $roomApi);
        $guzzle = new \GuzzleHttp\Client();

        $dispatcher = new Dispatcher($api, Config::get('hipchat.bot'));

        $dispatcher->registerCommand(new RollCommand($api));
        $dispatcher->registerCommand(new Math($api));
        $dispatcher->registerCommand(new Reddit($api, $guzzle, Config::get('hipchat.commands.reddit')));
        $dispatcher->registerCommand(new Invite($api));
        $dispatcher->registerCommand(new Define($api, $guzzle, Config::get('hipchat.commands.define')));
        $dispatcher->registerCommand(new Event($api));

        if ($install) {
            $dispatcher->dispatch($event);
        } else {
            Log::info("Installation not found || Client ID: {$event->oAuthClientId}");
        }
    }
}
