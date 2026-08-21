<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json;

use AlanShahoei\LibraryManagement\Domain\Loan;
use AlanShahoei\LibraryManagement\Domain\Repository\LoanRepositoryInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use RuntimeException;

class JsonLoanRepository implements LoanRepositoryInterface
{
    public function __construct(private JsonFileStorage $storage)
    {
    }

    public function save(Loan $loan): void
    {
        $loans = $this->findAll();
        $isUpdated = false;

        foreach ($loans as $key => $existingLoan) {
            if (
                $existingLoan->getBookCopyBarcode() === $loan->getBookCopyBarcode()
                && $existingLoan->getBorrowedAt() == $loan->getBorrowedAt()
            ) {
                $loans[$key] = $loan;
                $isUpdated = true;
                break;
            }
        }

        if (!$isUpdated) {
            $loans[] = $loan;
        }

        $records = [];

        foreach ($loans as $existingLoan) {
            $records[] = $this->mapToRecord($existingLoan);
        }

        $this->storage->write($records);
    }

    public function findActiveByBookCopyBarcode(string $bookCopyBarcode): ?Loan
    {
        foreach ($this->findAll() as $loan) {
            if ($loan->getBookCopyBarcode() === $bookCopyBarcode && $loan->isActive()) {
                return $loan;
            }
        }

        return null;
    }

    public function findActiveByMemberId(string $memberId): array
    {
        $activeLoans = [];

        foreach ($this->findAll() as $loan) {
            if ($loan->getMemberId() === $memberId && $loan->isActive()) {
                $activeLoans[] = $loan;
            }
        }

        return $activeLoans;
    }

    public function findByMemberId(string $memberId): array
    {
        $loans = [];

        foreach ($this->findAll() as $loan) {
            if ($loan->getMemberId() === $memberId) {
                $loans[] = $loan;
            }
        }

        return $loans;
    }

    public function findAll(): array
    {
        $records = $this->storage->read();
        $loans = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new RuntimeException('Invalid loan record in JSON storage.');
            }

            $loans[] = $this->mapToLoan($record);
        }

        return $loans;
    }

    private function mapToLoan(array $record): Loan
    {
        if (!isset($record['bookCopyBarcode'], $record['memberId'], $record['borrowedAt'], $record['dueAt'])
            || !is_string($record['bookCopyBarcode'])
            || !is_string($record['memberId'])
            || !is_string($record['borrowedAt'])
            || !is_string($record['dueAt'])
        ) {
            throw new RuntimeException('Invalid loan record in JSON storage.');
        }

        $returnedAtValue = $record['returnedAt'] ?? null;

        if ($returnedAtValue !== null && !is_string($returnedAtValue)) {
            throw new RuntimeException('Invalid loan record in JSON storage.');
        }

        if (
            trim($record['borrowedAt']) === ''
            || trim($record['dueAt']) === ''
            || ($returnedAtValue !== null && trim($returnedAtValue) === '')
        ) {
            throw new RuntimeException('Invalid loan record in JSON storage.');
        }

        try {
            $borrowedAt = new DateTimeImmutable($record['borrowedAt']);
            $dueAt = new DateTimeImmutable($record['dueAt']);
            $returnedAt = $returnedAtValue === null ? null : new DateTimeImmutable($returnedAtValue);
        } catch (Exception $exception) {
            throw new RuntimeException('Invalid loan record in JSON storage.', 0, $exception);
        }

        return new Loan($record['bookCopyBarcode'], $record['memberId'], $borrowedAt, $dueAt, $returnedAt);
    }

    private function mapToRecord(Loan $loan): array
    {
        $returnedAt = $loan->getReturnedAt();

        return [
            'bookCopyBarcode' => $loan->getBookCopyBarcode(),
            'memberId' => $loan->getMemberId(),
            'borrowedAt' => $loan->getBorrowedAt()->format(DateTimeInterface::ATOM),
            'dueAt' => $loan->getDueAt()->format(DateTimeInterface::ATOM),
            'returnedAt' => $returnedAt?->format(DateTimeInterface::ATOM),
        ];
    }
}