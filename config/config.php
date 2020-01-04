<?php

return [
    'parameters' => [],
    'resources' => [
        'book' => [
            'table' => 'book',
            'model' => [
                'title' => [
                    'type' => 'string',
                    'required' => true
                ],
                'isbn' => [
                    'type' => 'string'
                ],
                'description' => [
                    'type' => 'string'
                ],
                'author' => [
                    'type' => 'string'
                ],
                'publicationDate' => [
                    'type' => 'datetime'
                ]
            ],
            'actions' => [
                'create' => [
                    'method' => 'POST',
                    'uri' => '/books',
                ],
                'read' => [
                    'method' => 'GET',
                    'uri' => '/books/{id}',
                ],
                'update' => [
                    'method' => 'PUT',
                    'uri' => '/books/{id}',
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'uri' => '/books/{id}',
                ],
                'index' => [
                    'method' => 'GET',
                    'uri' => '/books',
                ]
            ]
        ]
    ]
];
