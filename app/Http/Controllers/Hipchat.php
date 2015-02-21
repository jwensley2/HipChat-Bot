<?php namespace App\Http\Controllers;

use App\HipChat\Commands\Math;
use App\HipChat\Commands\Roll as RollCommand;
use App\HipChat\Dispatcher;
use App\HipChat\Installer;
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
        $data = json_decode(Request::getContent());

        /** @var Installation $install */
        $install = Installation::whereOauthId($data->oauth_client_id)->first();

        $this->hipchat->checkToken($install);

        $auth = new OAuth2($install->token->access_token);
        $api = new Client($auth);

        $dispatcher = new Dispatcher($api);

        $dispatcher->registerCommand(new RollCommand());
        $dispatcher->registerCommand(new Math());

        if ($install) {
            $dispatcher->dispatch($data);
        } else {
            Log::info('Installation not found');
            Log::info(Request::getContent());
        }
    }

    public function installWebhook()
    {
        $this->hipchat->createWebhook(Installation::find(5));
    }
}
