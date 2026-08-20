<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain;

use InvalidArgumentException;

class BookCopy
{
    public function __construct(
        private string $barcode,
        private string $bookId,
        private bool $isActive = true
    ) {
        $this->barcode = self::normalizeRequiredText($this->barcode, 'Book copy barcode');
        $this->bookId = self::normalizeRequiredText($this->bookId, 'Book ID');
    }

    public function getBarcode(): string
    {
        return $this->barcode;
    }

    public function getBookId(): string
    {
        return $this->bookId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
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
