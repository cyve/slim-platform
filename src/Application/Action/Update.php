<?php

namespace SlimPlatform\Application\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use SlimPlatform\Domain\Repository;

class Update
{
    public function __construct(
        private Repository $repository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $this->repository->has($id) ?: throw new HttpNotFoundException($request);
        try {
            $data = $request->getParsedBody();
            $this->repository->update($id, $data);
            $content = $this->repository->get($id);
        } catch (\PDOException $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write(json_encode($content));

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    }
}
