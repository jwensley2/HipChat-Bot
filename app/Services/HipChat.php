<?php
namespace App\Services;

use App\Installation;
use App\Room;
use App\Token;
use Carbon\Carbon;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;
use GorkaLaucirica\HipchatAPIv2Client\Model\Webhook;
use Log;

class HipChat
{
    public function install($data)
    {
        $accessToken = $this->getAccessToken($data->oauthId, $data->oauthSecret);

        $expireDate = new Carbon();
        $expireDate->addSeconds($accessToken['expires_in']);

        $token = new Token();
        $token->access_token = $accessToken['access_token'];
        $token->expires = $expireDate;
        $token->save();

        $install = new Installation();
        $install->oauth_id = $data->oauthId;
        $install->oauth_secret = $data->oauthSecret;
        $install->room_id = $data->roomId;
        $install->token()->associate($token);
        $install->save();

        $this->createWebhook($install);
    }

    public function getAccessToken($authId, $authSecret)
    {
        $client = new \GuzzleHttp\Client(['base_url' => 'https://api.hipchat.com/v2/']);

        $response = $client->post('oauth/token', [
            'body' => [
                'grant_type' => 'client_credentials',
                'scope'      => 'send_notification admin_room',
            ],
            'auth' => [$authId, $authSecret]
        ]);

        return $response->json();
    }

    public function createWebhook(Installation $install)
    {
        $token = $install->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        $webhook = new Webhook();
        $webhook->setName('imgur');
        $webhook->setEvent('room_message');
        $webhook->setUrl('http://154b882c.ngrok.com/addon/imgur');
        $webhook->setPattern('^\/joebot');

        $roomAPI = new RoomAPI($client);
        $roomAPI->createWebhook($install->room_id, $webhook);

        Log::info($roomAPI->getAllWebhooks($install->room_id));
    }

    public function dispatch(Installation $install, $hookData)
    {
        $messageParts = explode(' ', trim(str_replace('/joebot', '', $hookData->item->message->message)), 2);

        $command = $messageParts[0];
        $value = isset($messageParts[1]) ? $messageParts[1] : null;

        Log::info($command);
        Log::info($value);

        if (empty($command)) {
            $this->sendMessage($install, 'Some help message');
            return;
        }

        switch ($command) {
            case 'joseph':
                $this->sendMessage($install, $command . ' is the best');
                break;
            case 'bruce':
            case 'topher':
            case 'bob':
            case 'roseanna':
            case 'rainulf':
                $this->sendMessage($install, 'so asian');
                break;
            case 'guillermo':
            case 'fernando':
                $this->sendMessage($install, 'mexican');
                break;
            case 'math':
                $compiler = \Hoa\Compiler\Llk::load(
                    new \Hoa\File\Read('hoa://Library/Math/Arithmetic.pp')
                );
                $visitor = new \Hoa\Math\Visitor\Arithmetic();
                $ast = $compiler->parse($value);
                $this->sendMessage($install, (string)$visitor->visit($ast));
                break;
            default:
                $this->sendMessage($install, 'who?');
                break;
        }
    }

    public function sendMessage($install, $messageText)
    {
        $this->checkToken($install);

        $token = $install->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        $message = new Message();
        $message->setMessage($messageText);

        $roomAPI = new RoomAPI($client);
        $roomAPI->sendRoomNotification($install->room_id, $message);
    }

    public function listWebhooks(Installation $install)
    {
        $token = $install->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        $roomAPI = new RoomAPI($client);

        return $roomAPI->getAllWebhooks($install->room_id);
    }

    public function checkToken(Installation $install)
    {
        $token = $install->token;

        if ($token->expires->lt(Carbon::now())) {
            \Log::info('Token Expired');
            $accessToken = $this->getAccessToken($install->oauth_id, $install->oauth_secret);

            $expireDate = new Carbon();
            $expireDate->addSeconds($accessToken['expires_in']);

            $token->access_token = $accessToken['access_token'];
            $token->expires = $expireDate;
            $token->save();
        } else {
            \Log::info('Token still good');
        }
    }
}