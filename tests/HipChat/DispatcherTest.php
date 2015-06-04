<?php
namespace Tests\HipChat;

use App\HipChat\Commands\CommandInterface;
use App\HipChat\Dispatcher;
use App\HipChat\Webhooks\Events\RoomMessage;
use GuzzleHttp\Client;
use Tests\HipChatTestCase;

class DispatcherTest extends HipChatTestCase
{

    public function testRegisterCommand()
    {
        $dispatcher = new Dispatcher($this->getApiMock());

        $redditCommand = $this->getMockBuilder('\App\HipChat\Commands\Reddit')
            ->setConstructorArgs([$this->getApiMock(), new Client()])
            ->setMethods(null)
            ->getMock();

        $defineCommand = $this->getMockBuilder('\App\HipChat\Commands\Define')
            ->setConstructorArgs([$this->getApiMock(), new Client()])
            ->setMethods(null)
            ->getMock();

        $dispatcher->registerCommand($redditCommand); // 0 aliases
        $dispatcher->registerCommand($defineCommand); // 2 aliases

        $commands = $dispatcher->getRegisteredCommands(false);
        $this->assertInternalType('array', $commands);
        $this->assertCount(2, $commands);

        $commandsWithAliases = $dispatcher->getRegisteredCommands(true);
        $this->assertInternalType('array', $dispatcher->getRegisteredCommands(true));
        $this->assertCount(4, $commandsWithAliases);
    }

    /**
     * Test that an exception is thrown when registering a duplicate command
     *
     * @expectedException     \App\HipChat\Exceptions\DuplicateCommandException
     */
    public function testDuplicateCommand()
    {
        $dispatcher = new Dispatcher($this->getApiMock());

        $redditCommand = $this->getMockBuilder('\App\HipChat\Commands\Reddit')
            ->setConstructorArgs([$this->getApiMock(), new Client()])
            ->setMethods(null)
            ->getMock();

        $dispatcher->registerCommand($redditCommand);
        $dispatcher->registerCommand($redditCommand);

    }

    /**
     * Test that the dispatcher triggers the correct command
     *
     * @dataProvider dispatchProvider
     * @param RoomMessage                                               $message
     * @param CommandInterface|\PHPUnit_Framework_MockObject_MockObject $command
     * @throws \App\HipChat\Exceptions\DuplicateCommandException
     */
    public function testDispatch(RoomMessage $message, $command)
    {
        $dispatcher = new Dispatcher($this->getApiMock(), ['command' => 'jb', 'name' => 'JoeBot']);

        $command->expects($this->once())
            ->method('trigger');

        $dispatcher->registerCommand($command);
        $dispatcher->dispatch($message);
    }

    public function dispatchProvider()
    {
        $redditCommand = $this->getMockBuilder('\App\HipChat\Commands\Reddit')
            ->setConstructorArgs([$this->getApiMock(), new Client()])
            ->setMethods(['trigger'])
            ->getMock();

        $defineCommand = $this->getMockBuilder('\App\HipChat\Commands\Define')
            ->setConstructorArgs([$this->getApiMock(), new Client()])
            ->setMethods(['trigger'])
            ->getMock();

        return [
            [
                new RoomMessage((object)[
                    'event'           => 'room_message',
                    'item'            => (object)[
                        'message' => (object)[
                            'id'      => 1,
                            'type'    => 'message',
                            'message' => '/jb define test',
                            'date'    => '2015-05-28 03:40:56',
                            'from'    => (object)[
                                'id'           => 1,
                                'name'         => 'Tester',
                                'mention_name' => 'tester',
                            ]
                        ],
                        'room'    => (object)[
                            'id'   => 1,
                            'name' => 1,
                        ]
                    ],
                    'oauth_client_id' => 1,
                    'webhook_id'      => 1,
                ]),
                $defineCommand
            ],
            [
                new RoomMessage((object)[
                    'event'           => 'room_message',
                    'item'            => (object)[
                        'message' => (object)[
                            'id'      => 1,
                            'type'    => 'message',
                            'message' => '/jb reddit test',
                            'date'    => '2015-05-28 03:40:56',
                            'from'    => (object)[
                                'id'           => 1,
                                'name'         => 'Tester',
                                'mention_name' => 'tester',
                            ]
                        ],
                        'room'    => (object)[
                            'id'   => 1,
                            'name' => 1,
                        ]
                    ],
                    'oauth_client_id' => 1,
                    'webhook_id'      => 1,
                ]),
                $redditCommand
            ]
        ];
    }
}
