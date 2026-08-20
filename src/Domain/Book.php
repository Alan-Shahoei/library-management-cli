<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain;

use InvalidArgumentException;

class Book
{
    public function __construct(
        private string $id,
        private string $title,
        private array $authors,
        private ?string $edition = null
    ) {
        $this->id = self::normalizeRequiredText($this->id, 'Book ID');
        $this->title = self::normalizeRequiredText($this->title, 'Book title');
        $this->authors = self::normalizeAuthors($this->authors);
        $this->edition = self::normalizeOptionalText($this->edition);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function getEdition(): ?string
    {
        return $this->edition;
    }

    public function updateDetails(string $title, array $authors, ?string $edition): void
    {
        $normalizedTitle = self::normalizeRequiredText($title, 'Book title');
        $normalizedAuthors = self::normalizeAuthors($authors);
        $normalizedEdition = self::normalizeOptionalText($edition);

        $this->title = $normalizedTitle;
        $this->authors = $normalizedAuthors;
        $this->edition = $normalizedEdition;
    }

    private static function normalizeRequiredText(string $value, string $fieldName): string
    {
        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            throw new InvalidArgumentException($fieldName . ' cannot be empty.');
        }

        return $normalizedValue;
    }

    private static function normalizeAuthors(array $authors): array
    {
        if ($authors === []) {
            throw new InvalidArgumentException('Book must have at least one author.');
        }

        $normalizedAuthors = [];

        foreach ($authors as $author) {
            if (!is_string($author)) {
                throw new InvalidArgumentException('Author name must be a string.');
            }

            $normalizedAuthor = trim($author);

            if ($normalizedAuthor === '') {
                throw new InvalidArgumentException('Author name cannot be empty.');
            }

            if (in_array($normalizedAuthor, $normalizedAuthors, true)) {
                throw new InvalidArgumentException('Book authors cannot contain duplicate names.');
            }

            $normalizedAuthors[] = $normalizedAuthor;
        }

        return $normalizedAuthors;
    }

    private static function normalizeOptionalText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim($value);

        return $normalizedValue === '' ? null : $normalizedValue;
    }
}
