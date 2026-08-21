<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain\Repository;

use AlanShahoei\LibraryManagement\Domain\Loan;

interface LoanRepositoryInterface
{
    public function save(Loan $loan): void;

    public function findActiveByBookCopyBarcode(string $bookCopyBarcode): ?Loan;

    public function findActiveByMemberId(string $memberId): array;

    public function findByMemberId(string $memberId): array;

    public function findAll(): array;
}
