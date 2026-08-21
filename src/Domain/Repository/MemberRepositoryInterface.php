<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Domain\Repository;

use AlanShahoei\LibraryManagement\Domain\Member;

interface MemberRepositoryInterface
{
    public function save(Member $member): void;

    public function findById(string $id): ?Member;

    public function findAll(): array;
}
