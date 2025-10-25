<?php

namespace SlimPlatform\Infrastructure\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

class Authentication implements MiddlewareInterface
{
    public function __construct(
        private string $encryptionKey,
    ){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->getAuthToken($request);

        if (empty($token) || !$this->isTokenValid($token)) {
            throw new HttpUnauthorizedException($request);
        }

        return $handler->handle($request);
    }

    private function getAuthToken(ServerRequestInterface $request): ?string
    {
        if($request->hasHeader('Authorization')) {
            $headers = $request->getHeader('Authorization');
            $header = reset($headers);

            if (!str_starts_with($header, 'Bearer ')) {
                return null;
            }

            [,$token] = explode(' ', $header, 2);

            return $token;
        }

        return null;
    }

    private function isTokenValid(string $token): bool
    {
        $payload = substr($token, 0, 13);
        $signature = substr($token, 13);

        $checksum = sha1($payload.$this->encryptionKey);

        return $signature === $checksum;
    }
}
