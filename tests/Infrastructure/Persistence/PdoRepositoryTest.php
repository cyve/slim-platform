<?php

namespace Tests\Infrastructure\Persistence;

use PHPUnit\Framework\TestCase;
use SlimPlatform\Infrastructure\Persistence\PdoRepository;

class PdoRepositoryTest extends TestCase
{
    private \PDO $pdo;
    private PdoRepository $repository;

    public function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host:127.0.0.1;port=3306;dbname=library', 'app');
        $this->repository = new PdoRepository($this->pdo, 'book');
    }

    public function testGetAll()
    {
        $books = $this->repository->getAll();

        $this->assertNotEmpty($books);
        foreach ($books as $book) {
            $this->assertIsObject($book);
            $this->assertObjectHasProperty('id', $book);
            $this->assertObjectHasProperty('title', $book);
            $this->assertObjectHasProperty('isbn', $book);
            $this->assertObjectHasProperty('note', $book);
            $this->assertObjectHasProperty('read', $book);
            $this->assertObjectHasProperty('tags', $book);
            $this->assertObjectHasProperty('author', $book);
        }
    }

    public function testGet()
    {
        $book = $this->repository->get(1);

        $this->assertIsObject($book);
        $this->assertEquals(1, $book->id);
        $this->assertEquals('The lord of the rings', $book->title);
        $this->assertEquals('9780008471286', $book->isbn);
        $this->assertEquals(5, $book->note);
        $this->assertEquals(true, $book->read);
        $this->assertEquals(['fantasy', 'top10'], $book->tags);
        $this->assertEquals('/authors/1', $book->author);
    }

    public function testGetUndefined()
    {
        $book = $this->repository->get(999);

        $this->assertNull($book);
    }

    public function testCreate()
    {
        $id = $this->repository->create([
            'id' => 1, // should be ignored
            'foo' => 'bar', // should be ignored
            'title' => 'Dune',
        ]);

        $this->assertNotEmpty($id);
        $this->assertIsInt($id);

        $book = $this->pdo->query('SELECT * FROM book ORDER BY ID DESC LIMIT 1')->fetch(\PDO::FETCH_OBJ);
        $this->assertEquals($id, $book->id);
        $this->assertEquals('Dune', $book->title);
        $this->assertEquals(false, $book->read);
        $this->assertEquals(0, $book->note);
        $this->assertEquals(false, $book->read);
        $this->assertEquals(null, $book->tags);
        $this->assertEquals(null, $book->author);
        $this->assertObjectNotHasProperty('foo', $book);
    }

    public function testCreateWithMissingField()
    {
        $this->expectException(\PDOException::class);

        $this->repository->create([]);
    }

    public function testCreateWithInvalidData()
    {
        $this->expectException(\PDOException::class);

        $this->repository->create([
            'title' => 'Dune',
            'note' => 'foo',
        ]);
    }

    public function testUpdate()
    {
        $lastInsertId = $this->pdo->query('SELECT id FROM book ORDER BY ID DESC LIMIT 1')->fetch(\PDO::FETCH_COLUMN);

        $this->repository->update(
            $lastInsertId,
            [
                'id' => 1, // should be overwritten
                'foo' => 'bar', // should be ignored
                'title' => 'Dune 2',
                'isbn' => '123456789',
                'note' => 2.5,
                'read' => true,
                'tags' => ['fantasy'],
                'author' => '/authors/1',
            ],
        );

        $book = $this->pdo->query('SELECT * FROM book WHERE id='.$lastInsertId)->fetch(\PDO::FETCH_OBJ);
        $this->assertEquals('Dune 2', $book->title);
        $this->assertEquals('123456789', $book->isbn);
        $this->assertEquals(2.5, $book->note);
        $this->assertEquals(true, $book->read);
        $this->assertEquals('["fantasy"]', $book->tags);
        $this->assertEquals(1, $book->author);
        $this->assertObjectNotHasProperty('foo', $book);
    }

    public function testDelete()
    {
        $lastInsertId = $this->pdo->query('SELECT id FROM book ORDER BY ID DESC LIMIT 1')->fetch(\PDO::FETCH_COLUMN);

        $this->repository->delete($lastInsertId);

        $book = $this->pdo->query('SELECT * FROM book WHERE id='.$lastInsertId)->fetch(\PDO::FETCH_OBJ);
        $this->assertEmpty($book);
    }

    public function testHas()
    {
        $this->assertTrue($this->repository->has(1));
    }

    public function testHasNot()
    {
        $this->assertFalse($this->repository->has(999));
    }
}
