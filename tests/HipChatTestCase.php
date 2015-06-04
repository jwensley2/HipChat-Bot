<?php
namespace Tests;

use App\HipChat\Api;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;

class HipChatTestCase extends \TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function getApiMock()
    {
        return $this->getMockBuilder('\App\HipChat\Api')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected function getClientMock()
    {
        return $this->getMockBuilder('\GorkaLaucirica\HipchatAPIv2Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param Client $client
     * @return \PHPUnit_Framework_MockObject_MockObject|RoomAPI
     */
    protected function getRoomApiMock(Client $client)
    {
        return $this->getMockBuilder('\GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI')
            ->setConstructorArgs([$client])
            ->getMock();
    }
}
