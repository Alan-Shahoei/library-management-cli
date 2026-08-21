<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Application\Service;

use AlanShahoei\LibraryManagement\Domain\Book;
use AlanShahoei\LibraryManagement\Domain\BookCopy;
use AlanShahoei\LibraryManagement\Domain\Repository\BookCopyRepositoryInterface;
use AlanShahoei\LibraryManagement\Domain\Repository\BookRepositoryInterface;
use AlanShahoei\LibraryManagement\Domain\Repository\LoanRepositoryInterface;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class BookService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private BookCopyRepositoryInterface $bookCopyRepository,
        private LoanRepositoryInterface $loanRepository
    ) {
    }

    public function addBook(string $title, array $authors, ?string $edition = null): Book
    {
        $books = $this->bookRepository->findAll();
        $id = $this->generateBookId($books);
        $book = new Book($id, $title, $authors, $edition);

        $this->assertBookIsNotDuplicate($book, $books);

        $this->bookRepository->save($book);

        return $book;
    }

    public function updateBook(string $id, string $title, array $authors, ?string $edition = null): Book
    {
        $book = $this->findBookOrFail($id);

        $book->updateDetails($title, $authors, $edition);

        $books = $this->bookRepository->findAll();

        $this->assertBookIsNotDuplicate($book, $books);

        $this->bookRepository->save($book);

        return $book;
    }

    public function addBookCopy(string $bookId, string $barcode): BookCopy
    {
        $bookCopy = new BookCopy($barcode, $bookId);

        $this->findBookOrFail($bookCopy->getBookId());

        if ($this->bookCopyRepository->findByBarcode($bookCopy->getBarcode()) !== null) {
            throw new LogicException('Book copy barcode already exists.');
        }

        $this->bookCopyRepository->save($bookCopy);

        return $bookCopy;
    }

    public function activateBookCopy(string $barcode): BookCopy
    {
        $bookCopy = $this->findBookCopyOrFail($barcode);

        $bookCopy->activate();

        $this->bookCopyRepository->save($bookCopy);

        return $bookCopy;
    }

    public function deactivateBookCopy(string $barcode): BookCopy
    {
        $bookCopy = $this->findBookCopyOrFail($barcode);

        $activeLoan = $this->loanRepository->findActiveByBookCopyBarcode(
            $bookCopy->getBarcode()
        );

        if ($activeLoan !== null) {
            throw new LogicException('Book copy cannot be deactivated while it is on loan.');
        }

        $bookCopy->deactivate();

        $this->bookCopyRepository->save($bookCopy);

        return $bookCopy;
    }

    public function getAllBooks(): array
    {
        return $this->bookRepository->findAll();
    }

    public function getBookCopies(string $bookId): array
    {
        $book = $this->findBookOrFail($bookId);

        return $this->bookCopyRepository->findByBookId($book->getId());
    }

    private function generateBookId(array $books): string
    {
        $max = 0;

        foreach ($books as $book) {
            $numericPart = (int) substr($book->getId(), 1);

            if ($numericPart > $max) {
                $max = $numericPart;
            }
        }

        return 'B' . sprintf('%05d', $max + 1);
    }

    private function assertBookIsNotDuplicate(Book $book, array $books): void
    {
        foreach ($books as $existingBook) {
            if ($existingBook->getId() === $book->getId()) {
                continue;
            }

            if (
                $book->getTitle() === $existingBook->getTitle()
                && $book->getAuthors() === $existingBook->getAuthors()
                && $book->getEdition() === $existingBook->getEdition()
            ) {
                throw new LogicException('Book already exists.');
            }
        }
    }

    private function findBookOrFail(string $id): Book
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            throw new InvalidArgumentException('Book ID cannot be empty.');
        }

        $book = $this->bookRepository->findById($normalizedId);

        if ($book === null) {
            throw new RuntimeException(
                "Book not found: {$normalizedId}"
            );
        }

        return $book;
    }

    private function findBookCopyOrFail(string $barcode): BookCopy
    {
        $normalizedBarcode = trim($barcode);

        if ($normalizedBarcode === '') {
            throw new InvalidArgumentException('Book copy barcode cannot be empty.');
        }

        $bookCopy = $this->bookCopyRepository->findByBarcode($normalizedBarcode);

        if ($bookCopy === null) {
            throw new RuntimeException("Book copy not found: {$normalizedBarcode}");
        }

        return $bookCopy;
    }
}