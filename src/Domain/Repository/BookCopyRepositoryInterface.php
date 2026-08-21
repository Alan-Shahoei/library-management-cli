<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain\Repository;

use AlanShahoei\LibraryManagement\Domain\BookCopy;

interface BookCopyRepositoryInterface
{
    public function save(BookCopy $bookCopy): void;

    public function findByBarcode(string $barcode): ?BookCopy;

    public function findByBookId(string $bookId): array;

    public function findAll(): array;
}
