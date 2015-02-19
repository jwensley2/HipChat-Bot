<?php
namespace App\Services;

use App\Room;
use App\Token;
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

        $token = new Token();
        $token->access_token = $accessToken;
        $token->save();

        $room = new Room();
        $room->room_id = $data->roomId;
        $room->group_id = $data->groupId;
        $room->token()->associate($token);
        $room->save();

        $this->createWebhook($room);
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

        Log::info($response->getBody());

        $json = $response->json();

        return $json['access_token'];
    }

    public function createWebhook(Room $room)
    {
        $token = $room->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        $webhook = new Webhook();
        $webhook->setName('imgur');
        $webhook->setEvent('room_message');
        $webhook->setUrl('http://154b882c.ngrok.com/addon/imgur');
        $webhook->setPattern('^\/joebot');

        $roomAPI = new RoomAPI($client);
        $roomAPI->createWebhook($room->room_id, $webhook);

        Log::info($roomAPI->getAllWebhooks($room->room_id));
    }

    public function sendMessage(Room $room)
    {
        $token = $room->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        $message = new Message();
        $message->setMessage('Testing');

        $roomAPI = new RoomAPI($client);
        $roomAPI->sendRoomNotification($room->room_id, $message);

        Log::info($roomAPI->getAllWebhooks($room->room_id));
    }

    public function listWebhooks(Room $room)
    {
        $token = $room->token;
        $accessToken = $token->access_token;

        $auth = new OAuth2($accessToken);
        $client = new Client($auth);

        $roomAPI = new RoomAPI($client);

        return $roomAPI->getAllWebhooks($room->room_id);
    }
}