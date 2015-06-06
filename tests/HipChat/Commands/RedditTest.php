<?php
namespace Tests\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Commands\Reddit;
use App\HipChat\Webhooks\Events\RoomMessage;
use Tests\HipChatTestCase;

class RedditTest extends HipChatTestCase
{
    /**
     * Test the trigger method
     *
     * @dataProvider triggerTestProvider
     * @param string $message          The webhook message
     * @param array  $posts            The reddit posts
     * @param array  $config
     * @param bool   $displayablePosts Whether posts array should contain posts that can be displayed
     */
    public function testTrigger($message, $posts, $config, $displayablePosts)
    {
        $response = $this->getMockBuilder('\GuzzleHttp\Message\Response')
            ->disableOriginalConstructor()
            ->setMethods(['json'])
            ->getMock();

        $response->expects($this->any())
            ->method('json')
            ->willReturn([
                'data' => ['children' => $posts]
            ]);

        $clientMock = $this->getMockBuilder('\GuzzleHttp\Client')
            ->setMethods(['get'])
            ->getMock();

        $clientMock->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $apiMock = $this->getApiMock();

        if ($displayablePosts) {
            $apiMock->expects($this->once())
                ->method('sendMessage')
                ->with($this->isType('integer'), $this->logicalNot($this->equalTo('No posts were retrieved')));
        } else {
            $apiMock->expects($this->once())
                ->method('sendMessage')
                ->with($this->isType('integer'), $this->equalTo('No posts were retrieved'));
        }


        $reddit = new Reddit($apiMock, $clientMock, $config);

        $reddit->trigger(new CommandParser('jb', '/jb reddit test'), $message);
    }

    public function triggerTestProvider()
    {
        $message = new RoomMessage((object)[
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
        ]);

        return [
            [$message, [$this->createPost(true), $this->createPost(true)], ['nsfw' => false], false],
            [$message, [$this->createPost(false), $this->createPost(true)], ['nsfw' => false], true],
            [$message, [$this->createPost(true), $this->createPost(true)], ['nsfw' => true], true],
            [$message, [], ['nsfw' => true], false],
        ];
    }

    protected function createPost($nsfw = null)
    {
        $faker = \Faker\Factory::create();

        return [
            'type' => 't3',
            'data' => [
                'domain'                 => $faker->domainName,
                'banned_by'              => null,
                'media_embed'            => [
                    'content'   => '',
                    'width'     => $faker->numberBetween(100, 1000),
                    'scrolling' => $faker->boolean(),
                    'height'    => $faker->numberBetween(100, 1000),
                ],
                'subreddit'              => $faker->word,
                'selftext_html'          => null,
                'selftext'               => '',
                'likes'                  => null,
                'suggested_sort'         => null,
                'user_reports'           => [],
                'secure_media'           => null,
                'link_flair_text'        => null,
                'id'                     => $faker->randomAscii,
                'from_kind'              => null,
                'gilded'                 => 0,
                'archived'               => false,
                'clicked'                => false,
                'report_reasons'         => null,
                'author'                 => '',
                'media'                  => [
                    'oembed' => [
                        'provider_url'     => $faker->url,
                        'description'      => $faker->words(5, true),
                        'title'            => $faker->words(5, true),
                        'type'             => 'rich',
                        'thumbnail_width'  => $faker->numberBetween(100, 1000),
                        'height'           => $faker->numberBetween(100, 1000),
                        'width'            => $faker->numberBetween(100, 1000),
                        'html'             => '',
                        'version'          => '1.0',
                        'provider_name'    => $faker->word,
                        'thumbnal_url'     => $faker->imageUrl(200, 200),
                        'thumbnail_height' => $faker->numberBetween(100, 1000),
                    ],
                ],
                'score'                  => $faker->numberBetween(-1000, 1000),
                'approved_by'            => null,
                'over_18'                => ($nsfw) ? $nsfw : $faker->boolean(),
                'hidden'                 => false,
                'num_comments'           => $faker->numberBetween(0, 1000),
                'thumbnail'              => '',
                'subreddit_id'           => $faker->randomAscii,
                'edited'                 => $faker->boolean(),
                'link_flair_css_class'   => null,
                'author_flair_css_class' => null,
                'downs'                  => $faker->numberBetween(0, 1000),
                'secure_media_embed'     => null,
                'saved'                  => $faker->boolean(),
                'removal_reason'         => null,
                'stickied'               => $faker->boolean(),
                'from'                   => null,
                'is_self'                => $faker->boolean(),
                'from_id'                => null,
                'permalink'              => '',
                'name'                   => $faker->randomAscii,
                'created'                => $faker->dateTimeThisMonth->format('u'),
                'url'                    => $faker->url,
                'author_flair_text'      => null,
                'title'                  => $faker->words(5, true),
                'created_utc'            => $faker->dateTimeThisMonth->format('u'),
                'distinguished'          => null,
                'mod_reports'            => [],
                'visited'                => false,
                'num_reports'            => null,
                'ups'                    => $faker->numberBetween(0, 1000)
            ]
        ];
    }
}
