<?php
namespace Tests\HipChat;

use App\HipChat\Commands\CommandInterface;
use App\HipChat\Dispatcher;
use App\HipChat\Webhooks\Events\RoomMessage;
use Tests\HipChatTestCase;

class DispatcherTest extends HipChatTestCase
{

    public function testRegisterCommand()
    {
        $client = $this->getClientMock();
        $roomApi = $this->getRoomApiMock($client);
        $dispatcher = new Dispatcher($client, $roomApi);

        $dispatcher->registerCommand($this->getCommandMock('\App\HipChat\Commands\Reddit')); // 0 aliases
        $dispatcher->registerCommand($this->getCommandMock('\App\HipChat\Commands\Define')); // 2 aliases

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
        $client = $this->getClientMock();
        $roomApi = $this->getRoomApiMock($client);
        $dispatcher = new Dispatcher($client, $roomApi);

        $dispatcher->registerCommand($this->getCommandMock('\App\HipChat\Commands\Reddit'));
        $dispatcher->registerCommand($this->getCommandMock('\App\HipChat\Commands\Reddit'));

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
        $client = $this->getClientMock();
        $roomApi = $this->getRoomApiMock($client);
        $dispatcher = new Dispatcher($client, $roomApi, ['command' => 'jb', 'name' => 'JoeBot']);

        $command->expects($this->once())
            ->method('trigger');

        $dispatcher->registerCommand($command);
        $dispatcher->dispatch($message);
    }

    public function dispatchProvider()
    {
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
                $this->getCommandMock('\App\HipChat\Commands\Define', ['trigger'])
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
                $this->getCommandMock('\App\HipChat\Commands\Reddit', ['trigger'])
            ]
        ];
    }
}
