<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Application\Service;

use AlanShahoei\LibraryManagement\Domain\BookCopy;
use AlanShahoei\LibraryManagement\Domain\Loan;
use AlanShahoei\LibraryManagement\Domain\Member;
use AlanShahoei\LibraryManagement\Domain\Repository\BookCopyRepositoryInterface;
use AlanShahoei\LibraryManagement\Domain\Repository\LoanRepositoryInterface;
use AlanShahoei\LibraryManagement\Domain\Repository\MemberRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class LoanService
{
    private const LOAN_DURATION_DAYS = 14;
    private const MAX_ACTIVE_LOANS_PER_MEMBER = 3;

    public function __construct(
        private LoanRepositoryInterface $loanRepository,
        private MemberRepositoryInterface $memberRepository,
        private BookCopyRepositoryInterface $bookCopyRepository
    ) {
    }

    public function borrowBook(string $barcode, string $memberId, DateTimeImmutable $borrowedAt): Loan
    {
        $bookCopy = $this->findBookCopyOrFail($barcode);

        if (!$bookCopy->isActive()) {
            throw new LogicException('Inactive book copy cannot be borrowed.');
        }

        $member = $this->findMemberOrFail($memberId);

        if (!$member->isActive()) {
            throw new LogicException('Inactive member cannot borrow books.');
        }

        $activeLoan = $this->loanRepository->findActiveByBookCopyBarcode($bookCopy->getBarcode());

        if ($activeLoan !== null) {
            throw new LogicException('Book copy is already on loan.');
        }

        $memberActiveLoans = $this->loanRepository->findActiveByMemberId($member->getId());

        if (count($memberActiveLoans) >= self::MAX_ACTIVE_LOANS_PER_MEMBER) {
            throw new LogicException('Member has reached the maximum number of active loans.');
        }

        $dueAt = $borrowedAt->modify('+' . self::LOAN_DURATION_DAYS . ' days');

        if ($dueAt === false) {
            throw new RuntimeException('Unable to calculate loan due date.');
        }

        $loan = new Loan($bookCopy->getBarcode(), $member->getId(), $borrowedAt, $dueAt);

        $this->loanRepository->save($loan);

        return $loan;
    }

    public function returnBook(string $barcode, DateTimeImmutable $returnedAt): Loan
    {
        $bookCopy = $this->findBookCopyOrFail($barcode);

        $activeLoan = $this->findActiveLoanOrFail($bookCopy->getBarcode());

        $activeLoan->markAsReturned($returnedAt);

        $this->loanRepository->save($activeLoan);

        return $activeLoan;
    }

    public function getAllLoans(): array
    {
        return $this->loanRepository->findAll();
    }

    public function getActiveLoans(): array
    {
        $activeLoans = [];

        foreach ($this->loanRepository->findAll() as $loan) {
            if ($loan->isActive()) {
                $activeLoans[] = $loan;
            }
        }

        return $activeLoans;
    }

    public function getOverdueLoans(DateTimeImmutable $currentDateTime): array
    {
        $overdueLoans = [];

        foreach ($this->loanRepository->findAll() as $loan) {
            if ($loan->isOverdueAt($currentDateTime)) {
                $overdueLoans[] = $loan;
            }
        }

        return $overdueLoans;
    }

    public function getLoansByMemberId(string $memberId): array
    {
        $member = $this->findMemberOrFail($memberId);

        return $this->loanRepository->findByMemberId($member->getId());
    }

    private function findMemberOrFail(string $id): Member
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            throw new InvalidArgumentException('Member ID cannot be empty.');
        }

        $member = $this->memberRepository->findById($normalizedId);

        if ($member === null) {
            throw new RuntimeException("Member not found: {$normalizedId}");
        }

        return $member;
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

    private function findActiveLoanOrFail(string $barcode): Loan
    {
        $normalizedBarcode = trim($barcode);

        if ($normalizedBarcode === '') {
            throw new InvalidArgumentException('Book copy barcode cannot be empty.');
        }

        $activeLoan = $this->loanRepository->findActiveByBookCopyBarcode($normalizedBarcode);

        if ($activeLoan === null) {
            throw new RuntimeException("Active loan not found for book copy: {$normalizedBarcode}");
        }

        return $activeLoan;
    }
}
