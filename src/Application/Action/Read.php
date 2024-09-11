<?php

namespace SlimPlatform\Application\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\StreamFactory;
use SlimPlatform\Domain\Repository;

class Read
{
    public function __construct(
        private Repository $repository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $content = $this->repository->get($id) ?: throw new HttpNotFoundException($request);

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody((new StreamFactory())->createStream(json_encode($content)));
    }
}
