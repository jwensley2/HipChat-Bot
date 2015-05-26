<?php

return [
    'bot'      => [
        'name'        => env('BOT_NAME', 'JoeBot'),
        'command'     => env('BOT_COMMAND', 'joebot'),
        'key'         => env('BOT_KEY', 'com.josephwensley.bot'),
        'description' => env('BOT_NAME', 'JoeBot') . ' is an extendable bot built with PHP',
    ],
    'commands' => [
        'define' => [
            'definitions' => 2,
        ],
        'reddit' => [
            'nsfw'     => false, // Show NSFW posts
            'sort'     => 'top', // relevance, new, hot, top, comments
            'timespan' => 'day', // hour, day, week, month, year, all
            'limit'    => 50,
        ]
    ]
];
