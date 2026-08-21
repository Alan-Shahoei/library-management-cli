<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json;

use AlanShahoei\LibraryManagement\Domain\Book;
use AlanShahoei\LibraryManagement\Domain\Repository\BookRepositoryInterface;
use RuntimeException;

class JsonBookRepository implements BookRepositoryInterface
{
    public function __construct(private JsonFileStorage $storage)
    {
    }

    public function save(Book $book): void
    {
        $books = $this->findAll();
        $isUpdated = false;

        foreach ($books as $key => $existingBook) {
            if ($existingBook->getId() === $book->getId()) {
                $books[$key] = $book;
                $isUpdated = true;
                break;
            }
        }

        if (!$isUpdated) {
            $books[] = $book;
        }

        $records = [];
        foreach ($books as $existingBook) {
            $records[] = $this->mapToRecord($existingBook);
        }

        $this->storage->write($records);
    }

    public function findById(string $id): ?Book
    {
        $books = $this->findAll();

        foreach ($books as $book) {
            if ($book->getId() === $id) {
                return $book;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        $records = $this->storage->read();

        $books = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new RuntimeException('Invalid book record in JSON storage.');
            }

            $books[] = $this->mapToBook($record);
        }

        return $books;
    }

    private function mapToBook(array $record): Book
    {
        if (
            !isset($record['id'], $record['title'], $record['authors'])
            || !is_string($record['id'])
            || !is_string($record['title'])
            || !is_array($record['authors'])
        ) {
            throw new RuntimeException('Invalid book record in JSON storage.');
        }

        $edition = $record['edition'] ?? null;

        if ($edition !== null && !is_string($edition)) {
            throw new RuntimeException('Invalid book record in JSON storage.');
        }

        return new Book($record['id'], $record['title'], $record['authors'], $edition);
    }

    private function mapToRecord(Book $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'authors' => $book->getAuthors(),
            'edition' => $book->getEdition(),
        ];
    }
}
