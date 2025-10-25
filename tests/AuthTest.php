<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testUnauthorized()
    {
        $http_response_header = [];
        fopen('http://127.0.0.1:8000/books', 'r', false, stream_context_create(['http' => ['ignore_errors' => true]]));
        preg_match('/HTTP\/[\d.]+ (\d{3})/', array_shift($http_response_header), $match);

        $this->assertEquals(401, (int) $match[1]);
    }
}
