<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json;

use AlanShahoei\LibraryManagement\Domain\BookCopy;
use AlanShahoei\LibraryManagement\Domain\Repository\BookCopyRepositoryInterface;
use RuntimeException;

class JsonBookCopyRepository implements BookCopyRepositoryInterface
{
    public function __construct(private JsonFileStorage $storage)
    {
    }

    public function save(BookCopy $bookCopy): void
    {
        $bookCopies = $this->findAll();
        $isUpdated = false;

        foreach ($bookCopies as $key => $existingBookCopy) {
            if ($existingBookCopy->getBarcode() === $bookCopy->getBarcode()) {
                $bookCopies[$key] = $bookCopy;
                $isUpdated = true;
                break;
            }
        }

        if (!$isUpdated) {
            $bookCopies[] = $bookCopy;
        }

        $records = [];

        foreach ($bookCopies as $existingBookCopy) {
            $records[] = $this->mapToRecord($existingBookCopy);
        }

        $this->storage->write($records);
    }

    public function findByBarcode(string $barcode): ?BookCopy
    {
        $bookCopies = $this->findAll();

        foreach ($bookCopies as $bookCopy) {
            if ($bookCopy->getBarcode() === $barcode) {
                return $bookCopy;
            }
        }

        return null;
    }

    public function findByBookId(string $bookId): array
    {
        $bookCopies = [];

        foreach ($this->findAll() as $bookCopy) {
            if ($bookCopy->getBookId() === $bookId) {
                $bookCopies[] = $bookCopy;
            }
        }

        return $bookCopies;
    }

    public function findAll(): array
    {
        $records = $this->storage->read();
        $bookCopies = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new RuntimeException('Invalid book copy record in JSON storage.');
            }

            $bookCopies[] = $this->mapToBookCopy($record);
        }

        return $bookCopies;
    }

    private function mapToBookCopy(array $record): BookCopy
    {
        if (
            !isset($record['barcode'], $record['bookId'], $record['isActive'])
            || !is_string($record['barcode'])
            || !is_string($record['bookId'])
            || !is_bool($record['isActive'])
        ) {
            throw new RuntimeException('Invalid book copy record in JSON storage.');
        }

        return new BookCopy($record['barcode'], $record['bookId'], $record['isActive']);
    }

    private function mapToRecord(BookCopy $bookCopy): array
    {
        return [
            'barcode' => $bookCopy->getBarcode(),
            'bookId' => $bookCopy->getBookId(),
            'isActive' => $bookCopy->isActive(),
        ];
    }
}
