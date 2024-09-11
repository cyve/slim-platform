<?php

namespace SlimPlatform\Application\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use SlimPlatform\Domain\Repository;

class Delete
{
    public function __construct(
        private Repository $repository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $this->repository->has($id) ?: throw new HttpNotFoundException($request);
        $this->repository->delete($id);

        return $response->withStatus(204);
    }
}
