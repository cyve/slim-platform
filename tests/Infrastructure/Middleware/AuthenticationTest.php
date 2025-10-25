<?php

namespace Tests\Infrastructure\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Uri;
use SlimPlatform\Infrastructure\Middlewares\Authentication;

class AuthenticationTest extends TestCase
{
    public function testAuthenticationWithValidToken()
    {
        $userId = 'abcdefghijklm';
        $encryptionKey = '123456789';
        $authentication = new Authentication($encryptionKey);

        $request = (new RequestFactory())->createRequest('GET', new Uri('http', '127.0.0.1'))
            ->withHeader('Authorization', 'Bearer '.$userId.sha1($userId.$encryptionKey));
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request);

        $authentication->process($request, $handler);
    }

    public function testAuthenticationWithInvalidTokenShouldThrowHttpUnauthorizedException()
    {
        $this->expectException(HttpUnauthorizedException::class);

        $userId = 'abcdefghijklm';
        $encryptionKey = '123456789';
        $authentication = new Authentication($encryptionKey);

        $request = (new RequestFactory())->createRequest('GET', new Uri('http', '127.0.0.1'))
            ->withHeader('Authorization', 'Bearer '.$userId.'invalid_signature');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $authentication->process($request, $handler);
    }

    public function testAuthenticationWithoutTokenShouldThrowHttpUnauthorizedException()
    {
        $this->expectException(HttpUnauthorizedException::class);

        $encryptionKey = '123456789';
        $authentication = new Authentication($encryptionKey);

        $request = (new RequestFactory())->createRequest('GET', new Uri('http', '127.0.0.1'));
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $authentication->process($request, $handler);
    }
}
