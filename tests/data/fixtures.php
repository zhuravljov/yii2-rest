<?php

return [

    'history' => [
        '1111111111111' => [
            'method' => 'post',
            'endpoint' => 'books',
            'description' => 'Create new book.',
            'status' => '201',
            'time' => '1234567890',
        ],
    ],

    'collection' => [
        '1111111111111' => [
            'method' => 'post',
            'endpoint' => 'books',
            'description' => 'Create new book.',
            'status' => '201',
            'time' => '1234567891',
        ],
    ],

    'records' => [
        '1111111111111' => [
            'request' => [
                'method' => 'post',
                'endpoint' => 'books',
                'tab' => '2',
                'queryKeys' => [],
                'queryValues' => [],
                'queryActives' => [],
                'bodyKeys' => ['title', 'description'],
                'bodyValues' => ['Title 1', 'Description 1'],
                'bodyActives' => ['1', '1'],
                'headerKeys' => [],
                'headerValues' => [],
                'headerActives' => [],
                'description' => 'Create new book.'
            ],
            'response' => [
                'status' => '201',
                'duration' => '0.123',
                'headers' => [
                    'Http-Code' => ['201'],
                    'Date' => ['Sat, 12 Sep 2015 00:13:02 GMT'],
                    'Server' => ['Apache/2.4.7 (Ubuntu)'],
                    'Location' => ['http://rest.local/books/1'],
                    'Content-Length' => ['104'],
                    'Connection' => ['close'],
                    'Content-Type' => ['application/json; charset=UTF-8'],
                ],
                'content' => json_encode([
                    'id' => 1,
                    'title' => 'Title 1',
                    'description' => 'Description 1',
                    'created_at' => 1442016782,
                    'updated_at' => 1442016782,
                ]),
            ],
        ],
    ],
];