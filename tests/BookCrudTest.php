<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

class BookCrudTest extends TestCase
{
    private \Pdo $pdo;

    public function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host:127.0.0.1;port=3306;dbname=library', 'app');
    }

    public function testGetBooks()
    {
        $response = $this->sendRequest('GET', '/books');

        $this->assertEquals(200, $response->getStatusCode());

        $books = json_decode((string) $response->getBody());
        $this->assertNotEmpty($books);
        foreach ($books as $book) {
            $this->assertIsInt($book->id);
            $this->assertIsString($book->title);
            $this->assertNotEmpty($book->title);
        }
    }

    public function testGetBook()
    {
        $response = $this->sendRequest('GET', '/books/1');

        $this->assertEquals(200, $response->getStatusCode());

        $book = json_decode((string) $response->getBody());
        $this->assertEquals(1, $book->id);
        $this->assertEquals('The lord of the rings', $book->title);
        $this->assertEquals('9780008471286', $book->isbn);
        $this->assertEquals(5, $book->note);
        $this->assertEquals(true, $book->read);
    }

    public function testGetBookNotFound()
    {
        $response = $this->sendRequest('GET', '/books/999');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateBook()
    {
        $response = $this->sendRequest('POST', '/books', [], ['title' => 'Dune']);

        $this->assertEquals(201, $response->getStatusCode());

        $book = json_decode((string) $response->getBody());
        $this->assertNotEmpty($book->id);
        $this->assertEquals('Dune', $book->title);

        $lastInsertId = $book->id;
        $book = $this->pdo->query('SELECT * FROM book ORDER BY ID DESC LIMIT 1')->fetchObject();
        $this->assertEquals($lastInsertId, $book->id);
        $this->assertEquals('Dune', $book->title);
        $this->assertEquals(null, $book->isbn);
        $this->assertEquals(0, $book->note);
        $this->assertEquals(false, $book->read);
    }

    public function testCreateBookWithMissingField()
    {
        $response = $this->sendRequest('POST', '/books', [], []);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateBookWithInvalidData()
    {
        $response = $this->sendRequest('POST', '/books', [], ['title' => 'Dune', 'note' => 'foo']);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUpdateBook()
    {
        $lastBookId = $this->pdo->query('SELECT id FROM book ORDER BY ID DESC LIMIT 1')->fetch(\PDO::FETCH_COLUMN);

        $response = $this->sendRequest('PATCH', '/books/'.$lastBookId, [], ['title' => 'Dune', 'isbn' => '1234']);

        $this->assertEquals(200, $response->getStatusCode());

        $book = json_decode((string) $response->getBody());
        $this->assertEquals($lastBookId, $book->id);
        $this->assertEquals('Dune', $book->title);
        $this->assertEquals('1234', $book->isbn);
        $this->assertEquals(0, $book->note);
        $this->assertEquals(false, $book->read);

        $book = $this->pdo->query('SELECT * FROM book WHERE id='.$lastBookId)->fetchObject();
        $this->assertEquals($lastBookId, $book->id);
        $this->assertEquals('Dune', $book->title);
        $this->assertEquals('1234', $book->isbn);
        $this->assertEquals(0, $book->note);
        $this->assertEquals(false, $book->read);
    }

    public function testUpdateBookWithInvalidData()
    {
        $lastBookId = $this->pdo->query('SELECT id FROM book ORDER BY ID DESC LIMIT 1')->fetch(\PDO::FETCH_COLUMN);

        $response = $this->sendRequest('PATCH', '/books/'.$lastBookId, [], ['title' => 'Dune', 'note' => 'foo']);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testDeleteBook()
    {
        $lastBookId = $this->pdo->query('SELECT id FROM book ORDER BY ID DESC LIMIT 1')->fetch(\PDO::FETCH_COLUMN);

        $response = $this->sendRequest('DELETE', '/books/'.$lastBookId);

        $this->assertEquals(204, $response->getStatusCode());

        $book = $this->pdo->query('SELECT * FROM book WHERE id='.$lastBookId)->fetchObject();
        $this->assertEmpty($book);
    }

    private function sendRequest(string $method, string $url, array $headers = [], ?array $body = null): ResponseInterface
    {
        $context  = stream_context_create([
            'http' => [
                'method'  => $method,
                'header'  => 'Content-Type: application/json',
                'content' => json_encode($body),
                'ignore_errors' => true,
            ]
        ]);
        $http_response_header = [];
        $stream = fopen('http://slim.local'.$url, 'r', false, $context);

        preg_match('/HTTP\/[\d.]+ (\d{3})/', array_shift($http_response_header), $match);
        $statusCode = (int) $match[1];

        $headers = [];
        foreach ($http_response_header as $header) {
            [$name, $value] = explode(': ', $header, 2);
            $headers[$name] = $value;
        }

        return new Response(
            $statusCode,
            new Headers($headers),
            new Stream($stream),
        );
    }
}
