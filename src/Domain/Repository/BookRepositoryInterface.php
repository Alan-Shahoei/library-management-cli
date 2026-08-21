<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain\Repository;

use AlanShahoei\LibraryManagement\Domain\Book;

interface BookRepositoryInterface
{
    public function save(Book $book): void;

    public function findById(string $id): ?Book;

    public function findAll(): array;
}
