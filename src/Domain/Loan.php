<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain;

use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

class Loan
{
    public function __construct(
        private string $bookCopyBarcode,
        private string $memberId,
        private DateTimeImmutable $borrowedAt,
        private DateTimeImmutable $dueAt,
        private ?DateTimeImmutable $returnedAt = null
    ) {
        $this->bookCopyBarcode = self::normalizeRequiredText($this->bookCopyBarcode, 'Book copy barcode');
        $this->memberId = self::normalizeRequiredText($this->memberId, 'Member ID');

        if ($this->dueAt <= $this->borrowedAt) {
            throw new InvalidArgumentException('Due date must be after the borrow date.');
        }

        if ($this->returnedAt !== null && $this->returnedAt < $this->borrowedAt) {
            throw new InvalidArgumentException('Return date cannot be before the borrow date.');
        }
    }

    public function getBookCopyBarcode(): string
    {
        return $this->bookCopyBarcode;
    }

    public function getMemberId(): string
    {
        return $this->memberId;
    }

    public function getBorrowedAt(): DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function getDueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function getReturnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }

    public function isOverdueAt(DateTimeImmutable $currentDateTime): bool
    {
        return $this->isActive() && $currentDateTime > $this->dueAt;
    }

    public function markAsReturned(DateTimeImmutable $returnedAt): void
    {
        if (!$this->isActive()) {
            throw new LogicException('Loan has already been returned.');
        }

        if ($returnedAt < $this->borrowedAt) {
            throw new InvalidArgumentException('Return date cannot be before the borrow date.');
        }

        $this->returnedAt = $returnedAt;
    }

    private static function normalizeRequiredText(string $value, string $fieldName): string
    {
        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            throw new InvalidArgumentException($fieldName . ' cannot be empty.');
        }

        return $normalizedValue;
    }
}
