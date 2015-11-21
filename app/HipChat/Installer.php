<?php
namespace App\HipChat;

use App\Installation;
use App\Services\HipChat;
use App\Token;
use Carbon\Carbon;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Webhook;
use Illuminate\Http\Exception\HttpResponseException;
use Log;

class Installer
{
    /** @var \StdClass */
    protected $data;

    /** @var array */
    protected $hooks = [];

    public function __construct($data)
    {
        $this->data = $data;

        $botCommand = \Config::get('hipchat.bot.command');

        $this->hooks = [
            [
                'name'    => 'help',
                'event'   => 'room_message',
                'url'     => \URL::to('addon/command'),
                'pattern' => "^\\/{$botCommand}$"
            ],
            [
                'name'    => 'command',
                'event'   => 'room_message',
                'url'     => \URL::to('addon/command'),
                'pattern' => "^\\/{$botCommand}\\s"
            ]
        ];
    }

    public function install()
    {
        $token = $this->saveToken();
        $install = $this->saveInstallation($token);

        $install->token()->associate($token);
        $install->save();

        $this->createHooks($install);

        return $install;
    }

    public function uninstall($oAuthId)
    {
        /** @var Installation $install */
        $install = Installation::whereOauthId($oAuthId)->first();

        if (!$install) {
            throw new HttpResponseException(response('Installation not found', 404));
        }

        $hc = new HipChat();
        $token = $hc->getAccessToken($install->oauth_id, $install->oauth_secret);

        if (!$token) {
            $install->token->delete();
            $install->delete();
        }
    }

    public function saveToken()
    {
        $hc = new HipChat();
        $accessToken = $hc->getAccessToken($this->data->oauthId, $this->data->oauthSecret);

        $expireDate = new Carbon();
        $expireDate->addSeconds($accessToken['expires_in']);

        $token = new Token();
        $token->service = 'hipchat';
        $token->access_token = $accessToken['access_token'];
        $token->expires = $expireDate;
        $token->save();

        return $token;
    }

    public function saveInstallation()
    {
        $install = new Installation();
        $install->oauth_id = $this->data->oauthId;
        $install->oauth_secret = $this->data->oauthSecret;
        $install->room_id = isset($this->data->roomId) ? $this->data->roomId : null;
        $install->group_id = isset($this->data->groupId) ? $this->data->groupId : null;
        $install->save();

        return $install;
    }

    public function createHooks(Installation $install)
    {
        $token = $install->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        foreach ($this->hooks as $hook) {
            $webhook = new Webhook();
            $webhook->setName($hook['name']);
            $webhook->setEvent($hook['event']);
            $webhook->setUrl($hook['url']);
            $webhook->setPattern($hook['pattern']);

            $roomAPI = new RoomAPI($client);
            $roomAPI->createWebhook($install->room_id, $webhook);
        }
    }
}
