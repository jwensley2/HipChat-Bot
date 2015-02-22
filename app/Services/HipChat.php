<?php
namespace App\Services;

use App\Installation;
use Carbon\Carbon;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use Log;

class HipChat
{
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
            $accessToken = $this->getAccessToken($install->oauth_id, $install->oauth_secret);

            $expireDate = new Carbon();
            $expireDate->addSeconds($accessToken['expires_in']);

            $token->access_token = $accessToken['access_token'];
            $token->expires = $expireDate;
            $token->save();
        }
    }
}