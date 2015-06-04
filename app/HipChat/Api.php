<?php
namespace App\HipChat;

use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

class Api
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var RoomAPI
     */
    protected $roomApi;

    /**
     * @param Client  $client
     * @param RoomAPI $roomApi
     */
    public function __construct(Client $client, RoomAPI $roomApi)
    {
        $this->client = $client;
        $this->roomApi = $roomApi;
    }

    /**
     * @return \GorkaLaucirica\HipchatAPIv2Client\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return \GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI
     */
    public function getRoomApi()
    {
        return $this->roomApi;
    }

    /**
     * Send a message to a room
     *
     * @link https://www.hipchat.com/docs/apiv2/method/send_room_notification API Documentation
     *
     * @param int    $roomId The id of the room
     * @param string $body   The message body
     * @param string $format text or html
     * @param string $color  The colour of the message, allowed values are: yellow, green, red, purple, gray, random
     */
    public function sendMessage($roomId, $body, $format = 'html', $color = Message::COLOR_YELLOW)
    {
        $format = in_array($format, ['html', 'text']) ? $format : 'html';

        $message = new Message();
        $message->setMessage($body);
        $message->setMessageFormat($format);
        $message->setColor($color);

        $this->roomApi->sendRoomNotification($roomId, $message);
    }
}
