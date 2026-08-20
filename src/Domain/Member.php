<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain;

use InvalidArgumentException;

class Member
{
    public function __construct(
        private string $id,
        private string $fullName,
        private string $phoneNumber,
        private bool $isActive = true
    ) {
        $this->id = self::normalizeRequiredText($this->id, 'Member ID');
        $this->fullName = self::normalizeRequiredText($this->fullName, "Member's full name");
        $this->phoneNumber = self::normalizeRequiredText($this->phoneNumber, "Member's phone number");
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function updateDetails(string $fullName, string $phoneNumber): void
    {
        $normalizedFullName = self::normalizeRequiredText($fullName, "Member's full name");
        $normalizedPhoneNumber = self::normalizeRequiredText($phoneNumber, "Member's phone number");

        $this->fullName = $normalizedFullName;
        $this->phoneNumber = $normalizedPhoneNumber;
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
