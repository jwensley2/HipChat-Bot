<?php
namespace Tests;

use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Client;

class HipChatTestCase extends \TestCase
{
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

    /**
     * @param      $class
     * @param null $setMethods
     * @return \App\HipChat\Commands\CommandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCommandMock($class, $setMethods = null)
    {
        $client = $this->getClientMock();
        $roomApi = $this->getRoomApiMock($client);

        $command = $this->getMockBuilder($class)
            ->setConstructorArgs([$client, $roomApi])
            ->setMethods($setMethods)
            ->getMock();

        return $command;
    }
}
