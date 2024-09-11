<?php

namespace SlimPlatform\Application\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use SlimPlatform\Domain\Repository;

class Create
{
    public function __construct(
        private Repository $repository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $id = $this->repository->create($data);
            $content = $this->repository->get($id);
        } catch (\PDOException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write(json_encode($content));

        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
    }
}
