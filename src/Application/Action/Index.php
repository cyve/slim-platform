<?php

namespace SlimPlatform\Application\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use SlimPlatform\Domain\Repository;

class Index
{
    public function __construct(
        private Repository $repository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $content = $this->repository->getAll();

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody((new StreamFactory())->createStream(json_encode($content)));
    }
}
